<?php

declare(strict_types=1);

namespace App\Controller;

use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Middleware\CloudDriveAccessRules;

/**
 * 旧版直连入口兼容：完整复刻迁移前根目录 index.php 的行为。
 */

final class LegacyModuleController
{
    private const MODULE_MAP = [
        'doctrine' => DoctrineController::class,
        'php' => PhpDemoController::class,
        'clouddrive' => CloudDriveController::class,
    ];

    public function __construct(
        private readonly PhpDemoController $php,
        private readonly DoctrineController $doctrine,
        private readonly CloudDriveController $clouddrive,
        private readonly ResponseFactory $responseFactory = new ResponseFactory(),
    ) {
    }
    #[OA\Get(
        path: '/api/legacy/{module}',
        tags: ['Legacy'],
        summary: '旧版接口兼容入口',
        parameters: [
            new OA\Parameter(name: 'module', in: 'path', required: true, description: '模块名：php/doctrine/clouddrive', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: '200', description: '成功'),
            new OA\Response(response: '404', description: '未知模块'),
        ]
    )]
    public function handle(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $module = (string) ($request->getQueryParams()['module'] ?? '');

        $controller = match ($module) {
            'php' => $this->php,
            'doctrine' => $this->doctrine,
            'clouddrive' => $this->clouddrive,
            default => null,
        };
        if ($controller === null) {
            return ResponseFactory::error($response, '未知模块', 404);
        }

        // 旧版 api/clouddrive.php 顶部 session_start() + 分支内的方法/登录校验
        if ($module === 'clouddrive') {
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            $action = (string) ($request->getQueryParams()['action'] ?? '');
            $violation = CloudDriveAccessRules::violation($action, $request->getMethod());
            if ($violation !== null) {
                return ResponseFactory::error($response, $violation['message'], $violation['code']);
            }
        }

        return $controller->handle($request, $response);
    }
}
