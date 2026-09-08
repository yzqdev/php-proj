<?php

declare(strict_types=1);

namespace Service\Http\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：分享条目（get_share_list 用）。
 */
#[OA\Schema(
    schema: 'ShareEntry',
    type: 'object',
    required: ['token', 'url', 'name', 'parent_path', 'is_dir', 'pwd', 'expire', 'create_time', 'is_expire', 'expire_text'],
    properties: [
        new OA\Property(property: 'token', type: 'string', example: '9f86d081884c7d659a2feaa0c55ad015'),
        new OA\Property(property: 'url', type: 'string', example: 'http://localhost:5200/?share=9f86d081884c7d659a2feaa0c55ad015'),
        new OA\Property(property: 'name', type: 'string', example: 'report.pdf'),
        new OA\Property(property: 'parent_path', type: 'string', example: 'docs'),
        new OA\Property(property: 'is_dir', type: 'boolean', example: false),
        new OA\Property(property: 'pwd', type: 'string', example: 'abc123'),
        new OA\Property(property: 'expire', type: 'integer', description: '过期时间戳，0=永久', example: 0),
        new OA\Property(property: 'create_time', type: 'integer', example: 1757110000),
        new OA\Property(property: 'is_expire', type: 'boolean', example: false),
        new OA\Property(property: 'expire_text', type: 'string', example: '永久有效'),
    ],
)]
final class ShareEntrySchema
{
}
