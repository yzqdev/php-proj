<?php

declare(strict_types=1);

namespace Yzqde\Playground\Controller;

use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Factory\StreamFactory;
use Yzqde\Playground\Dto\Image;
use Yzqde\Playground\Response\ApiResponse;
use Yzqde\Playground\Service\ImageService;
use Yzqde\Playground\Service\UploadedFile;

/**
 * 图床 API 控制器:只做参数提取、调用 Service、组装 JSON 响应,不写业务逻辑
 * 前端页面由独立的 Vue 项目负责,这里不再有任何 HTML 输出
 *
 * 方法上的 OA 注解与 public/index.php 里的路由一一对应,改路由必须同步改注解。
 * 注解参数只能是常量表达式,因此共用参数只能内联,不能抽成方法调用。
 */
final class ImageController
{
    public function __construct(
        private readonly ImageService $images,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[OA\Get(
        path: '/api/health',
        operationId: 'healthCheck',
        summary: '健康检查',
        tags: ['系统'],
        responses: [
            new OA\Response(
                response: '200',
                description: '服务正常',
                content: new OA\JsonContent(
                    required: ['success', 'data'],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            required: ['app', 'time'],
                            properties: [
                                new OA\Property(property: 'app', type: 'string', example: 'image-host'),
                                new OA\Property(
                                    property: 'time',
                                    type: 'string',
                                    format: 'date-time',
                                    example: '2026-09-08T04:31:29+00:00',
                                ),
                            ],
                            type: 'object',
                        ),
                    ],
                    type: 'object',
                ),
            ),
        ],
    )]
    public function health(Request $request, Response $response): Response
    {
        return ApiResponse::ok($response, ['app' => 'image-host', 'time' => date(DATE_ATOM)]);
    }

    #[OA\Get(
        path: '/api/images',
        operationId: 'listImages',
        description: '按上传时间倒序,仅返回白名单内的图片格式(下载接口仍可访问其他类型文件)',
        summary: '图片列表',
        tags: ['图片'],
        responses: [
            new OA\Response(
                response: '200',
                description: '图片列表',
                content: new OA\JsonContent(
                    required: ['success', 'data'],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            description: '图片列表,按上传时间倒序',
                            items: new OA\Items(ref: '#/components/schemas/Image'),
                        ),
                    ],
                    type: 'object',
                ),
            ),
        ],
    )]
    public function list(Request $request, Response $response): Response
    {
        return ApiResponse::ok($response, array_map(fn(Image $image) => $image->toArray(), $this->images->list()));
    }

    #[OA\Post(
        path: '/api/images',
        operationId: 'uploadImage',
        description: '单文件上传。扩展名白名单 jpg/jpeg/png/gif/webp/bmp,并做 finfo 与 getimagesize() 双重内容校验',
        summary: '上传图片',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['file'],
                    properties: [
                        new OA\Property(
                            property: 'file',
                            description: '图片文件,单文件上限由 UPLOAD_MAX_SIZE 决定(默认 10 MB)',
                            type: 'string',
                            format: 'binary',
                        ),
                    ],
                    type: 'object',
                ),
            ),
        ),
        tags: ['图片'],
        parameters: [
            new OA\Parameter(
                name: 'X-Requested-With',
                description: 'CSRF 安全头,后端要求固定值 XMLHttpRequest;前端 axios 实例与文档页自动注入',
                in: 'header',
                required: true,
                schema: new OA\Schema(type: 'string', enum: ['XMLHttpRequest'], example: 'XMLHttpRequest'),
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: '上传成功',
                content: new OA\JsonContent(
                    required: ['success', 'message', 'data'],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: '上传成功:a.png'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Image'),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: '500',
                description: '参数或文件非法(未收到文件、扩展名不在白名单、内容非有效图片、超过大小限制)。当前实现统一返回 500 而非语义上的 400/413,见项目已知问题',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'),
            ),
            new OA\Response(
                response: '403',
                description: '缺少 X-Requested-With 安全头',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'),
            ),
        ],
    )]
    public function upload(Request $request, Response $response): Response
    {
        // 请求体超过 post_max_size 时 PHP 会丢弃全部字段,这里兜底给明确提示
        $file = $request->getUploadedFiles()['file'] ?? null;
        if ($file === null) {
            return ApiResponse::error($response, '没有收到文件(可能超过服务器 post_max_size 限制)', 400);
        }

        $image = $this->images->store(UploadedFile::fromPsr7($file));
        $this->logger->info('图片上传成功', ['name' => $image->name, 'original' => $image->original, 'size' => $image->size]);

        return ApiResponse::ok($response, $image->toArray(), '上传成功:' . $image->original);
    }

    #[OA\Delete(
        path: '/api/images/{name}',
        operationId: 'deleteImage',
        description: '同时删除旁车元数据文件;仅允许删除白名单内的图片格式',
        summary: '删除图片',
        tags: ['图片'],
        parameters: [
            new OA\Parameter(
                name: 'X-Requested-With',
                description: 'CSRF 安全头,后端要求固定值 XMLHttpRequest;前端 axios 实例与文档页自动注入',
                in: 'header',
                required: true,
                schema: new OA\Schema(type: 'string', enum: ['XMLHttpRequest'], example: 'XMLHttpRequest'),
            ),
            new OA\Parameter(
                name: 'name',
                description: '存储文件名',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', example: '5803ef05b9b3fba37f60a78cb180a273.png'),
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: '删除成功',
                content: new OA\JsonContent(
                    required: ['success', 'message'],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: '已删除'),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: '500',
                description: '文件不存在。当前实现统一返回 500 而非语义上的 404,见项目已知问题',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'),
            ),
            new OA\Response(
                response: '403',
                description: '缺少 X-Requested-With 安全头',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'),
            ),
        ],
    )]
    public function delete(Request $request, Response $response, array $args): Response
    {
        $name = (string)($args['name'] ?? '');
        $this->images->delete($name);
        $this->logger->info('图片已删除', ['name' => $name]);
        return ApiResponse::okMessage($response, '已删除');
    }

    #[OA\Get(
        path: '/i/{name}',
        operationId: 'serveImage',
        description: '按真实 MIME inline 输出,可直接放进 <img> 标签或 Markdown;内容不可变,带一年 immutable 缓存',
        summary: '图片外链',
        tags: ['图片'],
        parameters: [
            new OA\Parameter(
                name: 'name',
                description: '存储文件名',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', example: '5803ef05b9b3fba37f60a78cb180a273.png'),
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: '图片二进制流(image/jpeg、image/png、image/gif、image/webp、image/bmp 之一)',
                headers: [
                    new OA\Header(
                        header: 'Cache-Control',
                        description: 'public, max-age=31536000, immutable',
                        schema: new OA\Schema(type: 'string'),
                    ),
                    new OA\Header(
                        header: 'X-Content-Type-Options',
                        schema: new OA\Schema(type: 'string', example: 'nosniff'),
                    ),
                ],
                content: new OA\MediaType(
                    mediaType: 'image/*',
                    schema: new OA\Schema(type: 'string', format: 'binary'),
                ),
            ),
            new OA\Response(
                response: '500',
                description: '文件不存在。当前实现统一返回 500 而非语义上的 404,见项目已知问题',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'),
            ),
        ],
    )]
    public function serve(Request $request, Response $response, array $args): Response
    {
        return $this->sendFile($response, $this->images->resolve((string)($args['name'] ?? ''), imageOnly: true), inline: true);
    }

    #[OA\Get(
        path: '/download/{name}',
        operationId: 'downloadImage',
        description: '附件方式下载,不限制文件类型(存储目录内任意文件均可),缓存策略为 no-store',
        summary: '下载文件',
        tags: ['图片'],
        parameters: [
            new OA\Parameter(
                name: 'name',
                description: '存储文件名',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', example: '5803ef05b9b3fba37f60a78cb180a273.png'),
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: '文件二进制流,按真实扩展名给出 Content-Type',
                headers: [
                    new OA\Header(
                        header: 'Content-Disposition',
                        description: 'attachment,文件名经 RFC 5987 编码以支持非 ASCII 字符',
                        schema: new OA\Schema(
                            type: 'string',
                            example: 'attachment; filename="a.png"; filename*=UTF-8\'\'a.png',
                        ),
                    ),
                    new OA\Header(
                        header: 'Cache-Control',
                        schema: new OA\Schema(type: 'string', example: 'no-store'),
                    ),
                ],
                content: new OA\MediaType(
                    mediaType: 'application/octet-stream',
                    schema: new OA\Schema(type: 'string', format: 'binary'),
                ),
            ),
            new OA\Response(
                response: '500',
                description: '文件不存在。当前实现统一返回 500 而非语义上的 404,见项目已知问题',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'),
            ),
        ],
    )]
    public function download(Request $request, Response $response, array $args): Response
    {
        return $this->sendFile($response, $this->images->resolve((string)($args['name'] ?? '')), inline: false);
    }

    private function sendFile(Response $response, string $path, bool $inline): Response
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $response = $response
            ->withBody((new StreamFactory())->createStreamFromFile($path, 'rb'))
            ->withHeader('Content-Type', ImageService::MIME_BY_EXT[$ext] ?? 'application/octet-stream')
            ->withHeader('Content-Length', (string)filesize($path))
            ->withHeader('X-Content-Type-Options', 'nosniff');

        if ($inline) {
            // 随机文件名内容不可变,允许浏览器/CDN 长缓存
            return $response->withHeader('Cache-Control', 'public, max-age=31536000, immutable');
        }

        $name = basename($path);
        // filename* 用 RFC 5987 携带非 ASCII 文件名
        return $response
            ->withHeader('Content-Disposition', 'attachment; filename="' . $name . '"; filename*=UTF-8\'\'' . rawurlencode($name))
            ->withHeader('Cache-Control', 'no-store');
    }
}
