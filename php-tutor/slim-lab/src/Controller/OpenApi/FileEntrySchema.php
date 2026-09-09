<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：网盘文件条目。
 */
#[OA\Schema(
    schema: 'FileEntry',
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'speedtest-master.zip'),
        new OA\Property(property: 'size', type: 'integer', example: 1234567),
        new OA\Property(property: 'mtime', type: 'string', example: '2026-09-08 10:00:00'),
        new OA\Property(property: 'isDir', type: 'boolean', example: false),
    ],
)]
final class FileEntrySchema
{
}
