<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：doctrine 数据。
 */
#[OA\Schema(
    schema: 'DoctrineData',
    oneOf: [
        new OA\Schema(ref: '#/components/schemas/MessageData'),
        new OA\Schema(ref: '#/components/schemas/DoctrineStatsData'),
    ],
)]
final class DoctrineDataSchema
{
}
