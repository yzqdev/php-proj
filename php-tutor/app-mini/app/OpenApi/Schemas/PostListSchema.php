<?php
declare(strict_types=1);

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

/** 文章分页列表。 */
#[OA\Schema(
    schema: 'PostList',
    properties: [
        new OA\Property(
            property: 'success',
            type: 'boolean',
            example: true,
        ),
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Post'),
        ),
        new OA\Property(
            property: 'meta',
            properties: [
                new OA\Property(property: 'total', type: 'integer', example: 25),
                new OA\Property(property: 'page', type: 'integer', example: 1),
                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                new OA\Property(property: 'last_page', type: 'integer', example: 2),
            ],
            type: 'object',
        ),
    ],
)]
final class PostListSchema
{
}
