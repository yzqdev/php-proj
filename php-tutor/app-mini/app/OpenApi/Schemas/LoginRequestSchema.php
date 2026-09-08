<?php
declare(strict_types=1);

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

/** 登录请求体。 */
#[OA\Schema(
    schema: 'LoginRequest',
    required: ['email', 'password'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'zhangsan@example.com'),
        new OA\Property(property: 'password', type: 'string', format: 'password'),
    ],
)]
final class LoginRequestSchema
{
}
