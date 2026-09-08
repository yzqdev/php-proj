<?php

declare(strict_types=1);

/**
 * 图床 API 唯一入口(纯 JSON API,页面由 web/ 下的 Vue 项目负责)
 *
 * 开发环境启动:
 *   php -S localhost:8080 -t public public/index.php
 * 本脚本作为 cli-server 的路由脚本,真实存在的静态资源直接放行,其余请求交给 Slim。
 */

// cli-server 下:请求的是 public/ 内真实文件时返回 false,由内置服务器直接输出
if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
    $file = realpath(__DIR__ . $path);
    if ($path !== '/' && $file !== false
        && str_starts_with($file, __DIR__ . DIRECTORY_SEPARATOR) && is_file($file)) {
        return false;
    }
}

require dirname(__DIR__) . '/vendor/autoload.php';

use DI\Container;
use Dotenv\Dotenv;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Yzqde\Playground\Controller\ImageController;
use Yzqde\Playground\Controller\LogController;
use Yzqde\Playground\Controller\OpenApiDocsController;
use Yzqde\Playground\Controller\PersonController;
use Yzqde\Playground\Controller\SwaggerController;
use Yzqde\Playground\Middleware\AccessLogMiddleware;
use Yzqde\Playground\Service\PersonService;
use Yzqde\Playground\Middleware\CorsMiddleware;
use Yzqde\Playground\Middleware\CsrfMiddleware;
use Yzqde\Playground\Middleware\JsonErrorRenderer;
use Yzqde\Playground\Middleware\LogAccessGuard;
use Yzqde\Playground\Settings;

$baseDir = dirname(__DIR__);
Dotenv::createUnsafeImmutable($baseDir)->safeLoad(); // .env 缺失不报错,按默认值运行

// 依赖统一由 PHP-DI 装配,业务代码不自行 new
$container = new Container(require $baseDir . '/config/dependencies.php');
AppFactory::setContainer($container);

$app = AppFactory::create();

$settings = $container->get(Settings::class);
$app->addRoutingMiddleware();
// 缺了它 JSON 请求体不会被解析,getParsedBody() 恒为空数组,所有校验都判失败
$app->addBodyParsingMiddleware();
$app->add(new CsrfMiddleware());

// 统一错误输出:JSON 结构 + 写入 Monolog;业务异常展示原文,其余不泄露内部信息
$errorMiddleware = $app->addErrorMiddleware($settings->debug, true, true);
$errorMiddleware->getDefaultErrorHandler()->forceContentType('application/json');
$errorMiddleware->getDefaultErrorHandler()->registerErrorRenderer(
    'application/json',
    new JsonErrorRenderer($container->get(LoggerInterface::class)),
);

// CORS 声明为最外层:错误响应同样携带跨域头,OPTIONS 预检在此短路
$app->add(new CorsMiddleware());

// 访问日志挂在全栈最外:响应已由谁生成(含错误中间件把异常转成的响应)都能记录到最终状态码与耗时
$app->add(AccessLogMiddleware::class);

$app->get('/api/health', [ImageController::class, 'health']);
$app->get('/api/images', [ImageController::class, 'list']);
$app->post('/api/images', [ImageController::class, 'upload']);
$app->delete('/api/images/{name}', [ImageController::class, 'delete']);
$app->get('/i/{name}', [ImageController::class, 'serve']);
$app->get('/download/{name}', [ImageController::class, 'download']);

// 日志查看:只读,路由级挂 LogAccessGuard 限制为本机来源(可用 .env 的 LOG_VIEW_ALLOW_ANY 放开)
$app->get('/api/logs', [LogController::class, 'list'])->add(LogAccessGuard::class);
$app->get('/api/logs/{name}', [LogController::class, 'tail'])->add(LogAccessGuard::class);

// 人员管理 CRUD
$app->get('/api/persons', [PersonController::class, 'list']);
$app->get('/api/personTool', [PersonController::class, 'personTool']);
$app->get('/api/persons/{id}', [PersonController::class, 'show']);
$app->post('/api/persons', [PersonController::class, 'create']);
$app->put('/api/persons/{id}', [PersonController::class, 'update']);
$app->delete('/api/persons/{id}', [PersonController::class, 'delete']);


// 接口文档:规范由 src/ 下的 OA 注解在运行时生成并缓存到 storage/openapi.json

$app->get('/swagger', [SwaggerController::class, 'index']);
$app->get('/swagger/json', [SwaggerController::class, 'json']);

$app->run();
