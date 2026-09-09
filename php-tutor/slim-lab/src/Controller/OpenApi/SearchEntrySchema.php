<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：网盘搜索条目。
 */
#[OA\Schema(
    schema: 'SearchEntry',
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'speedtest-master.zip'),
        new OA\Property(property: 'path', type: 'string', example: 'docs/speedtest-master.zip'),
        new OA\Property(property: 'size', type: 'integer', example: 1234567),
    ],
)]
final class SearchEntrySchema
{
}
