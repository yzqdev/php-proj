<?php

declare(strict_types=1);

namespace Yzqde\Playground\Controller;

use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Factory\StreamFactory;
use Yzqde\Playground\Service\OpenApiSpecService;

/**
 * 接口文档控制器:只提供 /openapi.json 与 /docs,不参与图床业务
 * 自身也带注解,让文档页能自描述(否则会漏掉这两个端点)
 */
final class OpenApiDocsController
{
    public function __construct(
        private readonly OpenApiSpecService $spec,
        private readonly string $uiPath,
    ) {
    }

    /**
     * 输出 OpenAPI 3.0 规范 JSON
     */
    #[OA\Get(
        path: '/openapi.json',
        operationId: 'getOpenApiSpec',
        summary: 'OpenAPI 规范',
        tags: ['文档'],
        parameters: [
            new OA\Parameter(
                name: 'refresh',
                in: 'query',
                required: false,
                description: '传 1 忽略缓存强制重新生成(改完注解、或升级 swagger-php 后手动刷新一次)',
                schema: new OA\Schema(type: 'string', enum: ['0', '1']),
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'OpenAPI 3.0 规范文档',
                content: new OA\JsonContent(type: 'object'),
            ),
            new OA\Response(
                response: '500',
                description: '规范生成失败',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'),
            ),
        ],
    )]
    public function spec(Request $request, Response $response): Response
    {
        $forceRefresh = (string)($request->getQueryParams()['refresh'] ?? '') === '1';
        $body = (new StreamFactory())->createStream($this->spec->json($forceRefresh));

        return $response
            ->withBody($body)
            ->withHeader('Content-Type', 'application/json; charset=UTF-8')
            ->withHeader('Cache-Control', 'no-cache');
    }

    /**
     * 输出 Swagger UI 页面;HTML 由 public/docs.html 提供,改样式只动那个文件
     */
    #[OA\Get(
        path: '/docs',
        operationId: 'getDocsUi',
        summary: '接口文档页面(Swagger UI)',
        tags: ['文档'],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Swagger UI 页面',
                content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string')),
            ),
            new OA\Response(
                response: '500',
                description: '文档页文件缺失',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'),
            ),
        ],
    )]
    public function ui(Request $request, Response $response): Response
    {
        if (!is_file($this->uiPath)) {
            throw new RuntimeException('文档页文件缺失:' . $this->uiPath);
        }

        $body = (new StreamFactory())->createStreamFromFile($this->uiPath, 'rb');

        return $response
            ->withBody($body)
            ->withHeader('Content-Type', 'text/html; charset=UTF-8')
            ->withHeader('Cache-Control', 'no-cache');
    }
}
