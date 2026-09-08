<?php

declare(strict_types=1);

namespace Service\Log;

use Service\ApiException;

/**
 * 按天日志文件的读取端：配合 LoggerFactory 落盘的 storage/logs/app-YYYY-MM-DD.log。
 * 读接口只读文件、不修改；日期参数严格校验后再拼路径，用户输入无法参与路径构造。
 */
final class LogStore
{
    /** 单次最多返回的行数，避免一次读爆内存与响应体 */
    private const MAX_LIMIT = 2000;

    /** 单份日志文件默认返回的行数 */
    private const DEFAULT_LIMIT = 500;

    /** 单条日志格式：[Y-m-d H:i:s] 通道.级别: 消息 [context JSON] */
    private const LINE_PATTERN = '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] ([^.]+)\.([A-Z]+): (.*)$/s';

    public function __construct(
        private readonly string $dir,
    ) {
    }

    /** __DIR__ = src/Service/Log，向上 3 级到项目根 */
    public static function defaultDir(): string
    {
        return dirname(__DIR__, 3) . '/storage/logs';
    }

    /**
     * 列出全部可用日志文件（新→旧）。只取 filesize()，不读文件内容，保持该接口开销极低。
     *
     * @return array{days: list<array{date: string, bytes: int}>}
     */
    public function days(int $limit = 30): array
    {
        $limit = max(1, min($limit, 90));
        $files = glob($this->dir . '/app-*.log');
        if ($files === false || $files === []) {
            return ['days' => []];
        }

        $entries = [];
        foreach ($files as $file) {
            $basename = basename($file);
            if (preg_match('/^app-(\d{4}-\d{2}-\d{2})\.log$/', $basename, $m) === 1) {
                $entries[] = ['date' => $m[1], 'bytes' => (int) filesize($file)];
            }
        }

        usort($entries, fn(array $a, array $b): int => strcmp($b['date'], $a['date']));

        return ['days' => array_slice($entries, 0, $limit)];
    }

    /**
     * 读取某一天的日志窗口。单次遍历文件：统计总行数的同时收集 [offset, offset+limit) 区间，
     * 内存占用与文件总行数无关。
     *
     * @return array{date: string, total: int, offset: int, limit: int, truncated: bool, lines: list<array<string, mixed>>}
     */
    public function readDay(string $date, int $offset, int $limit = self::DEFAULT_LIMIT): array
    {
        $path = $this->pathFor($date);
        $offset = max(0, $offset);
        $limit = max(1, min($limit, self::MAX_LIMIT));

        if (!is_file($path)) {
            return ['date' => $date, 'total' => 0, 'offset' => 0, 'limit' => $limit, 'truncated' => false, 'lines' => []];
        }

        $lines = [];
        $total = 0;
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new ApiException('日志读取失败', 500);
        }
        try {
            while (($raw = fgets($handle)) !== false) {
                $no = $total++;
                if ($no >= $offset && count($lines) < $limit) {
                    $lines[] = $this->parseLine($no, rtrim($raw, "\r\n"));
                }
            }
        } finally {
            fclose($handle);
        }

        return [
            'date' => $date,
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'truncated' => $total > $offset + $limit,
            'lines' => $lines,
        ];
    }

    /** 日期严格校验后拼接文件名，杜绝路径穿越 */
    private function pathFor(string $date): string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) !== 1 || date_create_from_format('Y-m-d', $date) === false) {
            throw new ApiException('日期格式须为 YYYY-MM-DD');
        }

        return $this->dir . '/app-' . $date . '.log';
    }

    /**
     * 解析单行日志为结构化字段；解析失败时降级为原文本，保证不丢行。
     *
     * @return array{no: int, timestamp: string|null, channel: string|null, level: string|null, message: string, context: array<string, mixed>|null, raw: string}
     */
    private function parseLine(int $no, string $raw): array
    {
        if (preg_match(self::LINE_PATTERN, $raw, $m) !== 1) {
            return [
                'no' => $no,
                'timestamp' => null,
                'channel' => null,
                'level' => null,
                'message' => $raw,
                'context' => null,
                'raw' => $raw,
            ];
        }

        $message = $m[4];
        $context = null;

        // context 以 " {json}" 结尾；只有真正解出对象才剥离，避免误伤含花括号的正文
        if (preg_match('/^(.*?) (\{.*\})$/s', $message, $c) === 1) {
            $decoded = json_decode($c[2], true);
            if (is_array($decoded)) {
                $message = $c[1];
                $context = $decoded;
            }
        }

        return [
            'no' => $no,
            'timestamp' => $m[1],
            'channel' => $m[2],
            'level' => $m[3],
            'message' => $message,
            'context' => $context,
            'raw' => $raw,
        ];
    }
}
