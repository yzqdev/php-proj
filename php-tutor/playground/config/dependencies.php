<?php

declare(strict_types=1);

/**
 * PHP-DI 容器定义:所有对象依赖统一在此装配,业务代码不再自行 new
 *
 * @return array<string, mixed> 传给 DI\Container 的定义数组
 */

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

use Yzqde\Playground\Middleware\AccessLogMiddleware;
use Yzqde\Playground\Middleware\LogAccessGuard;
use Yzqde\Playground\Service\ImageService;
use Yzqde\Playground\Service\LogService;

use Yzqde\Playground\Service\PersonService;
use Yzqde\Playground\Settings;

return [
    Settings::class => DI\factory(fn(): Settings => Settings::fromEnv(dirname(__DIR__))),

    // 数据库连接(DBAL):懒加载,首次真正查询时才建立 TCP 连接,未建库不会阻塞应用启动
    Connection::class => DI\factory(function (Settings $settings): Connection {
        return DriverManager::getConnection([
            'driver' => 'pdo_mysql',
            'host' => $settings->dbHost,
            'port' => $settings->dbPort,
            'dbname' => $settings->dbName,
            'user' => $settings->dbUser,
            'password' => $settings->dbPassword,
            'charset' => $settings->dbCharset,
        ]);
    }),

    // 双通道日志:app-* 记录应用日志(级别随 APP_DEBUG 区分开发/生产),error-* 只记错误且不再冒泡
    LoggerInterface ::class => DI\factory(function (Settings $settings): LoggerInterface {
        $logDir = __DIR__ . '/../storage/logs';
        if (!is_dir($logDir) && !mkdir($logDir, 0775, true)) {
            throw new RuntimeException('日志目录不可用:' . $logDir);
        }
        $logger = new Logger('app');
        $logger->pushHandler(new StreamHandler(
            sprintf('%s/app-%s.log', $logDir, date('Y-m-d')),
            $settings->debug ? Logger::DEBUG : Logger::INFO,
        ));
        $logger->pushHandler(new StreamHandler(
            sprintf('%s/error-%s.log', $logDir, date('Y-m-d')),
            Logger::ERROR,
            false,
        ));
        return $logger;
    }),

    ImageService::class => DI\factory(
        fn(Settings $settings): ImageService => new ImageService($settings->storagePath, $settings->maxUploadSize)
    ),

    // 路径必须在工厂内用 __DIR__ 现算:文件级局部变量在闭包惰性执行时已销毁
    // 缓存与锁文件均落在 storage/ 下,已被 gitignore(派生产物,不入版本库)




    // 日志查看:只读扫描 LoggerInterface 上面那两个 StreamHandler 写入的目录
    LogService::class => DI\factory(
        fn(): LogService => new LogService(__DIR__ . '/../storage/logs')
    ),

    // 人员管理:持久化到 MySQL,Connection 由上方工厂提供
    PersonService::class => DI\factory(
        fn(Connection $connection): PersonService => new PersonService($connection)
    ),

    // 日志接口只允许本机来源;LOG_VIEW_ALLOW_ANY=1 时放开(内网/生产调试用)
    LogAccessGuard::class => DI\factory(
        fn(Settings $settings): LogAccessGuard => new LogAccessGuard($settings->logViewAllowAny)
    ),

    AccessLogMiddleware::class => DI\factory(
        fn(LoggerInterface $logger): AccessLogMiddleware => new AccessLogMiddleware($logger)
    ),
];
