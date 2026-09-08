<?php

declare(strict_types=1);

use App\Repository\UserRepository;
use App\Repository\FileRepository;
use App\Middleware\CorsMiddleware;
use App\Entity\User;
use App\Entity\File;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Logger;
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
        $logger->pushHandler(new StreamHandler($logDir . '/app.log', \Monolog\Level::Debug));
        return $logger;
    },


];