<?php

namespace App\Controllers\Api;

use App\Dto\LoginDto;
use App\Helpers\JsonResponse;
use App\Helpers\RequestBody;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;
class ToolController
{
    /**
     * GET /api/v1/health
     *
     * @summary 服务健康检查
     * @description 不需要鉴权，返回服务状态和当前时间
     * @tag Health
     * @response 200 HealthInfo 服务正常
     */
    #[OA\Get(
        path: '/api/v1/tool/check',
        description: '不需要鉴权，返回服务状态和当前时间',
        summary: '服务健康检查',
        tags: ['Tool'],
        responses: [
            new OA\Response(
                response: 200,
                description: '服务正常',

            ),
        ],
    )]
    public function check(ServerRequestInterface $request): ResponseInterface
    {
        return JsonResponse::ok([
            'status'  => 'up',
            'time'    => date('Y-m-d H:i:s'),
            'version' => 'v1',
        ]);
    }
    #[OA\Post(
        path: '/api/v1/tool/login',
        description: '用户登录接口',
        summary: '用户登录',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['username', 'password'],
                properties: [
                    new OA\Property(
                        property: 'username',
                        type: 'string',
                        description: '用户名',
                        example: 'admin',
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        description: '密码',
                        example: '123456',
                    ),
                ],
            ),
        ),
        tags: ['Tool'],
        responses: [
            new OA\Response(
                response: 200,
                description: '登录成功',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'code',
                            type: 'integer',
                            example: 200,
                        ),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'success',
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'token',
                                    type: 'string',
                                    example: 'eyJhbGciOiJIUzI1NiIs...',
                                ),
                            ],
                        ),
                    ],
                    type: 'object',
                ),
            ),
        ],
    )]
    public function login(ServerRequestInterface $request): ResponseInterface
    {
        $data=RequestBody::entity($request,LoginDto::class);
        return JsonResponse::ok(
            $data
        );
    }
}