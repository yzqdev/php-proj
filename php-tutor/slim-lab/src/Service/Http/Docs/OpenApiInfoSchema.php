<?php

declare(strict_types=1);

namespace Service\Http\Docs;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 主信息：服务端地址与全局鉴权说明（会话 Cookie 方式，与迁移前一致）。
 * 该类仅承载文档元信息，不参与业务。
 */
#[OA\Info(
    version: '1.0.0',
    description: '原生 PHP 项目迁移至 Slim 4 后的接口文档。'
        . '统一响应信封：`{"code": 0, "message": "success", "data": ...}`，'
        . '`code` 非 0 时等于 HTTP 状态码（400 参数/业务错误、401 未登录、405 方法不允许、404 未知模块/未知操作、500 服务器错误）。'
        . '鉴权有两种：平台接口（/api/auth、/api/logs）用 Bearer Token；clouddrive 模块基于 PHP Session（Cookie: PHPSESSID）。',
    title: 'slim-lab API',
)]
#[OA\Server(url: '/', description: '当前部署（相对地址）')]
#[OA\SecurityScheme(
    securityScheme: 'SessionCookie',
    type: 'apiKey',
    description: 'clouddrive 受保护接口依赖登录会话；请先调用 POST /api/clouddrive?action=login。',
    name: 'PHPSESSID',
    in: 'cookie',
)]
#[OA\SecurityScheme(
    securityScheme: 'BearerAuth',
    type: 'http',
    description: '平台接口依赖 Bearer Token；请先调用 POST /api/auth/login，用返回的 token 作为 `Authorization: Bearer <token>`。令牌存于 Redis，退出登录后失效。',
    bearerFormat: 'opaque',
    scheme: 'bearer',
)]
final class OpenApiInfoSchema
{
}
