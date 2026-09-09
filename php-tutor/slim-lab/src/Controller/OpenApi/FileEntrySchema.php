<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：文件条目（list_dir 用）�?
 */
#[OA\Schema(
    schema: 'FileEntry',
    type: 'object',
    required: ['name', 'is_dir', 'size', 'size_text', 'mtime', 'ext'],
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'speedtest-master.zip'),
        new OA\Property(property: 'is_dir', type: 'boolean', example: false),
        new OA\Property(property: 'size', type: 'integer', example: 2150945),
        new OA\Property(property: 'size_text', type: 'string', example: '2.05 MB'),
        new OA\Property(property: 'mtime', type: 'string', example: '2026-09-06 10:00'),
        new OA\Property(property: 'ext', type: 'string', example: 'zip'),
    ],
)]
final class FileEntrySchema
{
}
