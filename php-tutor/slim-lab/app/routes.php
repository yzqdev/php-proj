<?php

declare(strict_types=1);

use App\Controller\AuthController;
use App\Controller\CloudDriveController;
use App\Controller\Docs\DocsController;
use App\Controller\Docs\SwaggerController;
use App\Controller\DoctrineController;
use App\Controller\LegacyModuleController;
use App\Controller\LogController;
use App\Controller\PhpDemoController;
use App\Middleware\ApiAuthMiddleware;
use App\Middleware\CloudDriveAuthMiddleware;
use App\Middleware\CorsMiddleware;
use App\Middleware\RequestLoggerMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Middleware\SessionMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

/**
 * 全部路由定义（Slim 4）。
 *
 * 接口地址规范（现代风格，无 .php 后缀）：
 *   - /api/php-demo、/api/doctrine、/api/clouddrive（前端与 Swagger 文档均使用此形式）
 *   - /api/{module}.php 保留为兼容别名（与迁移前 URL 一致，老书签/旧调用不受影响）
 *   - 兼容旧直连入口 /（原 index.php 的 ?module= 分发，行为逐分支一致）
 *   - 新增 /docs 与 /docs/openapi.json
 *
 * 中间件挂载顺序（Slim 后添加者先执行）：
 *   CorsMiddleware（最外层，含 OPTIONS 204 短路）
 *     → ErrorMiddleware（JSON 信封兜底）
 *       → 路由级 SessionMiddleware / CloudDriveAuthMiddleware（仅 clouddrive）
 */
return function (App $app): void {
    // ---------------- 认证（Bearer Token，与网盘 session 互不干扰） ----------------
    // 登录/注册经 Redis 限流：同 IP 60 秒内最多 10 次尝试，超出返回 429
    $app->post('/api/auth/register', [AuthController::class, 'register'])
        ->add(RateLimitMiddleware::class);
    $app->post('/api/auth/login', [AuthController::class, 'login'])
        ->add(RateLimitMiddleware::class);
    $app->post('/api/auth/logout', [AuthController::class, 'logout']);
    $app->get('/api/auth/userInfo', [AuthController::class, 'userInfo']);

    // ---------------- 运行日志（Bearer Token 鉴权；日志含内部路径与异常信息，不对外开放） ----------------
    $app->get('/api/logs/days', [LogController::class, 'days'])->add(ApiAuthMiddleware::class);
    $app->get('/api/logs', [LogController::class, 'index'])->add(ApiAuthMiddleware::class);

    // ---------------- 接口地址（.php 兼容别名在前，无后缀规范地址在后） ----------------

    // 注意：group 闭包不能用 static（Slim CallableResolver 需要 bindTo 容器，
    // PHP 8.5 下 static 闭包 bindTo 返回 null 导致路由注册失败）
    $app->group('/api', function (RouteCollectorProxy $group): void {
        // php 演示模块（旧后端不校验请求方法，此处沿用 any 保持一致）
        $group->any('/php-demo.php', [PhpDemoController::class, 'handle']);
        $group->any('/php-demo', [PhpDemoController::class, 'handle']);

        // doctrine 演示模块
        $group->any('/doctrine.php', [DoctrineController::class, 'handle']);
        $group->any('/doctrine', [DoctrineController::class, 'handle']);

        // clouddrive 网盘模块：Session 中间件 + 鉴权中间件（先 Session 后 Auth）
        $group->any('/clouddrive.php', [CloudDriveController::class, 'handle'])
            ->add(CloudDriveAuthMiddleware::class)
            ->add(SessionMiddleware::class);
        $group->any('/clouddrive', [CloudDriveController::class, 'handle'])
            ->add(CloudDriveAuthMiddleware::class)
            ->add(SessionMiddleware::class);
    });

    // ---------------- 文档 ----------------

    $app->get('/swagger', [SwaggerController::class, 'index']);
    $app->get('/swagger/json', [SwaggerController::class, 'json']);

    // ---------------- 兜底：旧直连入口 + 未匹配路由 ----------------
    // /?module=php|doctrine|clouddrive 与旧行为逐分支一致；其余路径返回 404 未知模块
    $app->any('/{route:.*}', [LegacyModuleController::class, 'handle']);

    // ---------------- 全局 CORS（必须最后 add，位于中间件栈最外层） ----------------
    // OPTIONS 一律 204 短路 + 所有响应补 Access-Control-* 头（值与迁移前一致）
    $app->add(CorsMiddleware::class);

    // 访问日志：比 CORS 更外一层，每个请求（含 OPTIONS 短路与错误信封）记录一行
    $app->add(RequestLoggerMiddleware::class);
};