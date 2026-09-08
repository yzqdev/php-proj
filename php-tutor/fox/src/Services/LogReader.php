<?php

declare(strict_types=1);

namespace Yzqde\Fox\Services;

use DateTime;

/**
 * Reads the rotated Monolog files written by {@see Logger}.
 *
 * Logger uses a RotatingFileHandler with filename "app.log", which produces one
 * file per day named app-YYYY-MM-DD.log. Every entry is one line in the shape
 *   Y-m-d H:i:s > LEVEL > message {context} {extra}
 */
class LogReader
{
    private const HEAD_PATTERN = '/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) > ([A-Z]+) > (.*)$/s';

    /** Levels a RotatingFileHandler at DEBUG level can ever write. */
    public const LEVELS = ['DEBUG', 'INFO', 'NOTICE', 'WARNING', 'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'];

    private const MAX_LIMIT = 5000;

    private string $logDir;
    private string $prefix;

    public function __construct(string $logDir = __DIR__ . '/../../logs', string $prefix = 'app')
    {
        $this->logDir = rtrim($logDir, '/\\');
        $this->prefix = $prefix;
    }

    public function isValidDate(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        // Rejects 2026-13-40 and friends; createFromFormat does not validate on its own.
        $parsed = DateTime::createFromFormat('!Y-m-d', $date);

        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }

    /** @return array<string, array<string, mixed>> date => stats, newest first */
    public function days(): array
    {
        if (!is_dir($this->logDir)) {
            return [];
        }

        $days = [];
        foreach (scandir($this->logDir) ?: [] as $name) {
            if (!preg_match('/^' . preg_quote($this->prefix, '/') . '-(\d{4}-\d{2}-\d{2})\.log$/', $name, $m)) {
                continue;
            }

            $date = $m[1];
            if (!$this->isValidDate($date)) {
                continue;
            }

            $days[$date] = $this->stats($date);
        }

        krsort($days);

        return $days;
    }

    public function stats(string $date): array
    {
        $lines = $this->readLines($date);
        $levels = [];

        foreach ($lines as $line) {
            $head = $this->headOf($line);
            if ($head[1] !== null) {
                $levels[$head[1]] = ($levels[$head[1]] ?? 0) + 1;
            }
        }

        return [
            'date' => $date,
            'file' => $this->fileName($date),
            'size' => $this->fileExists($date) ? (int) filesize($this->pathFor($date)) : 0,
            'entries' => count($lines),
            'first' => $this->headOf($lines[0] ?? '')[0],
            'last' => $this->headOf($lines[count($lines) - 1] ?? '')[0],
            'levels' => $levels,
        ];
    }

    /** @return array{date:string,total:int,limit:int,offset:int,hasMore:bool,entries:array}|null */
    public function query(
        string $date,
        ?string $level = null,
        ?string $keyword = null,
        int $limit = 500,
        int $offset = 0
    ): ?array {
        if (!$this->fileExists($date)) {
            return null;
        }

        $entries = array_map($this->parseLine(...), $this->readLines($date));

        $filtered = array_filter($entries, static function (array $entry) use ($level, $keyword): bool {
            if ($level !== null && $entry['level'] !== $level) {
                return false;
            }

            return $keyword === null || stripos($entry['raw'], $keyword) !== false;
        });

        $total = count($filtered);

        return [
            'date' => $date,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
            'hasMore' => $offset + $limit < $total,
            'entries' => array_slice($filtered, $offset, $limit),
        ];
    }

    public function raw(string $date): ?string
    {
        if (!$this->fileExists($date)) {
            return null;
        }

        $content = file_get_contents($this->pathFor($date));

        return $content === false ? null : $content;
    }

    public function exists(string $date): bool
    {
        return $this->fileExists($date);
    }

    public function parseLine(string $line): array
    {
        $raw = rtrim($line, "\r\n");

        if (!preg_match(self::HEAD_PATTERN, $raw, $m)) {
            return ['datetime' => null, 'level' => null, 'message' => null, 'context' => null, 'extra' => null, 'raw' => $raw];
        }

        // Peel at most two trailing tokens: the first one off is %extra%, the next %context%.
        $tail = [];
        $rest = $m[3];
        for ($i = 0; $i < 2; $i++) {
            $peeled = $this->peelToken($rest);
            if ($peeled === null) {
                break;
            }
            $tail[] = $peeled[1];
            $rest = $peeled[0];
        }

        return [
            'datetime' => $m[1],
            'level' => $m[2],
            'message' => trim($rest),
            'context' => $this->decodeJson($tail[1] ?? null),
            'extra' => $this->decodeJson($tail[0] ?? null),
            'raw' => $raw,
        ];
    }

    public function normalizeLimit(int $limit): int
    {
        return min(max($limit, 1), self::MAX_LIMIT);
    }

    public function normalizeOffset(int $offset): int
    {
        return max($offset, 0);
    }

    /**
     * Strips the last whitespace-separated bracketed token, so a message that itself
     * contains JSON is not swallowed into the context.
     *
     * @return array{0:string,1:string}|null [text before the token, token]
     */
    private function peelToken(string $text): ?array
    {
        $depth = 0;
        $start = null;

        for ($i = strlen($text) - 1; $i >= 0; $i--) {
            $char = $text[$i];

            if ($char === '}' || $char === ']') {
                $depth++;
                continue;
            }

            if ($char !== '{' && $char !== '[') {
                continue;
            }

            $depth--;
            if ($depth === 0) {
                $start = $i;
                break;
            }
        }

        if ($start === null) {
            return null;
        }

        $before = substr($text, 0, $start);

        // Without a space boundary the bracket belongs to the message, not to the context.
        if ($before !== '' && !preg_match('/\s$/', $before)) {
            return null;
        }

        return [rtrim($before), substr($text, $start)];
    }

    private function decodeJson(?string $token): mixed
    {
        if ($token === null) {
            return null;
        }

        $decoded = json_decode($token, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }

    /** @return array{0:?string,1:?string} [datetime, level] */
    private function headOf(string $line): array
    {
        if (!preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) > ([A-Z]+) >/', $line, $m)) {
            return [null, null];
        }

        return [$m[1], $m[2]];
    }

    private function readLines(string $date): array
    {
        if (!$this->fileExists($date)) {
            return [];
        }

        $lines = file($this->pathFor($date), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        return $lines === false ? [] : $lines;
    }

    private function fileExists(string $date): bool
    {
        $path = $this->pathFor($date);
        if (!is_file($path)) {
            return false;
        }

        // The filename is built from digits and dashes only, but keep the boundary check
        // so a prefix collision could never escape the log directory.
        $base = realpath($this->logDir);
        $real = realpath($path);

        return $base !== false && $real !== false && str_starts_with($real, $base . DIRECTORY_SEPARATOR);
    }

    private function pathFor(string $date): string
    {
        return $this->logDir . DIRECTORY_SEPARATOR . $this->fileName($date);
    }

    private function fileName(string $date): string
    {
        return $this->prefix . '-' . $date . '.log';
    }
}
