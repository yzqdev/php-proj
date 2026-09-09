<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：日志日期列表数据。
 */
#[OA\Schema(
    schema: 'LogDaysData',
    properties: [
        new OA\Property(property: 'days', type: 'array', items: new OA\Items(ref: '#/components/schemas/LogDay')),
    ],
)]
final class LogDaysDataSchema
{
}
