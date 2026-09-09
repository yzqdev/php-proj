<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Controller\ResponseFactory;

/**
 * clouddrive 鉴权中间件：等价旧 api/clouddrive.php 中每个受保护分支开头的
 *   if (!isset($_SESSION['login']) || !$_SESSION['login']) apiError('未登录', 401);
 *   以及 apiError('方法不允许', 405)。
 * 规则集中在 CloudDriveAccessRules（与旧代码逐分支一致），未通过时直接返回信封。
 */
final class CloudDriveAuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $action = (string) ($request->getQueryParams()['action'] ?? '');

        // 先方法校验（405），再登录校验（401）—— 与旧代码逐分支顺序一致。
        // 未知 action 不做登录拦截，落到控制器 default 分支返回 400"未知操作"（等价旧 switch default）。
        $violation = CloudDriveAccessRules::violation($action, $request->getMethod());
        if ($violation !== null) {
            return ResponseFactory::error($this->responseFactory->createResponse($violation['code']), $violation['message'], $violation['code']);
        }

        return $handler->handle($request);
    }
}
