<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：网盘搜索结果列表。
 */
#[OA\Schema(
    schema: 'SearchListData',
    properties: [
        new OA\Property(property: 'items', type: 'array', items: new OA\Items(ref: '#/components/schemas/SearchEntry')),
    ],
)]
final class SearchListDataSchema
{
}
