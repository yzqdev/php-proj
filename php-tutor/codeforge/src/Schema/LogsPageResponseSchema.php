<?php

declare(strict_types=1);

namespace App\Schema;

use OpenApi\Attributes as OA;

/**
 * 日志查询的成功响应（GET /api/logs）
 */
#[OA\Schema(
    schema: 'LogsPageResponse',
    required: ['code', 'message', 'data'],
    properties: [
        new OA\Property(property: 'code', type: 'integer', description: '0 表示成功，非 0 为业务错误码', example: 0),
        new OA\Property(property: 'message', type: 'string', example: '获取成功'),
        new OA\Property(property: 'data', ref: '#/components/schemas/LogsPage'),
    ],
)]
final class LogsPageResponseSchema
{
}
