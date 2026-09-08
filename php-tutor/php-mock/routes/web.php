<?php

declare(strict_types=1);

use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

/*
 |--------------------------------------------------------------------------
 | Web 路由：仅提供 Swagger UI 文档页（非管理后台/前端页面）
 |--------------------------------------------------------------------------
 */
Route::get('/', fn () => redirect('/docs'));

// 欢迎页面（演示 Blade 模板用法）
Route::get('/welcome', [WelcomeController::class, 'index']);

Route::get('/docs', function () {
    // Swagger UI 通过 CDN 加载，数据源为 /api/docs 返回的 OpenAPI JSON
    return response(<<<'HTML'
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <title>Mock API 文档</title>
  <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
</head>
<body>
  <div id="swagger-ui"></div>
  <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
  <script>
    window.onload = () => SwaggerUIBundle({ url: '/api/docs', dom_id: '#swagger-ui' });
  </script>
</body>
</html>
HTML)->header('Content-Type', 'text/html; charset=UTF-8');
});
