<?php
declare(strict_types=1);

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

/** 注册请求体。 */
#[OA\Schema(
    schema: 'RegisterRequest',
    required: ['name', 'email', 'password'],
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 100, example: '张三'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'zhangsan@example.com'),
        new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, maxLength: 72),
    ],
)]
final class RegisterRequestSchema
{
}
