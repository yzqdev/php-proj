<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：统一响应信封（与 apiSuccess/apiError 的 JSON 结构一致）。
 */
#[OA\Schema(
    schema: 'ApiEnvelope',
    properties: [
        new OA\Property(property: 'code', type: 'integer', example: 0),
        new OA\Property(property: 'message', type: 'string', example: 'success'),
        new OA\Property(property: 'data', nullable: true, example: null),
    ],
)]
final class ApiEnvelopeSchema
{
}
