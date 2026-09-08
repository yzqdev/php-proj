<?php

declare(strict_types=1);

namespace App\Schema;

use OpenApi\Attributes as OA;

/**
 * 文件记录（对应 File::toArray() 的 JSON 形状）
 */
#[OA\Schema(
    schema: 'File',
    required: ['id', 'original_name', 'extension', 'mime', 'size', 'created_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', format: 'int64', description: '文件记录 ID', example: 1),
        new OA\Property(property: 'original_name', type: 'string', maxLength: 255, description: '上传时的原始文件名', example: 'report.pdf'),
        new OA\Property(property: 'extension', type: 'string', description: '扩展名，小写不含点', example: 'pdf'),
        new OA\Property(property: 'mime', type: 'string', description: 'MIME 类型', example: 'application/pdf'),
        new OA\Property(property: 'size', type: 'integer', format: 'int64', description: '文件大小，字节', example: 204800),
        new OA\Property(property: 'created_at', type: 'string', description: '上传时间，格式 Y-m-d H:i:s', example: '2026-09-08 14:50:23'),
    ],
)]
final class FileSchema
{
}
