<?php

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * @var array{app: array{name: string, env: string}, logger: array{name: string, level: Level}} $settings
 */
$settings = require __DIR__ . '/settings.php';

return [
    'settings' => static fn (): array => $settings,

    LoggerInterface::class => static function () use ($settings): LoggerInterface {
        $logger = new Logger($settings['logger']['name']);
        $logger->pushHandler(new StreamHandler('php://stderr', $settings['logger']['level']));

        return $logger;
    },
];
