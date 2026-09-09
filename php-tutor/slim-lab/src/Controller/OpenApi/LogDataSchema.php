<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：GET /api/logs 的 data 结构（某一天的日志窗口）。
 */
#[OA\Schema(
    schema: 'LogData',
    type: 'object',
    required: ['date', 'total', 'offset', 'limit', 'truncated', 'lines'],
    properties: [
        new OA\Property(property: 'date', type: 'string', format: 'date', description: '查询的日期', example: '2026-09-08'),
        new OA\Property(property: 'total', type: 'integer', description: '当日日志总行数', example: 1234),
        new OA\Property(property: 'offset', type: 'integer', description: '本页起始行号', example: 0),
        new OA\Property(property: 'limit', type: 'integer', description: '本页最大行数（服务端上限 2000）', example: 500),
        new OA\Property(property: 'truncated', type: 'boolean', description: '是否还有未返回的行', example: false),
        new OA\Property(property: 'lines', type: 'array', items: new OA\Items(ref: '#/components/schemas/LogLine')),
    ],
)]
final class LogDataSchema
{
}
