<?php

declare(strict_types=1);

namespace App\Controller;

use App\Response\BaseResponse;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * 日志查看控制器
 *
 * 读取 var/logs/app.log 文件，返回 JSON 格式的日志数据。
 * 不再渲染 HTML 页面，前端由 Vue 3 独立处理。
 */
final class LogController
{
    /** 日志文件路径 */
    private const LOG_FILE = __DIR__ . '/../../var/logs/app.log';

    /** 最多显示最近 N 行 */
    private const MAX_LINES = 200;

    public function __construct(
        private readonly Logger $logger,
    ) {
    }

    /**
     * GET /api/logs — 获取应用日志
     *
     * 查询参数：
     *   level  日志级别过滤（INFO / WARNING / ERROR / DEBUG，默认不过滤）
     *   limit 返回条数（默认 100，最大 500）
     *
     * @param Request  $request  PSR-7 HTTP 请求对象
     * @param Response $response PSR-7 HTTP 响应对象
     *
     * @return Response JSON 响应
     */
    public function index(Request $request, Response $response): Response
    {
        $this->logger->info('获取应用日志');

        $params = $request->getQueryParams();
        $level = isset($params['level']) ? strtoupper(trim($params['level'])) : null;
        $limit = max(1, min(500, (int) ($params['limit'] ?? 100)));

        $lines = [];
        if (file_exists(self::LOG_FILE)) {
            $lines = array_slice(
                file(self::LOG_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES),
                -self::MAX_LINES,
            );
        }

        // 解析日志行为结构化数据
        $entries = [];
        foreach ($lines as $line) {
            $entry = $this->parseLogLine($line);
            if ($level !== null && $entry['level'] !== $level) {
                continue;
            }
            $entries[] = $entry;
        }

        // 限制返回条数
        $entries = array_slice($entries, -$limit);

        return BaseResponse::success($response, [
            'total' => count($entries),
            'entries' => $entries,
        ], '获取成功');
    }

    /**
     * 解析 Monolog 日志行为结构化数据
     *
     * Monolog 默认格式：YYYY-MM-DD HH:MM:SS LEVEL CHANNEL: message
     *
     * @param string $line 日志行
     *
     * @return array{ level: string, message: string, datetime: string }
     */
    private function parseLogLine(string $line): array
    {
        // Monolog StreamHandler default format: [2026-09-08T15:24:51.223534+00:00] app.INFO: message [] []
        if (preg_match(
            '/^\[([^\]]+)\]\s+(\w+)\.(\w+):\s*(.+)$/',
            $line,
            $matches,
        )) {
            return [
                'datetime' => $matches[1],
                'level'    => strtoupper($matches[3]),
                'channel'  => $matches[2],
                'message'  => $matches[4],
            ];
        }

        return [
            'datetime' => '',
            'level'    => 'UNKNOWN',
            'channel'  => '',
            'message'  => $line,
        ];
    }
}