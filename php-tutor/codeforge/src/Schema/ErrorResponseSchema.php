<?php

declare(strict_types=1);

namespace App\Schema;

use OpenApi\Attributes as OA;

/**
 * 错误响应（所有端点统一的结构）
 *
 * code 为业务错误码，同时等于 HTTP 状态码；data 为附加信息，无附加信息时为 null。
 */
#[OA\Schema(
    schema: 'ErrorResponse',
    required: ['code', 'message'],
    properties: [
        new OA\Property(property: 'code', type: 'integer', description: '业务错误码，与 HTTP 状态码一致', example: 404),
        new OA\Property(property: 'message', type: 'string', description: '错误提示', example: '资源不存在'),
        new OA\Property(property: 'data', nullable: true, description: '附加信息，无则为 null'),
    ],
)]
final class ErrorResponseSchema
{
}
