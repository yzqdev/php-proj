<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：统一响应信封（与迁移�?apiSuccess/apiError �?JSON 结构一致）�?
 */
#[OA\Schema(
    schema: 'ApiEnvelope',
    type: 'object',
    required: ['code', 'message', 'data'],
    properties: [
        new OA\Property(property: 'code', type: 'integer', description: '0=成功；其余等�?HTTP 状态码', example: 0),
        new OA\Property(property: 'message', type: 'string', example: 'success'),
        new OA\Property(property: 'data', nullable: true, description: '业务数据，业务错误时�?null', example: null),
    ],
)]
final class ApiEnvelopeSchema
{
}
