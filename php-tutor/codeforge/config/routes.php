<?php

declare(strict_types=1);

use App\Controller\UserController;
use App\Controller\LogController;
use App\Controller\FileController;
use Slim\App;

/** @var App $app */

/** @var UserController $controller */
$controller = $app->getContainer()->get(UserController::class);

/** @var LogController $logController */
$logController = $app->getContainer()->get(LogController::class);

/** @var FileController $fileController */
$fileController = $app->getContainer()->get(FileController::class);

// ─── CORS OPTIONS 预检路由（必须在其他路由之前注册）────────────
// 用 {path:.*} 让通配符跨斜杠，/api/users/1 这类子路径的预检才能命中
$app->options('/api/{path:.*}', function ($request, $response) {
    return $response;
});
$app->options('/pelican', function ($request, $response) {
    return $response;
});

// ─── 用户 API ──────────────────────────────────────────────
$app->get('/api/users', [$controller, 'index']);
$app->get('/api/users/{id}', [$controller, 'show']);
$app->post('/api/users', [$controller, 'create']);
$app->put('/api/users/{id}', [$controller, 'update']);
$app->delete('/api/users/{id}', [$controller, 'delete']);

// ─── 文件 API ──────────────────────────────────────────────
$app->get('/api/files', [$fileController, 'index']);
$app->get('/api/files/{id}', [$fileController, 'show']);
$app->post('/api/files', [$fileController, 'upload']);
$app->delete('/api/files/{id}', [$fileController, 'delete']);
$app->get('/api/files/{id}/download', [$fileController, 'download']);

// ─── 日志 API ──────────────────────────────────────────────
$app->get('/api/logs', [$logController, 'index']);

// ─── 特殊路由 ──────────────────────────────────────────────
// 鹈鹕骑单车动态 SVG
$app->get('/pelican', [$controller, 'pelican']);