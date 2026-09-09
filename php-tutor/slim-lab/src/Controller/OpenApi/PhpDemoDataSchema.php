<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：php-demo 数据。
 */
#[OA\Schema(
    schema: 'PhpDemoData',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: '{"key":"value"}'),
    ],
)]
final class PhpDemoDataSchema
{
}
