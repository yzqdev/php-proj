<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Yzqde\Fox\Middleware\CacheMiddleware;
use Yzqde\Fox\Routes\Web;
use Yzqde\Fox\Services\Logger;
use Yzqde\Fox\Util\DoctrineConfig;
use Yzqde\Fox\Util\RedisCache;
use Yzqde\Fox\Util\RedisCachePool;
use Yzqde\Fox\Util\RedisConfig;

$logger = new Logger('fox');

$app = AppFactory::create();

// ---------- Redis ----------
$redisConfig = new RedisConfig(
    host: '127.0.0.1',
    port: 6379,
    password: '123456',
);
$redis = $redisConfig->create();
$cache = new RedisCache($redis);

// ---------- Doctrine EntityManager ----------
$doctrineCache = new RedisCachePool($redis, 'fox:doctrine:');
$em = DoctrineConfig::create(
    [
        'host' => '127.0.0.1',
        'port' => 3306,
        'user' => 'root',
        'password' => '123456',
        'dbname' => 'fox',
    ],
    [],
    $doctrineCache,
);

// 将 EntityManager 和 RedisCache 注入到容器，方便控制器使用
$container = $app->getContainer();
if ($container !== null) {
    $container->set(\Doctrine\ORM\EntityManager::class, $em);
    $container->set(RedisCache::class, $cache);
}

// 日志中间件（最先注册，最后执行）
$app->add(function ($request, $handler) use ($logger) {
    $logger->info('Request', [
        'method' => $request->getMethod(),
        'uri' => $request->getUri()->getPath(),
        'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
    ]);
    return $handler->handle($request);
});

// 缓存中间件（后注册，先执行）—— 需在路由解析之后
$app->add(new CacheMiddleware(
    $cache,
    $app->getResponseFactory()
));

$webRoutes = new Web($logger, $em);
$webRoutes->register($app);

$app->run();
