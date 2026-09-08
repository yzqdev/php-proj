<?php

declare(strict_types=1);

namespace Service\Http\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：clouddrive login/logout/check 的 data 结构。
 */
#[OA\Schema(
    schema: 'LoginData',
    type: 'object',
    required: ['login'],
    properties: [
        new OA\Property(property: 'login', type: 'boolean', example: true),
    ],
)]
final class LoginDataSchema
{
}
