<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：网盘获取数据。
 */
#[OA\Schema(
    schema: 'CloudDriveGetData',
    oneOf: [
        new OA\Schema(ref: '#/components/schemas/LoginData'),
        new OA\Schema(ref: '#/components/schemas/ShareListData'),
        new OA\Schema(ref: '#/components/schemas/DirListData'),
    ],
)]
final class CloudDriveGetDataSchema
{
}
