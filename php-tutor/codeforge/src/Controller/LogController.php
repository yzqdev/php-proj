<?php

declare(strict_types=1);

namespace App\Controller;

use App\Config\FileConst;
use App\Response\BaseResponse;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * 日志查看控制器
 *
 * 读取 var/logs 下按天轮转的 app.log-YYYY-MM-DD.log，返回 JSON 格式的日志数据。
 * 不再渲染 HTML 页面，前端由 Vue 3 独立处理。
 */
final class LogController
{
    /** 日志目录 */
    private const LOG_DIR = FileConst::LOG_DIR;

    /** 日志文件前缀，需与 config/container.php 中的 RotatingFileHandler 保持一致 */
    private const LOG_BASENAME = 'app';

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
     *   level 日志级别过滤（INFO / WARNING / ERROR / DEBUG，默认不过滤）
     *   limit 返回条数（默认 100，最大 500）
     *   date  指定日期 YYYY-MM-DD（默认当天，格式非法时回退到当天）
     *
     * @param Request  $request  PSR-7 HTTP 请求对象
     * @param Response $response PSR-7 HTTP 响应对象
     *
     * @return Response JSON 响应
     */
    #[OA\Get(path: '/api/logs', summary: '获取应用日志', security: [['bearerAuth' => []]], tags: ['日志'])]
    #[OA\Parameter(name: 'level', description: '日志级别过滤，默认不过滤', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['DEBUG', 'INFO', 'WARNING', 'ERROR']))]
    #[OA\Parameter(name: 'limit', description: '返回条数，默认 100，最大 500', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 500, example: 100))]
    #[OA\Parameter(name: 'date', in: 'query', required: false, schema: new OA\Schema(type: 'string', pattern: '^\\d{4}-\\d{2}-\\d{2}$', example: '2026-09-09'), description: '指定日期 YYYY-MM-DD，默认当天；格式非法时回退到当天')]
    #[OA\Response(response: 200, description: '获取成功', content: new OA\JsonContent(ref: '#/components/schemas/LogsPageResponse'))]
    #[OA\Response(response: 500, description: '服务器错误', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function index(Request $request, Response $response): Response
    {
        $this->logger->info('获取应用日志');

        $params = $request->getQueryParams();
        $level = isset($params['level']) ? strtoupper(trim($params['level'])) : null;
        $limit = max(1, min(500, (int) ($params['limit'] ?? 100)));
        $rawDate = $params['date'] ?? null;
        $date = $this->resolveDate(is_string($rawDate) ? $rawDate : null);

        $lines = [];
        $logFile = self::LOG_DIR . '/' . self::LOG_BASENAME . '-' . $date . '.log';
        if (is_file($logFile)) {
            $content = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $lines = is_array($content) ? array_slice($content, -self::MAX_LINES) : [];
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
     * 解析日志日期参数，规范化为 YYYY-MM-DD。
     *
     * 用 RotatingFileHandler::FILE_PER_DAY 取日期格式，确保与日志文件的命名规则始终一致。
     * 参数缺失或格式非法时回退到当天，避免一次误传就让页面空白。
     *
     * @param string|null $raw 请求中的 date 参数
     *
     * @return string 规范化后的 YYYY-MM-DD 日期
     */
    private function resolveDate(?string $raw): string
    {
        $today = date(RotatingFileHandler::FILE_PER_DAY);

        if ($raw === null) {
            return $today;
        }

        $date = trim($raw);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) !== 1) {
            return $today;
        }

        return $date;
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