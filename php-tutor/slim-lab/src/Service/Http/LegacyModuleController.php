<?php

declare(strict_types=1);

namespace Service\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Service\Middleware\CloudDriveAccessRules;

/**
 * 旧版直连入口兼容：完整复刻迁移前根目录 index.php 的行为。
 *   - /?module=php|doctrine|clouddrive&action=xxx → 转发到对应模块控制器（含 clouddrive 的
 *     session_start 与访问规则校验，行为与旧文件一致）
 *   - module 缺失/未知（包括其他未匹配路径）→ {"code":404,"message":"未知模块","data":null}
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
