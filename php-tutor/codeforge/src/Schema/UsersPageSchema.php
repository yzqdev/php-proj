<?php

declare(strict_types=1);

namespace App\Schema;

use OpenApi\Attributes as OA;

/**
 * 用户分页结果（GET /api/users 的 data 字段）
 */
#[OA\Schema(
    schema: 'UsersPage',
    required: ['data', 'total', 'page', 'pageSize', 'lastPage'],
    properties: [
        new OA\Property(property: 'data', type: 'array', description: '当前页用户列表', items: new OA\Items(ref: '#/components/schemas/User')),
        new OA\Property(property: 'total', type: 'integer', description: '总条数', example: 4),
        new OA\Property(property: 'page', type: 'integer', description: '当前页码', example: 1),
        new OA\Property(property: 'pageSize', type: 'integer', description: '每页条数', example: 20),
        new OA\Property(property: 'lastPage', type: 'integer', description: '最后一页页码', example: 1),
    ],
)]
final class UsersPageSchema
{
}
