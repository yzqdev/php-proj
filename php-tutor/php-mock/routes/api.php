<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Mock\MockApiController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ResourceController;
use App\Http\Controllers\DocsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 认证接口（公开）
|--------------------------------------------------------------------------
*/
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| 管理接口（auth:sanctum，Bearer token）
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);

    // 在指定项目下定义资源（定义成功后立即按 count 生成数据）
    Route::post('/projects/{project}/resources', [ResourceController::class, 'store']);

    Route::get('/resources/{resource}', [ResourceController::class, 'show']);
    // 重新生成：先清空旧数据，再按 count 生成
    Route::post('/resources/{resource}/generate', [ResourceController::class, 'generate']);
    // 清空资源数据（保留 Schema）
    Route::delete('/resources/{resource}/records', [ResourceController::class, 'clearRecords']);
});

/*
|--------------------------------------------------------------------------
| 公开 Mock 接口（无需认证，限流 60 次/分钟）
|--------------------------------------------------------------------------
*/
Route::prefix('v1/mock')->middleware('throttle:60,1')->group(function (): void {
    Route::get('{projectSlug}/{resourceName}', [MockApiController::class, 'index']);
    Route::get('{projectSlug}/{resourceName}/{id}', [MockApiController::class, 'show']);
    Route::post('{projectSlug}/{resourceName}', [MockApiController::class, 'store']);
    Route::match(['put', 'patch'], '{projectSlug}/{resourceName}/{id}', [MockApiController::class, 'update']);
    Route::delete('{projectSlug}/{resourceName}/{id}', [MockApiController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| API 文档（OpenAPI JSON + Swagger UI）
|--------------------------------------------------------------------------
*/
Route::get('/docs', [DocsController::class, 'spec']);
