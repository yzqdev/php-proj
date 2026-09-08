<?php

declare(strict_types=1);

namespace App\Schema;

use OpenApi\Attributes as OA;

/**
 * 更新用户请求体（PUT /api/users/{id}）
 *
 * 两个字段均可选，但至少要提供一个非空字段。
 */
#[OA\Schema(
    schema: 'UserUpdateRequest',
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 100, description: '用户姓名', example: 'Alice2'),
        new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 150, description: '用户邮箱，需全局唯一', example: 'alice2@example.com'),
    ],
)]
final class UserUpdateRequest
{
}
