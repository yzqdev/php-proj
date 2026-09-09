<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：单个日志日期信息。
 */
#[OA\Schema(
    schema: 'LogDay',
    properties: [
        new OA\Property(property: 'date', type: 'string', example: '2026-09-08'),
        new OA\Property(property: 'bytes', type: 'integer', example: 12345),
    ],
)]
final class LogDaySchema
{
}
