<?php

declare(strict_types=1);

namespace Yzqde\Playground\Controller;

use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yzqde\Playground\Exception\LogNotFoundException;
use Yzqde\Playground\Response\ApiResponse;
use Yzqde\Playground\Service\LogService;

/**
 * 日志查看控制器:供前端日志页读取按天的 Monolog 日志文件
 *
 * 两个端点都在路由上挂了 LogAccessGuard,仅本机来源可访问(见 .env 的 LOG_VIEW_ALLOW_ANY)。
 * 只读接口,不做任何写操作,日志清理需另行处理。
 */
final readonly class LogController
{
    public function __construct(private LogService $logs)
    {
    }

    #[OA\Get(
        path: '/api/logs',
        operationId: 'listLogFiles',
        description: '按天分文件,Monolog 按日期写入 storage/logs/ 下的 app-YYYY-MM-DD.log 与 error-YYYY-MM-DD.log;按修改时间倒序返回',
        summary: '日志文件列表',
        tags: ['日志'],
        responses: [
            new OA\Response(
                response: '200',
                description: '日志文件列表',
                content: new OA\JsonContent(
                    required: ['success', 'data'],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            description: '日志文件列表,按修改时间倒序',
                            type: 'array',
                            items: new OA\Items(
                                required: ['name', 'channel', 'date', 'size', 'mtime'],
                                properties: [
                                    new OA\Property(property: 'name', type: 'string', example: 'app-2026-09-07.log'),
                                    new OA\Property(property: 'channel', type: 'string', enum: ['app', 'error'], example: 'app'),
                                    new OA\Property(property: 'date', type: 'string', format: 'date', example: '2026-09-07'),
                                    new OA\Property(property: 'size', type: 'integer', format: 'int64', description: '字节数', example: 17086),
                                    new OA\Property(property: 'mtime', type: 'integer', format: 'int64', description: '最后写入时间,Unix 秒', example: 1757200208),
                                ],
                                type: 'object',
                            ),
                        ),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: '403',
                description: '非本机来源访问被拒绝',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'),
            ),
        ],
    )]
    public function list(Request $request, Response $response): Response
    {
        return ApiResponse::ok($response, $this->logs->list());
    }

    #[OA\Get(
        path: '/api/logs/{name}',
        operationId: 'tailLogFile',
        description: '从文件末尾回读并解析出时间戳、级别、消息与上下文,按最新在前返回;仅回读固定窗口字节,大文件也不会整份载入',
        summary: '查看日志末尾',
        tags: ['日志'],
        parameters: [
            new OA\Parameter(
                name: 'name',
                description: '日志文件名,形如 app-2026-09-07.log',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', example: 'app-2026-09-07.log'),
            ),
            new OA\Parameter(
                name: 'limit',
                description: '最多返回多少条,默认 200,上限 1000',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', format: 'int64', minimum: 1, maximum: 1000, example: 200),
            ),
            new OA\Parameter(
                name: 'level',
                description: '只返回该级别的记录,不传则返回全部',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'ERROR',
                    enum: ['DEBUG', 'INFO', 'NOTICE', 'WARNING', 'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'],
                ),
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: '日志记录,最新在最前',
                content: new OA\JsonContent(
                    required: ['success', 'data'],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            required: ['file', 'total', 'limit', 'lines'],
                            properties: [
                                new OA\Property(property: 'file', type: 'string', example: 'app-2026-09-07.log'),
                                new OA\Property(property: 'total', type: 'integer', format: 'int64', description: '回读窗口内的记录总数(不含本次返回条数限制)', example: 122),
                                new OA\Property(property: 'limit', type: 'integer', format: 'int64', example: 200),
                                new OA\Property(property: 'level', type: 'string', nullable: true, example: 'ERROR'),
                                new OA\Property(
                                    property: 'lines',
                                    description: '日志记录,最新在最前',
                                    type: 'array',
                                    items: new OA\Items(
                                        required: ['level', 'message'],
                                        properties: [
                                            new OA\Property(property: 'ts', type: 'string', nullable: true, description: 'ISO 8601 时间戳', example: '2026-09-07T22:08:08.335037+00:00'),
                                            new OA\Property(property: 'channel', type: 'string', nullable: true, example: 'app'),
                                            new OA\Property(property: 'level', type: 'string', example: 'INFO'),
                                            new OA\Property(property: 'message', type: 'string', example: '图片已删除'),
                                            new OA\Property(
                                                property: 'context',
                                                description: '日志上下文;含 exception、file、trace 等字段,不可视为可信内容',
                                                type: 'object',
                                                nullable: true,
                                            ),
                                        ],
                                        type: 'object',
                                    ),
                                ),
                            ],
                            type: 'object',
                        ),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: '500',
                description: '日志文件不存在或文件名不合法。当前实现统一返回 500 而非语义上的 404,见项目已知问题',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'),
            ),
            new OA\Response(
                response: '403',
                description: '非本机来源访问被拒绝',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'),
            ),
        ],
    )]
    /**
     * 读取单个日志文件的末尾记录
     *
     * @param array<string, mixed> $args 路由参数,`name` 为日志文件名
     *
     * @throws LogNotFoundException 文件名不合法或文件不存在,由错误中间件统一转成 JSON 响应
     */
    public function tail(Request $request, Response $response, array $args): Response
    {
        $params = $request->getQueryParams();
        $name = (string)($args['name'] ?? '');
        $limit = (int)($params['limit'] ?? 0);
        $level = isset($params['level']) && $params['level'] !== '' ? (string)$params['level'] : null;

        return ApiResponse::ok($response, $this->logs->tail($name, $limit, $level));
    }
}
