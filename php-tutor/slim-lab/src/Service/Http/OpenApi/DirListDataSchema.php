<?php

declare(strict_types=1);

namespace Service\Http\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：list_dir 的 data 结构。
 */
#[OA\Schema(
    schema: 'DirListData',
    type: 'object',
    required: ['list', 'dirs', 'current_folder'],
    properties: [
        new OA\Property(property: 'list', type: 'array', items: new OA\Items(ref: '#/components/schemas/FileEntry')),
        new OA\Property(property: 'dirs', type: 'array', items: new OA\Items(type: 'string'), description: '全部子目录相对路径', example: ['photos', 'docs/work']),
        new OA\Property(property: 'current_folder', type: 'string', example: 'docs'),
    ],
)]
final class DirListDataSchema
{
}
