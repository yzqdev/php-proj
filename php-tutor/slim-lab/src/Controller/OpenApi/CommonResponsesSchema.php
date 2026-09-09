<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 公共响应组件：业务错误信封（与 apiError() 的 JSON 结构一致）。
 */
#[OA\Response(response: 'BadRequest', description: '业务/参数错误（code=400）', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'))]
#[OA\Response(response: 'Unauthorized', description: '未登录 / 令牌无效（code=401）', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'))]
#[OA\Response(response: 'NotFound', description: '未知模块、未知操作，或分享/目录不存在（code=404）', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'))]
#[OA\Response(response: 'MethodNotAllowed', description: '方法不允许：POST-only 动作用非 POST 访问（code=405）', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'))]
#[OA\Response(response: 'Gone', description: '分享链接已过期（code=410）', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'))]
#[OA\Response(response: 'TooManyRequests', description: '登录/注册限流：同 IP 60 秒内超过 10 次（code=429）', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'))]
#[OA\Response(response: 'ServerError', description: '服务器内部错误（code=500）', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'))]
final class CommonResponsesSchema
{
}
