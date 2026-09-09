<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：单行日志（解析 storage/logs/app-YYYY-MM-DD.log 后的一条记录）。
 * 原文行无法按格式解析时降级为仅 message/raw 有值，其余字段为 null。
 */
#[OA\Schema(
    schema: 'LogLine',
    type: 'object',
    required: ['no', 'message', 'raw'],
    properties: [
        new OA\Property(property: 'no', type: 'integer', description: '当日文件内的行号（从 0 开始）', example: 1234),
        new OA\Property(property: 'timestamp', type: 'string', nullable: true, format: 'date-time', description: '记录时间 Y-m-d H:i:s', example: '2026-09-08 08:31:02'),
        new OA\Property(property: 'channel', type: 'string', nullable: true, description: '日志通道', example: 'app'),
        new OA\Property(
            property: 'level',
            type: 'string',
            nullable: true,
            description: '日志级别',
            enum: ['DEBUG', 'INFO', 'NOTICE', 'WARNING', 'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'],
            example: 'INFO',
        ),
        new OA\Property(property: 'message', type: 'string', description: '日志正文（不含尾部 context JSON）', example: 'GET /api/logs 200'),
        new OA\Property(
            property: 'context',
            type: 'object',
            nullable: true,
            description: 'Monolog context（请求 ID、耗时、IP、异常等），无上下文时为 null',
            additionalProperties: true,
            example: null,
        ),
        new OA\Property(property: 'raw', type: 'string', description: '原始日志行，便于排查解析失败的记录', example: '[2026-09-08 08:31:02] app.INFO: GET /api/logs 200 {"rid":"1a2b3c4d"}'),
    ],
)]
final class LogLineSchema
{
}
