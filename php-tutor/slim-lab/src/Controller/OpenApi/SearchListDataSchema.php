<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：global_search �?data 结构�?
 */
#[OA\Schema(
    schema: 'SearchListData',
    type: 'object',
    required: ['list'],
    properties: [
        new OA\Property(property: 'list', type: 'array', items: new OA\Items(ref: '#/components/schemas/SearchEntry')),
    ],
)]
final class SearchListDataSchema
{
}
