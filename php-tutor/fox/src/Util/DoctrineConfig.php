<?php

declare(strict_types=1);

namespace Yzqde\Fox\Util;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Doctrine EntityManager 工厂。
 *
 * 提供 create() 方法创建 EntityManager 实例，
 * 项目启动时调用一次即可。
 */
class DoctrineConfig
{
    /**
     * 创建 EntityManager。
     *
     * @param array<string, mixed> $dbParams 数据库连接参数
     * @param string[]             $paths    Entity 扫描目录
     */
    public static function create(array $dbParams, array $paths = [], ?CacheItemPoolInterface $cache = null): EntityManager
    {
        if (empty($paths)) {
            $paths = [dirname(__DIR__) . '/Entity'];
        }

        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: $paths,
            isDevMode: true,
            cache: $cache,
        );

        // 代理类目录
        $proxyDir = dirname(__DIR__, 2) . '/cache/doctrine/proxies';
        $proxyDir = is_dir($proxyDir) ? $proxyDir : sys_get_temp_dir() . '/fox-doctrine-proxy';
        $config->setProxyDir($proxyDir);
        $config->setProxyNamespace('Yzqde\Fox\Proxies');

        $connectionParams = [
            'dbname' => $dbParams['dbname'] ?? 'fox',
            'user' => $dbParams['user'] ?? 'root',
            'password' => $dbParams['password'] ?? '',
            'host' => $dbParams['host'] ?? '127.0.0.1',
            'port' => $dbParams['port'] ?? 3306,
            'driver' => 'pdo_mysql',
            'charset' => $dbParams['charset'] ?? 'utf8mb4',
            'driverOptions' => [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ],
        ];

        $connection = DriverManager::getConnection($connectionParams);

        return new EntityManager($connection, $config);
    }
}
