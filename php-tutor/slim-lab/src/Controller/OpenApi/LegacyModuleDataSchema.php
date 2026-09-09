<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：旧版模块数据。
 */
#[OA\Schema(
    schema: 'LegacyModuleData',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'success'),
    ],
)]
final class LegacyModuleDataSchema
{
}
