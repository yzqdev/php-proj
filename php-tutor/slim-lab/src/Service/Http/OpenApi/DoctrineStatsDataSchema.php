<?php

declare(strict_types=1);

namespace Service\Http\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：doctrine stats 接口的 data 结构。
 */
#[OA\Schema(
    schema: 'DoctrineStatsData',
    type: 'object',
    required: ['userCount', 'productCount'],
    properties: [
        new OA\Property(property: 'userCount', type: 'integer', example: 2),
        new OA\Property(property: 'productCount', type: 'integer', example: 3),
    ],
)]
final class DoctrineStatsDataSchema
{
}
