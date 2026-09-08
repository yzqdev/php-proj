<?php
declare(strict_types=1);

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

/** 用户公开资料 + 已发布文章。 */
#[OA\Schema(
    schema: 'UserProfile',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: '张三'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        new OA\Property(
            property: 'posts',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Post'),
        ),
    ],
)]
final class UserProfileSchema
{
}
