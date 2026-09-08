<?php
declare(strict_types=1);

/**
 * 路由与中间件注册。
 *
 * Slim 中间件按 LIFO 执行(后添加者先执行),故添加顺序为:
 * BodyParsing(最内,紧贴路由处理器) → Routing → AccessLog → Error → CORS(最外)。
 * 这样:错误响应也携带 CORS 头;AccessLog 能记录异常请求;Body 解析发生在控制器前。
 */

use App\Controllers\AuthController;
use App\Controllers\DocsController;
use App\Controllers\PostController;
use App\Controllers\UserController;
use App\Http\ApiErrorHandler;
use App\Middleware\AccessLogMiddleware;
use App\Middleware\AuthMiddleware;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Tuupola\Middleware\CorsMiddleware;

return static function (App $app, ContainerInterface $container): App {
    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();
    $app->add(AccessLogMiddleware::class);

    $errorMiddleware = $app->addErrorMiddleware(
        (bool) app_config('debug'),
        true,
        true,
    );
    // 使用自定义错误处理器(实例,已注入全部依赖),实例本身即 invokable
    $errorMiddleware->setDefaultErrorHandler($container->get(ApiErrorHandler::class));

    $app->add(new CorsMiddleware([
        'origin' => app_config('cors.origins'),
        'methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        'headers.allow' => ['Authorization', 'Content-Type', 'Accept'],
        'headers.expose' => [],
        'credentials' => true,
        'cache' => 86400,
    ]));

    $app->group('/api/v1', static function (RouteCollectorProxy $group): void {
        $group->post('/auth/register', [AuthController::class, 'register']);
        $group->post('/auth/login', [AuthController::class, 'login']);
        $group->get('/auth/me', [AuthController::class, 'me'])->add(AuthMiddleware::class);

        $group->get('/posts', [PostController::class, 'index']);
        $group->get('/posts/{id}', [PostController::class, 'show']);
        $group->post('/posts', [PostController::class, 'create'])->add(AuthMiddleware::class);
        $group->put('/posts/{id}', [PostController::class, 'update'])->add(AuthMiddleware::class);
        $group->delete('/posts/{id}', [PostController::class, 'delete'])->add(AuthMiddleware::class);

        $group->get('/users/{id}', [UserController::class, 'show']);
    });

    $app->get('/docs', [DocsController::class, 'docs']);
    $app->get('/docs/openapi.json', [DocsController::class, 'openapi']);

    return $app;
};
