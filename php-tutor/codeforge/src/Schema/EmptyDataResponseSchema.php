<?php

declare(strict_types=1);

namespace App\Schema;

use OpenApi\Attributes as OA;

/**
 * 无返回数据的成功响应（删除类操作）
 */
#[OA\Schema(
    schema: 'EmptyDataResponse',
    required: ['code', 'message', 'data'],
    properties: [
        new OA\Property(property: 'code', type: 'integer', description: '0 表示成功，非 0 为业务错误码', example: 0),
        new OA\Property(property: 'message', type: 'string', example: ' '),
        new OA\Property(property: 'data', nullable: true, description: '删除操作不返回数据'),
    ],
)]
final class EmptyDataResponseSchema
{
}
