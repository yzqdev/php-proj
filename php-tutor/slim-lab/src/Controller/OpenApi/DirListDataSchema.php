<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：网盘目录列表数据。
 */
#[OA\Schema(
    schema: 'DirListData',
    properties: [
        new OA\Property(property: 'list', type: 'array', items: new OA\Items(ref: '#/components/schemas/FileEntry')),
        new OA\Property(property: 'dirs', type: 'array', items: new OA\Items(type: 'string'), example: ['photos', 'docs/work']),
        new OA\Property(property: 'current_folder', type: 'string', example: 'docs'),
    ],
)]
final class DirListDataSchema
{
}
