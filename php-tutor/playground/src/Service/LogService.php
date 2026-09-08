<?php

declare(strict_types=1);

namespace Yzqde\Playground\Service;

use Yzqde\Playground\Exception\LogNotFoundException;

/**
 * 日志读取:列出按天的日志文件,以及查看某个文件末尾的若干条记录
 *
 * Monolog 的默认 LineFormatter 输出为
 * `[2026-09-07T22:08:08.335037+00:00] app.INFO: 消息 {"上下文":...} []`。
 * 注意一条记录并不一定只占一行:带异常时,上下文里的 file/trace 含有换行,
 * 因此按「以 ISO 8601 时间戳起头」切分记录,而不是按换行切。
 */
final class LogService
{
    /** 读取窗口上限:只从文件末尾回读这么多字节,大文件也不会整文件载入内存 */
    private const int|float MAX_TAIL_BYTES = 4 * 1024 * 1024;

    private const int DEFAULT_LIMIT = 200;
    private const int MAX_LIMIT = 1000;

    /** 一条记录的开头:LineFormatter 以 ISO 8601 时间戳起头 */
    private const string RECORD_START = '/^\[\d{4}-\d{2}-\d{2}T/';

    public function __construct(private readonly string $logDir)
    {
    }

    /**
     * @return list<array{name: string, channel: string, date: string, size: int, mtime: int}> 按修改时间倒序
     */
    public function list(): array
    {
        $files = [];
        if (!is_dir($this->logDir)) {
            return $files;
        }

        foreach (scandir($this->logDir) ?: [] as $name) {
            if (!preg_match('/^(app|error)-(\d{4}-\d{2}-\d{2})\.log$/', $name, $m)) {
                continue;
            }
            $path = $this->logDir . '/' . $name;
            if (!is_file($path)) {
                continue;
            }
            $files[] = [
                'name' => $name,
                'channel' => $m[1],
                'date' => $m[2],
                'size' => (int)filesize($path),
                'mtime' => (int)filemtime($path),
            ];
        }

        usort($files, fn(array $a, array $b) => $b['mtime'] <=> $a['mtime']);
        return $files;
    }

    /**
     * 读取文件末尾的记录,从最新往前数
     *
     * @param int         $limit 最多返回多少条,超出上限会被夹紧
     * @param string|null $level 只返回该级别的记录,空值表示全部
     *
     * @throws LogNotFoundException 文件名不合法或文件不存在
     */
    public function tail(string $file, int $limit = self::DEFAULT_LIMIT, ?string $level = null): array
    {
        $path = $this->resolve($file);

        // 先取回读窗口内的原始文本,再逐条解析;避免大文件整份读入
        $size = (int)filesize($path);
        $offset = max(0, $size - self::MAX_TAIL_BYTES);
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new LogNotFoundException('日志文件不可读');
        }

        try {
            fseek($handle, $offset);
            $chunk = stream_get_contents($handle);
        } finally {
            fclose($handle);
        }

        if ($chunk === false) {
            $chunk = '';
        }

        $lines = array_values(array_filter(
            explode("\n", $chunk),
            fn(string $line) => $line !== '' && $line !== "\r",
        ));

        $raw = $this->toRecords($lines);

        // 回读窗口可能从记录中间切入,丢掉开头那条残缺的
        if ($offset > 0 && isset($raw[0]) && !preg_match(self::RECORD_START, $raw[0])) {
            array_shift($raw);
        }

        $limit = max(1, min($limit, self::MAX_LIMIT));
        $records = [];

        // 从最新往前数,凑够 limit 条即停
        for ($i = count($raw) - 1; $i >= 0 && count($records) < $limit; $i--) {
            $parsed = $this->parseRecord($raw[$i]);
            if ($level !== null && $parsed['level'] !== $level) {
                continue;
            }
            $records[] = $parsed;
        }

