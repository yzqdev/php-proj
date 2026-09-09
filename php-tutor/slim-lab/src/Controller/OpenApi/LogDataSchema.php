<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：日志查询数据。
 */
#[OA\Schema(
    schema: 'LogData',
    properties: [
        new OA\Property(property: 'date', type: 'string', example: '2026-09-08'),
        new OA\Property(property: 'total', type: 'integer', example: 1234),
        new OA\Property(property: 'offset', type: 'integer', example: 0),
        new OA\Property(property: 'limit', type: 'integer', example: 500),
        new OA\Property(property: 'truncated', type: 'boolean', example: false),
        new OA\Property(property: 'lines', type: 'array', items: new OA\Items(ref: '#/components/schemas/LogLine')),
    ],
)]
final class LogDataSchema
{
}
