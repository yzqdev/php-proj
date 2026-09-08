<?php
declare(strict_types=1);

namespace App\Support;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;

/**
 * Monolog v3 factory (hard constraint 7):
 *   - log level via Level enum, not Logger::DEBUG ints
 *   - StreamHandler 3rd param is bool $bubble
 *   - UidProcessor seeds a per-request uid; RequestIdMiddleware resets it
 *     to the X-Request-Id value so every record of one request shares it.
 */
final class LoggerFactory
{
    public static function create(): Logger
    {
        $logPath = (string) Env::get('LOG_PATH', 'storage/logs/app.log');

        // Anchor relative paths to the project root: the built-in dev server
        // chdir()s into public/, so './...' would otherwise land in public/.
        if (!preg_match('#^([A-Za-z]:[/\\\\]|/|\\\\\\\\|phar://)#', $logPath)) {
            $logPath = dirname(__DIR__, 2) . '/' . ltrim($logPath, './\\');
        }

        $logDir = dirname($logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $level = match (strtoupper((string) Env::get('LOG_LEVEL', 'Debug'))) {
            'DEBUG'     => Level::Debug,
            'INFO'      => Level::Info,
            'NOTICE'    => Level::Notice,
            'WARNING'   => Level::Warning,
            'ERROR'     => Level::Error,
            'CRITICAL'  => Level::Critical,
            'ALERT'     => Level::Alert,
            'EMERGENCY' => Level::Emergency,
            default     => Level::Debug,
        };

        $logger = new Logger('app');
        $logger->pushProcessor(new UidProcessor());
        $logger->pushHandler(new StreamHandler($logPath, $level, true));

        return $logger;
    }
}
