<?php

declare(strict_types=1);

namespace Yzqde\Fox\Services;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class Logger
{
    private MonologLogger $logger;

    public function __construct(string $name = 'app', string $logDir = __DIR__ . '/../../logs')
    {
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $this->logger = new MonologLogger($name);

        $handler = new RotatingFileHandler(
            filename: $logDir . '/app.log',
            maxFiles: 30,
            level: MonologLogger::DEBUG
        );

        $handler->setFormatter(new LineFormatter(
            format: "%datetime% > %level_name% > %message% %context% %extra%\n",
            dateFormat: 'Y-m-d H:i:s',
            allowInlineLineBreaks: true
        ));

        $this->logger->pushHandler($handler);
    }

    public function getLogger(): MonologLogger
    {
        return $this->logger;
    }

    public function debug(string $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }
}
