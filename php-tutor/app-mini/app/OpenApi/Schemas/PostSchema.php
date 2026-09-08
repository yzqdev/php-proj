<?php
declare(strict_types=1);

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

/** 文章对象。 */
#[OA\Schema(
    schema: 'Post',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: '我的第一篇文章'),
        new OA\Property(property: 'slug', type: 'string', example: 'wo-de-di-yi-pian-wen-zhang'),
        new OA\Property(property: 'content', type: 'string', example: '正文内容...'),
        new OA\Property(property: 'status', type: 'string', enum: ['draft', 'published'], example: 'published'),
        new OA\Property(property: 'author_id', type: 'integer', example: 1),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        new OA\Property(
            property: 'author',
            ref: '#/components/schemas/UserPublic',
        ),
    ],
)]
final class PostSchema
{
}
