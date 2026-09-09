<?php

declare(strict_types=1);

namespace App\Doctrine;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

/**
 * Doctrine EntityManager 工厂（连接参数与迁移前一致：doctrine_demo 库，本机 root）。
 */
final class EntityManagerFactory
{
    public function create(): EntityManager
    {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: [dirname(__DIR__) . '/Entity'],
            isDevMode: true,
        );

        $connectionParams = [
            'dbname' => 'doctrine_demo',
            'user' => 'root',
            'password' => '123456',
            'host' => '127.0.0.1',
            'driver' => 'pdo_mysql',
            'charset' => 'utf8mb4',
            'serverVersion' => '8.0',
        ];

        $connection = DriverManager::getConnection($connectionParams, $config);

        return new EntityManager($connection, $config);
    }
}
