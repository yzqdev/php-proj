<?php
declare(strict_types=1);

/**
 * 日志记录器（基于 Monolog）
 *
 * 封装 Monolog，提供静态方法接口。
 * 日志按日期分割存储，JSON Lines 格式。
 *
 * 用法：
 *   require_once 'lib/Logger.php';
 *   Logger::info('服务器启动', ['version' => '2.0']);
 *   Logger::error('错误消息', ['error' => $e->getMessage()]);
 *   Logger::test($testResults);
 *   Logger::getRecent(50);
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\JsonFormatter;
use Psr\Log\LogLevel;

class Logger
{
    /** @var MonologLogger|null 单例实例 */
    private static ?MonologLogger $instance = null;

    /** @var string 日志目录 */
    private static string $logDir = '';

    /** @var string 当前日志文件路径 */
    private static string $currentFile = '';

    /** @var int 日志保留天数 */
    public const RETENTION_DAYS = 30;

    /**
     * 初始化 Monolog 日志器
     */
    private static function init(): MonologLogger
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        self::$logDir = dirname(__DIR__) . '/logs';
        self::$currentFile = self::$logDir . '/speedtest-' . date('Y-m-d') . '.log';

        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }

        // 使用 StreamHandler 按日期分割日志
        $handler = new StreamHandler(self::$currentFile, LogLevel::INFO, true);

        // JSON Lines 格式
        $formatter = new JsonFormatter();
        $handler->setFormatter($formatter);

        $logger = new MonologLogger('speedtest');
        $logger->pushHandler($handler);

        self::$instance = $logger;
        return $logger;
    }

    /**
     * 获取 Monolog 实例
     */
    public static function getInstance(): MonologLogger
    {
        return self::init();
    }

    /**
     * 记录信息日志
     */
    public static function info(string $message, array $context = []): void
    {
        self::init()->info($message, $context);
    }

    /**
     * 记录警告日志
     */
    public static function warning(string $message, array $context = []): void
    {
        self::init()->warning($message, $context);
    }

    /**
     * 记录错误日志
     */
    public static function error(string $message, array $context = []): void
    {
        self::init()->error($message, $context);
    }

    /**
     * 记录测速结果（便捷方法）
     */
    public static function test(array $results): void
    {
        self::init()->info('测速结果', $results);
    }

    /**
     * 获取日志目录路径
     */
    public static function getLogDir(): string
    {
        self::init();
        return self::$logDir;
    }

    /**
     * 获取当前日志文件路径
     */
    public static function getCurrentFile(): string
    {
        self::init();
        return self::$currentFile;
    }

    /**
     * 获取最近的日志条目
     *
     * @param int $limit 最大返回条数
     * @return array 日志条目数组（按时间倒序）
     */
    public static function getRecent(int $limit = 100): array
    {
        self::init();

        if (!file_exists(self::$currentFile)) {
            return [];
        }

        $lines = @file(self::$currentFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false || empty($lines)) {
            return [];
        }

        $entries = [];
        $startIdx = max(0, count($lines) - $limit);
        for ($i = $startIdx; $i < count($lines); $i++) {
            $entry = json_decode($lines[$i], true);
            if (is_array($entry)) {
                $entries[] = $entry;
            }
        }

        return array_reverse($entries);
    }

    /**
     * 获取今日日志统计
     */
    public static function getStats(): array
    {
        self::init();

        if (!file_exists(self::$currentFile)) {
            return [
                'total' => 0, 'tests' => 0, 'errors' => 0,
                'file_size' => 0, 'file_size_human' => '0 B',
            ];
        }

        $lines = @file(self::$currentFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $total = is_array($lines) ? count($lines) : 0;
        $tests = 0;
        $errors = 0;

        if (is_array($lines)) {
            foreach ($lines as $line) {
                $entry = json_decode($line, true);
                if (is_array($entry)) {
                    if ($entry['message'] === '测速结果') $tests++;
                    if (($entry['level_name'] ?? '') === 'ERROR') $errors++;
                }
            }
        }

        $size = filesize(self::$currentFile);

        return [
            'total' => $total,
            'tests' => $tests,
            'errors' => $errors,
            'file_size' => $size,
            'file_size_human' => self::formatBytes($size),
        ];
    }

    /**
     * 清理过期日志文件
     *
     * @return int 删除的文件数量
     */
    public static function cleanup(int $retentionDays = self::RETENTION_DAYS): int
    {
        self::init();

        if (!is_dir(self::$logDir)) {
            return 0;
        }

        $deleted = 0;
        $cutoff = time() - $retentionDays * 86400;

        foreach (glob(self::$logDir . '/speedtest-*.log') as $file) {
            if (filemtime($file) < $cutoff) {
                if (@unlink($file)) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    /**
     * 格式化字节数
     */
    private static function formatBytes(int $bytes): string
    {
        return match (true) {
            $bytes < 1024       => $bytes . ' B',
            $bytes < 1048576    => round($bytes / 1024, 1) . ' KB',
            $bytes < 1073741824 => round($bytes / 1048576, 1) . ' MB',
            default             => round($bytes / 1073741824, 1) . ' GB',
        };
    }
}
