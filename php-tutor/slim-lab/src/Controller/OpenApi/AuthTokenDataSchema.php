<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：auth login 返回的令牌数据。
 */
#[OA\Schema(
    schema: 'AuthTokenData',
    properties: [
        new OA\Property(property: 'token', type: 'string', example: '9f86d081884c7d659a2feaa0c55ad0159f86d081884c7d659a2feaa0c55ad015'),
        new OA\Property(property: 'user', ref: '#/components/schemas/AuthUser'),
    ],
)]
final class AuthTokenDataSchema
{
}
