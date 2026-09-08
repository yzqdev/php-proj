<?php
declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 顶层信息与 Bearer 安全方案。
 * 扫描 app/ 目录时,本类上的注解会被收集到根 OpenApi 对象。
 */
#[OA\Info(
    version: '1.0.0',
    title: '多用户博客 REST API',
    description: 'Slim 4 + PHP-DI + Eloquent + JWT 实现的多用户博客纯 REST API。',
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: '使用 Bearer Token(JWT HS256)。在 Authorization 头中传入 `Bearer <token>`。',
)]
final class OpenApiDefinition
{
}
