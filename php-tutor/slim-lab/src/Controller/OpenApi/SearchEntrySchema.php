<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：global_search 结果条目�?
 */
#[OA\Schema(
    schema: 'SearchEntry',
    type: 'object',
    required: ['full_rel', 'name', 'parent_dir', 'is_dir', 'size', 'mtime', 'ext'],
    properties: [
        new OA\Property(property: 'full_rel', type: 'string', example: 'docs/report.pdf'),
        new OA\Property(property: 'name', type: 'string', example: 'report.pdf'),
        new OA\Property(property: 'parent_dir', type: 'string', example: 'docs'),
        new OA\Property(property: 'is_dir', type: 'boolean', example: false),
        new OA\Property(property: 'size', type: 'integer', example: 1024),
        new OA\Property(property: 'mtime', type: 'string', example: '2026-09-06 10:00'),
        new OA\Property(property: 'ext', type: 'string', example: 'pdf'),
    ],
)]
final class SearchEntrySchema
{
}
