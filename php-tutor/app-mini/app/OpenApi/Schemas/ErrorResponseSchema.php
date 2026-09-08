<?php
declare(strict_types=1);

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

/** 统一错误响应。 */
#[OA\Schema(
    schema: 'ErrorResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(
            property: 'error',
            properties: [
                new OA\Property(property: 'code', type: 'string', example: 'validation_failed'),
                new OA\Property(property: 'message', type: 'string', example: '参数校验失败'),
                new OA\Property(
                    property: 'details',
                    type: 'object',
                    additionalProperties: new OA\AdditionalProperties(type: 'array', items: new OA\Items(type: 'string')),
                    description: '字段级错误信息,字段名 => 消息列表',
                ),
            ],
            type: 'object',
        ),
    ],
)]
final class ErrorResponseSchema
{
}
