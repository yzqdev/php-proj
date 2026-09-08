<?php

declare(strict_types=1);

namespace Service\Log;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * 日志工厂：构建 PSR-3 日志器，同时写往 php://stderr 与按天切分的日志文件。
 * PHP 内置服务器会把 stderr 显示在启动终端（控制台），便于本地开发观察请求与异常；
 * 文件日志 storage/logs/app-YYYY-MM-DD.log 供日志查询接口（LogStore / LogController）读取。
 */
final class LoggerFactory
{
    /**
     * 单条日志格式：时间 [通道.级别] 消息 上下文JSON
     */
    private const FORMAT = "[%datetime%] %channel%.%level_name%: %message% %context%\n";

    public function create(): LoggerInterface
    {
        $logger = new Logger('app');

        $formatter = new LineFormatter(self::FORMAT, 'Y-m-d H:i:s', true, true);
        // 上下文以单行 JSON 追加，控制台阅读友好
        $formatter->includeStacktraces(false);

        $console = new StreamHandler('php://stderr', Logger::DEBUG);
        $console->setFormatter($formatter);
        $logger->pushHandler($console);

        // Monolog 不负责创建目录，需自行 mkdir；启用了文件锁，避免 php -S 并发写串行化竞争
        $logDir = dirname(__DIR__, 3) . '/storage/logs';
        if (!is_dir($logDir) && !mkdir($logDir, 0755, true) && !is_dir($logDir)) {
            throw new RuntimeException('日志目录创建失败：' . $logDir);
        }
        $daily = new StreamHandler($logDir . '/app-' . date('Y-m-d') . '.log', Logger::DEBUG, true, null, true);
        $daily->setFormatter($formatter);
        $logger->pushHandler($daily);

        return $logger;
    }
}
