<?php

declare(strict_types=1);

// 冒烟测试：验证容器构建、各依赖解析、数据库连接与表结构
$container = require __DIR__ . '/config/dependencies.php';
echo '[OK] container: ' . get_class($container) . PHP_EOL;

$userRepo = $container->get(\Yzqde\DouyinSpider\Repository\UserRepository::class);
echo '[OK] UserRepository: ' . get_class($userRepo) . PHP_EOL;

$articleRepo = $container->get(\Yzqde\DouyinSpider\Repository\ArticleRepository::class);
echo '[OK] ArticleRepository: ' . get_class($articleRepo) . PHP_EOL;

$authService = $container->get(\Yzqde\DouyinSpider\Service\AuthService::class);
echo '[OK] AuthService: ' . get_class($authService) . PHP_EOL;

$articleService = $container->get(\Yzqde\DouyinSpider\Service\ArticleService::class);
echo '[OK] ArticleService: ' . get_class($articleService) . PHP_EOL;

$jwt = $container->get(\Yzqde\DouyinSpider\Middleware\JwtAuthMiddleware::class);
echo '[OK] JwtAuthMiddleware: ' . get_class($jwt) . PHP_EOL;

$cors = $container->get(\Yzqde\DouyinSpider\Middleware\CorsMiddleware::class);
echo '[OK] CorsMiddleware: ' . get_class($cors) . PHP_EOL;

$conn = $container->get(\Doctrine\DBAL\Connection::class);

try {
    $row = $conn->executeQuery('SELECT 1 AS ok')->fetchAssociative();
    echo '[OK] DB connected: ' . json_encode($row) . PHP_EOL;

    $tables = $conn->executeQuery('SHOW TABLES')->fetchFirstColumn();
    echo '[OK] Tables: ' . json_encode($tables) . PHP_EOL;
} catch (\Throwable $e) {
    echo '[FAIL] DB error: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}

echo 'SMOKE TEST PASSED' . PHP_EOL;
