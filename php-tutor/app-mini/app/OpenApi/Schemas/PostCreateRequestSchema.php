<?php
declare(strict_types=1);

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

/** 创建文章请求体。 */
#[OA\Schema(
    schema: 'PostCreateRequest',
    required: ['title', 'content'],
    properties: [
        new OA\Property(property: 'title', type: 'string', maxLength: 190, example: '我的第一篇文章'),
        new OA\Property(property: 'content', type: 'string', example: '正文内容...'),
        new OA\Property(property: 'status', type: 'string', enum: ['draft', 'published'], example: 'published'),
    ],
)]
final class PostCreateRequestSchema
{
}
