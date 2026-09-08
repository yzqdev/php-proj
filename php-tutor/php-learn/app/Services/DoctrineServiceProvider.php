<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Config;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * ============================================================
 * Doctrine 服务提供者
 * ============================================================
 *
 * 职责：
 *   - 从 config/doctrine.php 读配置，组装 EntityManager
 *   - 提供静态工厂 createEntityManager()，让 DI 容器 / CLI 脚本都能拿到同一个 EM
 *
 * 对比 Java / Spring Boot：
 *   - Spring 的 EntityManagerFactory bean（@Bean public EntityManagerFactory）
 *   - Spring 的 LocalContainerEntityManagerFactoryBean
 *
 * 关键 Doctrine 概念：
 *   - Configuration：ORM 配置（元数据驱动、缓存、代理目录）
 *   - EntityManager：工作单元入口（类似 Java 的 EntityManager）
 *   - Connection：底层 PDO 连接
 *
 * PHP 8.x 特性：
 *   - 属性注解（#[ORM\…]）替代 docblock 注解，更类型安全；
 *   - ORMSetup::createAttributeMetadataConfiguration() 是读属性注解的入口。
 */
class DoctrineServiceProvider
{
    /**
     * 组装并返回 Doctrine EntityManager
     *
     * 配置来源：config.php 的 'doctrine' 节点
     *
     * @return EntityManager
     */
    public static function createEntityManager(): EntityManager
    {
        $cfg = Config::get('doctrine', []);

        // 1) 元数据驱动：读 PHP 8 属性注解（#[ORM\Entity] 等）
        $metaConfig = ORMSetup::createAttributeMetadataConfiguration(
            $cfg['metadata_dirs'] ?? [],
            $cfg['debug'] ?? Config::get('app.debug', false),
            $cfg['proxy_dir'] ?? null,
        );

        // 启用 PHP 8.4+ 原生延迟加载对象（避免依赖 symfony/var-exporter 生成 LazyGhost）
        // 旧版 Doctrine 用 Proxy 类 + ProxyHelper 生成代理，需要 symfony/var-exporter；
        // PHP 8.4 原生 lazy objects 是更干净的替代方案。
        if (PHP_VERSION_ID >= 80400) {
            $metaConfig->enableNativeLazyObjects(true);
        }

        // 2) 缓存配置（开发期用 array，生产期建议用 file / apcu / redis）
        $cacheConfig = $cfg['cache'] ?? [];
        if (isset($cacheConfig['metadata']) && $cacheConfig['metadata'] === 'array') {
            $metaConfig->setMetadataCache(new ArrayAdapter());
        }

        // 3) 连接参数：复用 'db' 节点（单一数据源），避免与 Database::connection() 漂移
        $db = Config::get('db', []);
        $connectionParams = [
            'driver'   => 'pdo_mysql',
            'host'     => $db['host']     ?? '127.0.0.1',
            'port'     => (int)($db['port'] ?? 3306),
            'dbname'   => $db['name']     ?? 'php_learn',
            'user'     => $db['username'] ?? 'root',
            'password' => $db['password'] ?? '',
            'charset'  => $db['charset']  ?? 'utf8mb4',
        ];

        // 4) 组装 EntityManager（Doctrine 3.x 用 new EntityManager，不再提供静态 create()）
        $connection = DriverManager::getConnection($connectionParams);
        return new EntityManager($connection, $metaConfig);
    }
}