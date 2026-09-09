<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：可查日志日期条目�?
 */
#[OA\Schema(
    schema: 'LogDay',
    type: 'object',
    required: ['date', 'bytes'],
    properties: [
        new OA\Property(property: 'date', type: 'string', format: 'date', description: '日志日期', example: '2026-09-08'),
        new OA\Property(property: 'bytes', type: 'integer', format: 'int64', description: '当日日志文件大小（字节）', example: 125840),
    ],
)]
final class LogDaySchema
{
}
