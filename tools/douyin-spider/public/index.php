<?php

declare(strict_types=1);

// Web 入口：唯一对外暴露的文件
// 启动命令：php -S localhost:8805 public/index.php

require __DIR__ . '/../vendor/autoload.php';

// 加载环境变量
if (file_exists(__DIR__ . '/../.env')) {
    \Dotenv\Dotenv::createImmutable(dirname(__DIR__))->load();
}

$container = require __DIR__ . '/../config/dependencies.php';

/** @var \Psr\Log\LoggerInterface $logger */
$logger = $container->get(\Psr\Log\LoggerInterface::class);
$debug = filter_var(env('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN);

$app = \Slim\Factory\AppFactory::create(container: $container);

// 全局中间件（ExceptionMiddleware 无法捕获 RoutingMiddleware 的 HttpNotFoundException，需外层兜底）
$app->add(new \Yzqde\DouyinSpider\Middleware\ExceptionMiddleware($logger, $debug));
$app->add(new \Yzqde\DouyinSpider\Middleware\CorsMiddleware());
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// 路由注册（集中管理在 routes/api.php）
Routes\api($app, $container);

try {
    $app->run();
} catch (\Slim\Exception\HttpNotFoundException $e) {
    // RoutingMiddleware 抛出的404，ExceptionMiddleware 无法捕获，在此兜底返回JSON
    $response = (new \Slim\Psr7\Factory\ResponseFactory())->createResponse(404);
    $body = (new \Slim\Psr7\Factory\StreamFactory())->createStream(
        json_encode(['code' => -4, 'message' => 'Not Found', 'data' => null], JSON_UNESCAPED_UNICODE)
    );
    $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withBody($body);
    $response->getBody()->rewind();
    $out = fopen('php://output', 'w');
    fwrite($out, (string) $response->getBody());
    fclose($out);
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(404);
} catch (\Throwable $e) {
    $logger->error('未处理异常: ' . $e->getMessage(), ['class' => get_class($e), 'trace' => $e->getTraceAsString()]);
    $message = $debug ? $e->getMessage() : '服务器内部错误';
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['code' => -99, 'message' => $message, 'data' => null], JSON_UNESCAPED_UNICODE);
}
