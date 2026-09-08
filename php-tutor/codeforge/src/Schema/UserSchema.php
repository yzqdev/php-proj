<?php

declare(strict_types=1);

namespace App\Schema;

use OpenApi\Attributes as OA;

/**
 * 用户（对应 User::toArray() 的 JSON 形状）
 */
#[OA\Schema(
    schema: 'User',
    required: ['id', 'name', 'email', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', format: 'int64', description: '用户 ID', example: 1),
        new OA\Property(property: 'name', type: 'string', maxLength: 100, description: '用户姓名', example: 'Alice'),
        new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 150, description: '用户邮箱（唯一）', example: 'alice@example.com'),
        new OA\Property(property: 'created_at', type: 'string', description: '创建时间，格式 Y-m-d H:i:s', example: '2026-09-08 14:50:23'),
        new OA\Property(property: 'updated_at', type: 'string', description: '更新时间，格式 Y-m-d H:i:s', example: '2026-09-08 14:50:23'),
    ],
)]
final class UserSchema
{
}
