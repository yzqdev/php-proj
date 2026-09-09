<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：create_share �?data 结构�?
 */
#[OA\Schema(
    schema: 'ShareCreateData',
    type: 'object',
    required: ['url', 'pwd', 'token'],
    properties: [
        new OA\Property(property: 'url', type: 'string', example: 'http://localhost:5200/?share=9f86d081884c7d659a2feaa0c55ad015'),
        new OA\Property(property: 'pwd', type: 'string', example: 'abc123'),
        new OA\Property(property: 'token', type: 'string', example: '9f86d081884c7d659a2feaa0c55ad015'),
    ],
)]
final class ShareCreateDataSchema
{
}
