<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：auth/userInfo 返回的用户包装数据。
 */
#[OA\Schema(
    schema: 'AuthUserWrap',
    properties: [
        new OA\Property(property: 'user', ref: '#/components/schemas/AuthUser'),
    ],
)]
final class AuthUserWrapSchema
{
}
