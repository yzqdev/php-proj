<?php

declare(strict_types=1);

namespace App\Schema;

use OpenApi\Attributes as OA;

/**
 * 创建用户请求体（POST /api/users）
 */
#[OA\Schema(
    schema: 'UserCreateRequest',
    required: ['name', 'email'],
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 100, description: '用户姓名，不能为空', example: 'Alice'),
        new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 150, description: '用户邮箱，不能为空且需全局唯一', example: 'alice@example.com'),
    ],
)]
final class UserCreateRequest
{
}
