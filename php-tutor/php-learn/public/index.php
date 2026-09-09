<?php

/**
 * ============================================================
 * 唯一的 Web 入口（Front Controller 模式）— Slim 4 + Vue SPA
 * ============================================================
 *
 * 请求分流逻辑：
 *   1. /api/v1/*  → Slim 走 API Controller（JSON 响应）
 *   2. /swagger/* → Slim 走 SwaggerController
 *   3. 其他路径    → 返回 Vue SPA 的 app.html（支持前端路由）
 *
 * PHP 内置服务器 `php -S localhost:8080 -t public` 的规则：
 *   - public 下真实存在的文件（如 /assets/*.js）→ 直接返回（走不到 index.php）
 *   - 不存在的路径（如 /articles/123）→ 交给 index.php → 返回 app.html
 *
 * 对比 Java / Spring Boot：
 *   - 类似 DispatcherServlet + 前端 Controller 的组合
 *   - API 走后端路由，其他走前端 SPA
 */
declare(strict_types=1);

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH);

// API 和 Swagger 请求 → 走 Slim 后端
if (str_starts_with($path, '/api/') || str_starts_with($path, '/swagger')) {
    $app = require dirname(__DIR__) . '/app/bootstrap.php';
    $app->run();
    exit;
}

// 其他请求 → 返回 Vue SPA（支持 History 路由模式）
// PHP 内置服务器对真实存在的静态文件已经直接返回了，
// 走到这里的都是不存在的路径（如 /articles/123），需要返回 index.html
$htmlFile = __DIR__ . '/app.html';
if (file_exists($htmlFile)) {
    header('Content-Type: text/html; charset=UTF-8');
    readfile($htmlFile);
    exit;
}

// fallback：没有 app.html 时走 Slim（兼容旧模式）
$app = require dirname(__DIR__) . '/app/bootstrap.php';
$app->run();