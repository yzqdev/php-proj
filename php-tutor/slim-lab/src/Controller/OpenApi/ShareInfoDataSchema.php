<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：网盘分享信息数据。
 */
#[OA\Schema(
    schema: 'ShareInfoData',
    properties: [
        new OA\Property(property: 'token', type: 'string', example: '9f86d081884c7d659a2feaa0c55ad015'),
        new OA\Property(property: 'name', type: 'string', example: 'speedtest-master.zip'),
        new OA\Property(property: 'need_pwd', type: 'boolean', example: false),
        new OA\Property(property: 'expired', type: 'boolean', example: false),
    ],
)]
final class ShareInfoDataSchema
{
}
