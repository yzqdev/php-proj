<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：网盘分享列表数据。
 */
#[OA\Schema(
    schema: 'ShareListData',
    properties: [
        new OA\Property(property: 'shares', type: 'array', items: new OA\Items(ref: '#/components/schemas/ShareEntry')),
    ],
)]
final class ShareListDataSchema
{
}
