<?php

declare(strict_types=1);

namespace Service\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Service\Log\LogStore;

/**
 * 运行日志查询接口（需 Bearer Token）：
 *   GET /api/logs/days           列出可查的日期（新→旧）
 *   GET /api/logs?date=&offset=&limit=  分页读取某一天
 *
 * 日志含内部路径、异常消息与请求上下文，因此挂在鉴权中间件之后，不对未登录用户开放。
 */
final class LogController
{
    public function __construct(
        private readonly LogStore $logs,
    ) {
    }

    public function days(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getQueryParams();

        return ResponseFactory::ok($response, $this->logs->days((int) ($params['limit'] ?? 30)));
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getQueryParams();

        // 未指定日期时默认今天，前端进入页面即可看到实时日志
        $date = (string) ($params['date'] ?? '') ?: date('Y-m-d');

        return ResponseFactory::ok(
            $response,
            $this->logs->readDay($date, (int) ($params['offset'] ?? 0), (int) ($params['limit'] ?? 500)),
        );
    }
}
