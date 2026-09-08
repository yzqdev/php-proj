<?php

declare(strict_types=1);

// 必须在构造 Logger 之前设置：RotatingFileHandler 按当前时区决定当天日志文件名，
// 不设置时 PHP 默认 UTC，会按 UTC 日期建文件，和服务器本地日期差一天。
date_default_timezone_set('Asia/Shanghai');

require_once __DIR__ . '/../vendor/autoload.php';

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Middleware\CorsMiddleware;

// 构建 PHP-DI 容器（autowiring 默认开启，业务类自动解析）
$builder = new ContainerBuilder();

$builder->addDefinitions(require __DIR__ . '/../config/container.php');
$container = $builder->build();

// 创建 Slim 应用
AppFactory::setContainer($container);
$app = AppFactory::create();

// 加载路由
require __DIR__ . '/../config/routes.php';

// ─── 错误处理器 ───────────────────────────────────────────
// 捕获异常，返回统一 JSON 格式
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler(function (
    Request $request,
    Throwable $e,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
) use ($app): Response {
    $status = 500;
    $message = 'Internal server error';

    if ($e instanceof HttpNotFoundException) {
        $status = 404;
        $message = 'Not found';
    } elseif ($e instanceof HttpException) {
        $status = $e->getCode();
        $message = $e->getMessage();
    }

    $body = json_encode([
        'code'    => $status,
        'message' => $message,
        'data'    => $displayErrorDetails ? ['exception' => $e->getMessage()] : null,
    ], JSON_UNESCAPED_UNICODE);

    $response = $app->getResponseFactory()->createResponse();
    $response->getBody()->write($body);
    return $response
        ->withStatus($status)
        ->withHeader('Content-Type', 'application/json; charset=utf-8');
});

// ─── CORS 中间件 ──────────────────────────────────────────
// 开发环境允许所有来源跨域访问，支持所有常用 HTTP 方法
// 必须最后注册：Slim 后注册者在最外层，否则 404/405/500 的响应不会带跨域头
$app->add($container->get(CorsMiddleware::class));

$app->run();