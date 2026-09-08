<?php

declare(strict_types=1);

// 设置服务器时区（Monolog 的 RotatingFileHandler 使用 date() 函数决定文件名日期）
date_default_timezone_set('Asia/Shanghai');

require __DIR__ . '/../vendor/autoload.php';

// 加载环境变量
if (file_exists(__DIR__ . '/../.env')) {
    \Dotenv\Dotenv::createImmutable(dirname(__DIR__))->load();
}

/**
 * 获取环境变量（兼容 PHP 8.2+ getenv() 不自动读取 .env 的问题）
 */
function env(string $key, mixed $default = null): mixed
{
    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }
    // PHP 8.2+ getenv() 不读取 .env，需要从 $_ENV 取
    if (isset($_ENV[$key])) {
        return $_ENV[$key];
    }
    // 从 $_SERVER 取
    if (isset($_SERVER[$key])) {
        return $_SERVER[$key];
    }
    return $default;
}

$debug = filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN);

$dbParams = [
    'driver' => env('DB_DRIVER') ?: 'pdo_mysql',
    'host' => env('DB_HOST') ?: '127.0.0.1',
    'port' => (int) (env('DB_PORT') ?: 3306),
    'dbname' => env('DB_DATABASE') ?: 'douyin_spider',
    'user' => env('DB_USERNAME') ?: 'root',
    'password' => env('DB_PASSWORD') ?: '',
    'charset' => env('DB_CHARSET') ?: 'utf8mb4',
];

// Doctrine 元数据配置（Attribute 驱动；空代理路径，避免 PHP 8.4+ 下旧 ProxyFactory 逻辑）
$metadataConfig = \Doctrine\ORM\ORMSetup::createAttributeMetadataConfiguration(
    paths: [__DIR__ . '/../src/Model'],
    isDevMode: $debug,
);
$metadataConfig->enableNativeLazyObjects(true);
$metadataConfig->setProxyDir('');
$metadataConfig->setProxyNamespace('');

// 手动创建 Logger（容器初始化阶段的 Fatal Error 由 shutdown 函数兜底写入日志）
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
$logLevel = $debug ? \Monolog\Logger::DEBUG : \Monolog\Logger::INFO;
$logger = new \Monolog\Logger('app');
$fileHandler = new \Monolog\Handler\RotatingFileHandler($logDir . '/app.log', 30, $logLevel);
$fileHandler->setFilenameFormat('{filename}-{date}', 'Y-m-d');
$logger->pushHandler($fileHandler);
$logger->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout', $logLevel));

register_shutdown_function(function () use ($logger): void {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        // 跳过已由 ExceptionMiddleware 处理的 Http 异常（Slim 内部路由 404/405 等）
        $message = $error['message'] ?? '';
        if (str_contains($message, 'Slim\\Exception\\Http')) {
            return;
        }
        $logger->error(
            '[FATAL] ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line'],
            ['type' => $error['type']],
        );
    }
});

// 构建容器：全部使用显式闭包定义并关闭 autowire，规避 PHP-DI 在 PHP 8.5 下 \DI\autowire() 的无限递归问题
$container = (new \DI\ContainerBuilder())
    ->useAutowiring(false)
    ->addDefinitions([
        \Psr\Log\LoggerInterface::class => static fn (): \Psr\Log\LoggerInterface => $logger,

        \Doctrine\DBAL\Connection::class => static fn (): \Doctrine\DBAL\Connection => \Doctrine\DBAL\DriverManager::getConnection($dbParams, $metadataConfig),

        \Doctrine\ORM\EntityManagerInterface::class => static fn ($c): \Doctrine\ORM\EntityManager => new \Doctrine\ORM\EntityManager(
            $c->get(\Doctrine\DBAL\Connection::class),
            $metadataConfig,
        ),

        \Yzqde\DouyinSpider\Repository\UserRepository::class => static fn ($c) => $c->get(\Doctrine\ORM\EntityManagerInterface::class)
            ->getRepository(\Yzqde\DouyinSpider\Model\User::class),

        \Yzqde\DouyinSpider\Repository\ArticleRepository::class => static fn ($c) => $c->get(\Doctrine\ORM\EntityManagerInterface::class)
            ->getRepository(\Yzqde\DouyinSpider\Model\Article::class),

        \Yzqde\DouyinSpider\Service\AuthService::class => static fn ($c) => new \Yzqde\DouyinSpider\Service\AuthService(
            $c->get(\Doctrine\ORM\EntityManagerInterface::class),
            $c->get(\Yzqde\DouyinSpider\Repository\UserRepository::class),
            $c->get(\Psr\Log\LoggerInterface::class),
        ),

        \Yzqde\DouyinSpider\Service\ArticleService::class => static fn ($c) => new \Yzqde\DouyinSpider\Service\ArticleService(
            $c->get(\Doctrine\ORM\EntityManagerInterface::class),
            $c->get(\Yzqde\DouyinSpider\Repository\ArticleRepository::class),
            $c->get(\Psr\Log\LoggerInterface::class),
        ),

        \Yzqde\DouyinSpider\Controller\AuthController::class => static fn ($c) => new \Yzqde\DouyinSpider\Controller\AuthController(
            $c->get(\Yzqde\DouyinSpider\Service\AuthService::class),
        ),

        \Yzqde\DouyinSpider\Controller\ArticleController::class => static fn ($c) => new \Yzqde\DouyinSpider\Controller\ArticleController(
            $c->get(\Yzqde\DouyinSpider\Service\ArticleService::class),
        ),

        \Yzqde\DouyinSpider\Middleware\JwtAuthMiddleware::class => static fn ($c) => new \Yzqde\DouyinSpider\Middleware\JwtAuthMiddleware(
            $c->get(\Psr\Log\LoggerInterface::class),
        ),

        \Yzqde\DouyinSpider\Middleware\CorsMiddleware::class => static fn (): \Yzqde\DouyinSpider\Middleware\CorsMiddleware => new \Yzqde\DouyinSpider\Middleware\CorsMiddleware(),

        \Yzqde\DouyinSpider\Middleware\ExceptionMiddleware::class => static fn ($c) => new \Yzqde\DouyinSpider\Middleware\ExceptionMiddleware(
            $c->get(\Psr\Log\LoggerInterface::class),
            $debug,
        ),
    ])
    ->build();

return $container;