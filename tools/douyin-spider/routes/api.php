<?php

declare(strict_types=1);

namespace Routes;

use Psr\Container\ContainerInterface;
use Slim\App;
use Yzqde\DouyinSpider\Controller\ArticleController;
use Yzqde\DouyinSpider\Controller\AuthController;
use Yzqde\DouyinSpider\Middleware\JwtAuthMiddleware;

/**
 * 注册所有 API 路由到 Slim App 实例
 *
 * 公开路由：注册、登录、刷新 Token
 * 需认证路由：文章 CRUD（JWT 中间件保护）
 */
function api(App $app, ContainerInterface $container): void
{
    // 公开路由（无需认证）
    $app->group('/api', function ($router) use ($container) {
        $authController = $container->get(AuthController::class);
        $router->post('/auth/register', [$authController, 'register']);
        $router->post('/auth/login', [$authController, 'login']);
        $router->post('/auth/refresh', [$authController, 'refreshToken']);
    });

    // 需认证的路由
    $app->group('/api', function ($router) use ($container) {
        $articleController = $container->get(ArticleController::class);
        $jwtMiddleware = $container->get(JwtAuthMiddleware::class);

        // 浏览文章：所有登录用户可访问
        $router->get('/articles', [$articleController, 'index']);
        $router->get('/articles/{id}', [$articleController, 'show']);

        // 写操作：仅 editor/admin（在控制器内做角色校验）
        $router->post('/articles', [$articleController, 'store']);
        $router->put('/articles/{id}', [$articleController, 'update']);
        $router->delete('/articles/{id}', [$articleController, 'delete']);
    })->add($container->get(JwtAuthMiddleware::class));
}
