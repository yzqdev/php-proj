<?php

declare(strict_types=1);

namespace Service\Http\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 公共响应组件：错误信封（与迁移前 apiError 的 JSON 结构完全一致）。
 */
#[OA\Response(
    response: 'BadRequest',
    description: '业务/参数错误（code=400）',
    content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'),
)]
#[OA\Response(
    response: 'Unauthorized',
    description: '未登录（code=401）',
    content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'),
)]
#[OA\Response(
    response: 'MethodNotAllowed',
    description: '方法不允许（code=405）',
    content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'),
)]
#[OA\Response(
    response: 'ServerError',
    description: '服务器内部错误（code=500）',
    content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'),
)]
final class CommonResponsesSchema
{
}