        return [
            'file' => $file,
            'total' => count($raw),
            'limit' => $limit,
            'level' => $level,
            'lines' => $records,
        ];
    }

    /**
     * 把按换行切出的文本合并成完整记录:不以时间戳起头的行是上一条记录的续行
     *
     * @param list<string> $lines
     *
     * @return list<string>
     */
    private function toRecords(array $lines): array
    {
        $records = [];
        foreach ($lines as $line) {
            if ($records !== [] && !preg_match(self::RECORD_START, $line)) {
                $records[array_key_last($records)] .= "\n" . $line;
                continue;
            }
            $records[] = $line;
        }
        return $records;
    }

    /**
     * 解析一条记录:时间戳、通道、级别、消息与上下文
     */
    private function parseRecord(string $record): array
    {
        if (!preg_match('/^\[([^\]]+)\]\s+([\w.-]+)\.(\w+):\s+(.*)$/s', $record, $m)) {
            return ['ts' => null, 'channel' => null, 'level' => 'UNKNOWN', 'message' => $record, 'context' => null];
        }

        [$message, $context] = $this->splitTrailingJson($m[4]);

        return [
            'ts' => $m[1],
            'channel' => $m[2],
            'level' => strtoupper($m[3]),
            'message' => $message,
            'context' => $context,
        ];
    }

    /**
     * 剥出记录末尾由 Monolog 追加的 context 与 extra
     *
     * 不能用正则做左向贪婪匹配:异常上下文里有 `{main}` 之类的花括号,
     * 会让 `\{.*\}` 错位吞掉真正的上下文。改为从末尾倒序扫描括号深度。
     *
     * @return array{0: string, 1: array<string, mixed>|null} 消息文本与解析后的上下文
     */
    private function splitTrailingJson(string $record): array
    {
        $tokens = $this->trailingJsonTokens($record);

        // 末尾两个片段依次是 context 与 extra(extra 通常为空数组)
        if (count($tokens) < 2) {
            return [$record, null];
        }

        $decoded = json_decode($tokens[1][1], true);

        return [rtrim(substr($record, 0, $tokens[1][0])), is_array($decoded) ? $decoded : null];
    }

    /**
     * 倒序取出记录末尾成对的 JSON 片段
     *
     * @return list<array{0: int, 1: string}> 每项为 [起始偏移, 文本],按从右往左排列
     */
    private function trailingJsonTokens(string $record, int $max = 2): array
    {
        $tokens = [];
        $i = strlen($record) - 1;

        while (count($tokens) < $max && $i >= 0) {
            while ($i >= 0 && $record[$i] === ' ') {
                $i--;
            }
            if ($i < 0 || ($record[$i] !== '}' && $record[$i] !== ']')) {
                break;
            }

            $close = $record[$i];
            $open = $close === '}' ? '{' : '[';
            $depth = 0;
            $start = -1;
            for ($j = $i; $j >= 0; $j--) {
                if ($record[$j] === $close) {
                    $depth++;
                } elseif ($record[$j] === $open) {
                    $depth--;
                    if ($depth === 0) {
                        $start = $j;
                        break;
                    }
                }
            }

            if ($start < 0) {
                break;
            }

            $tokens[] = [$start, substr($record, $start, $i - $start + 1)];
            $i = $start - 1;
        }

        return $tokens;
    }

    /**
     * 严格白名单 + realpath 前缀校验,防目录穿越
     */
    private function resolve(string $file): string
    {
        if (!preg_match('/^(app|error)-\d{4}-\d{2}-\d{2}\.log$/', basename($file))) {
            throw new LogNotFoundException('日志文件不存在');
        }

        $realBase = realpath($this->logDir);
        $realPath = realpath($this->logDir . '/' . $file);
        if ($realBase === false || $realPath === false || !str_starts_with($realPath, $realBase . DIRECTORY_SEPARATOR)) {
            throw new LogNotFoundException('日志文件不存在');
        }

        return $realPath;
    }
}
