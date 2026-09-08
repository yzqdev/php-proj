<?php

declare(strict_types=1);

namespace App\Schema;

use OpenApi\Attributes as OA;

/**
 * 单条日志（GET /api/logs 的 entries 元素）
 */
#[OA\Schema(
    schema: 'LogEntry',
    required: ['datetime', 'level', 'channel', 'message'],
    properties: [
        new OA\Property(property: 'datetime', type: 'string', description: 'ISO-8601 时间戳，带时区', example: '2026-09-09T01:58:07.333737+08:00'),
        new OA\Property(property: 'level', type: 'string', description: '日志级别', example: 'INFO'),
        new OA\Property(property: 'channel', type: 'string', description: '日志通道', example: 'app'),
        new OA\Property(property: 'message', type: 'string', description: '日志内容', example: '获取用户列表 [] []'),
    ],
)]
final class LogEntrySchema
{
}
