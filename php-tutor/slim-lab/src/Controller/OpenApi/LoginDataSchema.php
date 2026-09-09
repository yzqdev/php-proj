<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：login 返回的登录数据。
 */
#[OA\Schema(
    schema: 'LoginData',
    properties: [
        new OA\Property(property: 'userId', type: 'string', example: 'json:1'),
        new OA\Property(property: 'username', type: 'string', example: 'demo_user'),
    ],
)]
final class LoginDataSchema
{
}
