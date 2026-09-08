<?php

declare(strict_types=1);

use App\Repository\UserRepository;
use App\Repository\FileRepository;
use App\Entity\User;
use App\Entity\File;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use function DI\autowire;

// 注册基础设施，业务类由 PHP-DI autowiring 自动解析。

return [
    EntityManagerInterface::class => function (): EntityManagerInterface {
        /** @var EntityManager $em */
        $em = require __DIR__ . '/doctrine.php';
        return $em;
    },

    UserRepository::class => function (EntityManagerInterface $em): UserRepository {
        $class = $em->getClassMetadata(User::class);
        return new UserRepository($em, $class);
    },

    FileRepository::class => function (EntityManagerInterface $em): FileRepository {
        $class = $em->getClassMetadata(File::class);
        return new FileRepository($em, $class);
    },

    Logger::class => function (): Logger {
        $logger = new Logger('app');
        $logger->pushHandler(new StreamHandler('php://stderr', \Monolog\Level::Debug));
        $logDir = __DIR__ . '/../var/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        // 按天轮转：每天生成 app.log-YYYY-MM-DD.log，只保留最近 30 天
        // 文件名规则为 {filename}-{date}，此处传带 .log 后缀的名字，
        // 否则生成的文件是 app-YYYY-MM-DD（无后缀），LogController 读不到。
        $logger->pushHandler(new RotatingFileHandler(
            filename: $logDir . '/app.log',
            maxFiles: 30,
            level: \Monolog\Level::Debug,
            useLocking: true,
        ));
        return $logger;
    },


];