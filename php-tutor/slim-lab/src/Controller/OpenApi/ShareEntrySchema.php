<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：网盘分享条目。
 */
#[OA\Schema(
    schema: 'ShareEntry',
    properties: [
        new OA\Property(property: 'token', type: 'string', example: '9f86d081884c7d659a2feaa0c55ad015'),
        new OA\Property(property: 'name', type: 'string', example: 'speedtest-master.zip'),
        new OA\Property(property: 'expire', type: 'string', example: '2026-09-15'),
    ],
)]
final class ShareEntrySchema
{
}
