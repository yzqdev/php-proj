<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：doctrine stats 数据。
 */
#[OA\Schema(
    schema: 'DoctrineStatsData',
    properties: [
        new OA\Property(property: 'users', type: 'integer', example: 10),
        new OA\Property(property: 'products', type: 'integer', example: 50),
    ],
)]
final class DoctrineStatsDataSchema
{
}
