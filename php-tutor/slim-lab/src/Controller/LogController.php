<?php

declare(strict_types=1);

namespace App\Controller;

use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Util\LogStore;

/**
 * 运行日志查询接口（需 Bearer Token）：
 *   GET /api/logs/days           列出可查的日期（新→旧）
 *   GET /api/logs?date=&offset=&limit=  分页读取某一天
 */


final class LogController
{
    public function __construct(
        private readonly LogStore $logs,
    ) {
    }
    #[OA\Get(
        path: '/api/logs/days',
        tags: ['Logs'],
        summary: '列出可查的日志日期',
        security: [['BearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'limit', in: 'query', description: '最多返回的天数', schema: new OA\Schema(type: 'integer', default: 30, maximum: 90)),
        ],
        responses: [
            new OA\Response(response: '200', description: '成功', content: new OA\JsonContent(ref: '#/components/schemas/LogDaysData')),
            new OA\Response(response: '401', description: '未登录'),
            new OA\Response(response: '500', description: '服务器错误'),
        ]
    )]
    public function days(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getQueryParams();

        return ResponseFactory::ok($response, $this->logs->days((int) ($params['limit'] ?? 30)));
    }
    #[OA\Get(
        path: '/api/logs',
        tags: ['Logs'],
        summary: '分页读取某一天的日志',
        security: [['BearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'date', in: 'query', description: '日志日期 YYYY-MM-DD', schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'offset', in: 'query', description: '起始行号', schema: new OA\Schema(type: 'integer', default: 0, minimum: 0)),
            new OA\Parameter(name: 'limit', in: 'query', description: '本页最大行数', schema: new OA\Schema(type: 'integer', default: 500, maximum: 2000)),
        ],
        responses: [
            new OA\Response(response: '200', description: '成功', content: new OA\JsonContent(ref: '#/components/schemas/LogData')),
            new OA\Response(response: '401', description: '未登录'),
            new OA\Response(response: '500', description: '服务器错误'),
        ]
    )]
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
