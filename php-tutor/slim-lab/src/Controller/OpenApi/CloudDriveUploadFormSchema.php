<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：网盘上传表单结构。
 */
#[OA\Schema(
    schema: 'CloudDriveUploadForm',
    properties: [
        new OA\Property(property: 'action', type: 'string', example: 'upload'),
        new OA\Property(property: 'current_folder', type: 'string', example: 'docs'),
        new OA\Property(property: 'relative_path', type: 'string', example: ''),
        new OA\Property(property: 'file', type: 'string', format: 'binary'),
    ],
)]
final class CloudDriveUploadFormSchema
{
}
