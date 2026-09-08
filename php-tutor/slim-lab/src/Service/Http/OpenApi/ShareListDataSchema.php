<?php

declare(strict_types=1);

namespace Service\Http\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：get_share_list 的 data 结构。
 */
#[OA\Schema(
    schema: 'ShareListData',
    type: 'object',
    required: ['list'],
    properties: [
        new OA\Property(property: 'list', type: 'array', items: new OA\Items(ref: '#/components/schemas/ShareEntry')),
    ],
)]
final class ShareListDataSchema
{
}
