<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：日志行数据。
 */
#[OA\Schema(
    schema: 'LogLine',
    properties: [
        new OA\Property(property: 'no', type: 'integer', example: 1234),
        new OA\Property(property: 'timestamp', type: 'string', nullable: true, example: '2026-09-08 08:31:02'),
        new OA\Property(property: 'channel', type: 'string', nullable: true, example: 'app'),
        new OA\Property(property: 'level', type: 'string', nullable: true, example: 'INFO'),
        new OA\Property(property: 'message', type: 'string', example: 'GET /api/logs 200'),
        new OA\Property(property: 'context', type: 'object', nullable: true, example: null),
        new OA\Property(property: 'raw', type: 'string', example: '[2026-09-08 08:31:02] app.INFO: GET /api/logs 200 {"rid":"1a2b3c4d"}'),
    ],
)]
final class LogLineSchema
{
}
