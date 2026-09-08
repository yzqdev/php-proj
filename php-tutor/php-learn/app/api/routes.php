<?php

/**
 * ============================================================
 * API v1 路由表 — Slim 4 版
 * ============================================================
 *
 * 与 app/routes.php（旧 HTML 视图路由）物理隔离：
 *   - routes.php    → 旧 Controller 渲染 HTML 视图（Session 鉴权）
 *   - api/routes.php → 新 API Controller 输出 JSON（Bearer Token 鉴权）
 *
 * 关键设计：
 *   - 所有路径统一前缀 /api/v1（版本号在路径里，便于未来 v2 并存）
 *   - CORS 中间件在 bootstrap.php 里统一加（全局中间件）
 *   - 需鉴权的路由挂 ApiAuthenticate 中间件（Slim PSR-15）
 *   - 用 PUT/DELETE/PATCH 表达完整 REST 语义
 *
 * 对比 Java / Spring Boot：
 *   - Spring 用 @RequestMapping("/api/v1/...") 类级别前缀
 *   - Slim 用 $app->group('/api/v1', ...) 组前缀
 *
 * 注意：本文件被 bootstrap.php require，此时 $app 已经创建好，
 *       所以这里直接用 $app->get() / $app->post() 等注册路由。
 */
declare(strict_types=1);

use App\Controllers\Api\ActivityController as ApiActivityController;

use App\Controllers\Api\ArticleController as ApiArticleController;
use App\Controllers\Api\AuthController as ApiAuthController;
use App\Controllers\Api\HealthController as ApiHealthController;
use App\Controllers\Api\LogController;
use App\Controllers\Api\SwaggerController;
use App\Controllers\Api\ToolController;
use App\Middleware\ApiAuthenticate;
use App\Middleware\RequestLogger;
use Slim\App;


/**
 * 注册所有 /api/v1/* 路由
 *
 * @param App $app Slim 应用实例
 */
return function (App $app): void {
    // -----------------------------------------------------------------------
    // 健康检查（不需要鉴权）
    // -----------------------------------------------------------------------
    $app->get('/api/v1/health', [ApiHealthController::class, 'check'])
        ->add(new RequestLogger());

    // -----------------------------------------------------------------------
    // API 文档（Swagger UI + OpenAPI spec）
    // 不需要鉴权：文档是公开信息
    // -----------------------------------------------------------------------


    // -----------------------------------------------------------------------
    // 认证（Bearer Token）
    // -----------------------------------------------------------------------
    $app->post('/api/v1/auth/login',   [ApiAuthController::class, 'login']);
    $app->post('/api/v1/auth/refresh', [ApiAuthController::class, 'refresh']);
    $app->post('/api/v1/auth/logout',  [ApiAuthController::class, 'logout'])
        ->add(new ApiAuthenticate());
    $app->get('/api/v1/auth/me',       [ApiAuthController::class, 'me'])
        ->add(new ApiAuthenticate());

    // -----------------------------------------------------------------------
    // 文章管理（完整 CRUD + 分页）
    // -----------------------------------------------------------------------
    $app->get('/api/v1/articles',      [ApiArticleController::class, 'index']);
    $app->get('/api/v1/articles/{id}', [ApiArticleController::class, 'show']);
    $app->post('/api/v1/articles',     [ApiArticleController::class, 'store'])
        ->add(new ApiAuthenticate());
    $app->put('/api/v1/articles/{id}', [ApiArticleController::class, 'update'])
        ->add(new ApiAuthenticate());
    $app->delete('/api/v1/articles/{id}', [ApiArticleController::class, 'destroy'])
        ->add(new ApiAuthenticate());

    // -----------------------------------------------------------------------
    // 活动管理（完整 CRUD + 分页）
    // -----------------------------------------------------------------------
    $app->get('/api/v1/activities',      [ApiActivityController::class, 'index']);
    $app->get('/api/v1/activities/{id}', [ApiActivityController::class, 'show']);
    $app->post('/api/v1/activities',     [ApiActivityController::class, 'store'])
        ->add(new ApiAuthenticate());
    $app->put('/api/v1/activities/{id}', [ApiActivityController::class, 'update'])
        ->add(new ApiAuthenticate());
    $app->delete('/api/v1/activities/{id}', [ApiActivityController::class, 'destroy'])
        ->add(new ApiAuthenticate());

    // -----------------------------------------------------------------------
    // 系统日志查看（仅管理员可访问）
    // -----------------------------------------------------------------------
    $app->get('/api/v1/logs',           [LogController::class, 'index'])
        ->add(new ApiAuthenticate());
    $app->get('/api/v1/logs/{date}',    [LogController::class, 'show'])
        ->add(new ApiAuthenticate());


    $app->get('/api/v1/tool/check',           [ToolController::class, 'check']);


    $app->get('/swagger', [SwaggerController::class, 'index']);
    $app->get('/swagger/json', [SwaggerController::class, 'json']);
};