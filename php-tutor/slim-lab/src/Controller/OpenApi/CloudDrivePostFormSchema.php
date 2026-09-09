<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：网盘通用表单结构。
 */
#[OA\Schema(
    schema: 'CloudDrivePostForm',
    properties: [
        new OA\Property(property: 'action', type: 'string', example: 'login'),
        new OA\Property(property: 'pwd', type: 'string', example: '123456'),
        new OA\Property(property: 'kw', type: 'string', example: 'test'),
        new OA\Property(property: 'dir', type: 'string', example: 'docs'),
        new OA\Property(property: 'token', type: 'string', example: '9f86d081884c7d659a2feaa0c55ad015'),
        new OA\Property(property: 'share_pwd', type: 'string', example: 'gm0mmf'),
        new OA\Property(property: 'share_file', type: 'string', example: 'speedtest-master.zip'),
        new OA\Property(property: 'is_dir_share', type: 'string', example: '0'),
        new OA\Property(property: 'share_expire', type: 'integer', example: 7),
    ],
)]
final class CloudDrivePostFormSchema
{
}
