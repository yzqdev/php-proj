<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：认证用户信息。
 */
#[OA\Schema(
    schema: 'AuthUser',
    properties: [
        new OA\Property(property: 'id', type: 'string', example: 'json:1'),
        new OA\Property(property: 'username', type: 'string', example: 'demo_user'),
        new OA\Property(property: 'email', type: 'string', example: 'demo@test.com'),
    ],
)]
final class AuthUserSchema
{
}
