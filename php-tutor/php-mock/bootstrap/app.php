<?php

use App\Http\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 全局追加响应头中间件：所有响应带 X-Mock-Data: true
        $middleware->append(App\Http\Middleware\AddMockDataHeader::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // API 路由一律以 JSON 渲染异常
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // 统一错误格式：{"code": <http状态码>, "message": "..."}
        $exceptions->render(function (ValidationException $e) {
            // 取第一条校验错误，格式如 "fields.0.type：selected type is invalid"
            $first = collect($e->errors())->first();
            $message = is_array($first) ? (string) $first[0] : '校验失败';

            return ApiResponse::error(422, $message);
        });

        $exceptions->render(function (ModelNotFoundException|NotFoundHttpException $e) {
            return ApiResponse::error(404, '资源不存在');
        });

        $exceptions->render(function (AuthenticationException $e) {
            return ApiResponse::error(401, '未认证，请先登录');
        });

        $exceptions->render(function (HttpException $e) {
            return ApiResponse::error($e->getStatusCode(), $e->getMessage() ?: '请求失败');
        });
    })->create();
