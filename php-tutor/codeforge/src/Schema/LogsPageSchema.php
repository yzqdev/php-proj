<?php

declare(strict_types=1);

namespace App\Schema;

use OpenApi\Attributes as OA;

/**
 * 日志查询结果（GET /api/logs 的 data 字段）
 */
#[OA\Schema(
    schema: 'LogsPage',
    required: ['total', 'entries'],
    properties: [
        new OA\Property(property: 'total', type: 'integer', description: '命中条数', example: 1),
        new OA\Property(property: 'entries', type: 'array', description: '日志条目', items: new OA\Items(ref: '#/components/schemas/LogEntry')),
    ],
)]
final class LogsPageSchema
{
}
