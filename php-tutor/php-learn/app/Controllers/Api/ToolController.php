<?php

namespace App\Controllers\Api;

use App\Helpers\JsonResponse;
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
        path: '/tool/check',
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
}