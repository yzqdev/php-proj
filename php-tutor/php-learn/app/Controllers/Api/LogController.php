<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Helpers\Config;
use App\Helpers\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * ============================================================
 * 日志查看接口
 * ============================================================
 *
 * 提供两个端点：
 *   - GET /api/v1/logs       → 列出所有日志文件（日期 + 大小）
 *   - GET /api/v1/logs/{date} → 读取指定日期的日志内容
 *
 * 日志文件由 Monolog RotatingFileHandler 生成：
 *   - 路径：{logger.path}/app-YYYY-MM-DD（无扩展名）
 *   - 格式：[datetime] app.LEVEL: message context extra
 *
 * 安全限制：
 *   - 仅管理员可访问（路由层 ApiAuthenticate 中间件已校验角色）
 *   - 只能读取 {date} 参数格式为 YYYY-MM-DD 的文件，防止路径穿越
 *   - 文件读取有大小上限（默认 512KB），避免一次性加载超大日志
 *
 * 对应 Java / Spring Boot：
 *   - Actuator 的 /actuator/logfile 端点
 *   - 但本项目不引 Spring Boot Actuator，手写一个轻量版
 */
final class LogController
{
    /** 日志文件名前缀（与 Logger.php 里的 RotatingFileHandler 一致） */
    private const FILE_PREFIX = 'app-';

    /** 单次读取最大字节数（512KB） */
    private const MAX_READ_BYTES = 512 * 1024;

    /** 日志目录路径（从 config 读取，延迟初始化） */
    private string $logPath;

    public function __construct()
    {
        $this->logPath = (string) Config::get('logger.path', dirname(__DIR__, 2) . '/logs');
    }

    /**
     * GET /api/v1/logs
     *
     * 列出所有日志文件，按日期倒序排列。
     * 返回：{ list: [{ date, fileName, size, sizeHuman }], total }
     */
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $files = $this->scanLogFiles();

        // 按日期倒序（最新在前）
        usort($files, static fn(array $a, array $b) => strcmp($b['date'], $a['date']));

        return JsonResponse::ok([
            'list'  => $files,
            'total' => count($files),
        ]);
    }

    /**
     * GET /api/v1/logs/{date}
     *
     * 读取指定日期的日志内容。
     * 支持 query 参数：
     *   - offset  跳过前 N 行（默认 0）
     *   - limit   最多返回 N 行（默认 500，最大 2000）
     *   - level   按级别过滤（debug/info/warning/error/critical）
     *
     * 返回：{ date, fileName, totalLines, offset, limit, lines }
     */
    public function show(ServerRequestInterface $request, string $date): ResponseInterface
    {
        // 1) 校验日期格式（防路径穿越）
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return JsonResponse::validation([
                'date' => ['日期格式必须为 YYYY-MM-DD'],
            ]);
        }

        // 2) 构造文件路径并检查是否存在
        $filePath = $this->logPath . '/' . self::FILE_PREFIX . $date;

        if (!is_file($filePath)) {
            return JsonResponse::error(
                JsonResponse::CODE_NOT_FOUND,
                "日志文件不存在：{$date}",
                404,
            );
        }

        // 3) 读取查询参数
        $queryParams = $request->getQueryParams();
        $offset      = max(0, (int) ($queryParams['offset'] ?? 0));
        $limit       = min(2000, max(1, (int) ($queryParams['limit'] ?? 500)));
        $levelFilter = strtolower((string) ($queryParams['level'] ?? ''));

        // 4) 读取文件内容
        $content = file_get_contents($filePath);
        if ($content === false) {
            return JsonResponse::error(
                JsonResponse::CODE_INTERNAL_ERROR,
                '日志文件读取失败',
                500,
            );
        }

        // 5) 按行拆分
        $allLines = preg_split('/\r?\n/', $content) ?: [];
        // 移除末尾空行
        if (end($allLines) === '') {
            array_pop($allLines);
        }

        // 6) 按级别过滤
        if ($levelFilter !== '') {
            $allLines = array_filter($allLines, static function (string $line) use ($levelFilter): bool {
                // 日志格式：[datetime] app.LEVEL: message ...
                return str_contains($line, ".{$levelFilter}:") || str_contains($line, ".{$levelFilter} ");
            });
            $allLines = array_values($allLines);
        }

        // 7) 分页
        $totalLines = count($allLines);
        $lines      = array_slice($allLines, $offset, $limit);

        return JsonResponse::ok([
            'date'       => $date,
            'fileName'   => self::FILE_PREFIX . $date,
            'totalLines' => $totalLines,
            'offset'     => $offset,
            'limit'      => $limit,
            'lines'      => $lines,
        ]);
    }

    /**
     * 扫描日志目录，返回文件信息列表
     *
     * @return array<int, array{date: string, fileName: string, size: int, sizeHuman: string}>
     */
    private function scanLogFiles(): array
    {
        $result = [];
        $pattern = $this->logPath . '/' . self::FILE_PREFIX . '*';

        foreach (glob($pattern) as $filePath) {
            if (!is_file($filePath)) {
                continue;
            }

            $fileName = basename($filePath);
            // 提取日期部分：app-2026-09-08 → 2026-09-08
            $date = substr($fileName, strlen(self::FILE_PREFIX));

            // 只保留合法日期格式的文件
            if ($date === false || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                continue;
            }

            $size = filesize($filePath);

            $result[] = [
                'date'      => $date,
                'fileName'  => $fileName,
                'size'      => $size,
                'sizeHuman' => self::formatBytes($size),
            ];
        }

        return $result;
    }

    /**
     * 格式化文件大小为可读字符串
     *
     * 对应 Java：org.apache.commons.io.FileUtils.byteCountToDisplaySize()
     */
    private static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        $size = (float) $bytes;

        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return number_format($size, $i === 0 ? 0 : 1) . ' ' . $units[$i];
    }
}
