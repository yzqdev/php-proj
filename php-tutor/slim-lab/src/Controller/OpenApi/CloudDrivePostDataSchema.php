<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：网盘创建/更新数据。
 */
#[OA\Schema(
    schema: 'CloudDrivePostData',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: '上传成功'),
    ],
)]
final class CloudDrivePostDataSchema
{
}
