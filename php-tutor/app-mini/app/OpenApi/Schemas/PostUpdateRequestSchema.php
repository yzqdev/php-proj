<?php
declare(strict_types=1);

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

/** 更新文章请求体(部分更新,至少一个字段)。 */
#[OA\Schema(
    schema: 'PostUpdateRequest',
    properties: [
        new OA\Property(property: 'title', type: 'string', maxLength: 190, example: '修改后的标题'),
        new OA\Property(property: 'content', type: 'string'),
        new OA\Property(property: 'status', type: 'string', enum: ['draft', 'published']),
    ],
)]
final class PostUpdateRequestSchema
{
}
