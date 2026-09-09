<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：clouddrive login/logout/check �?data 结构�?
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
