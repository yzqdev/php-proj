<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Helpers\JsonResponse;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * ============================================================
 * 健康检查 API 控制器 — Slim 4 风格
 * ============================================================
 *
 * 端点：
 *   GET /api/v1/health  不需要鉴权，返回服务状态和当前时间
 */
class HealthController
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
        path: '/health',
        description: '不需要鉴权，返回服务状态和当前时间',
        summary: '服务健康检查',
        tags: ['Health'],
        responses: [
            new OA\Response(
                response: 200,
                description: '服务正常',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/HealthInfo',
                ),
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