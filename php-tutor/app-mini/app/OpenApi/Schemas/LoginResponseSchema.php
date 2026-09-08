<?php
declare(strict_types=1);

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

/** 登录成功响应数据。 */
#[OA\Schema(
    schema: 'LoginResponse',
    properties: [
        new OA\Property(property: 'token', type: 'string', example: 'eyJhbGciOiJIUzI1NiJ9.eyJ1aWQ'),
        new OA\Property(
            property: 'user',
            ref: '#/components/schemas/UserPublic',
        ),
    ],
)]
final class LoginResponseSchema
{
}
