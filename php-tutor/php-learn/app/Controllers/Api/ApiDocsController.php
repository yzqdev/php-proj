<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\OpenApi\SpecGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\StreamFactory;

/**
 * ============================================================
 * API 文档控制器（Swagger UI + OpenAPI 3.0）
 * ============================================================
 *
 * 端点：
 *   GET /api/v1/docs         返回 Swagger UI 页面（HTML）
 *   GET /api/v1/docs/spec    返回 OpenAPI 3.0 规范（JSON）
 *
 * OpenAPI spec 生成方式：
 *   - 使用 zircote/swagger-php（Composer 包）扫描 src/ 下所有
 *     #[OA\Get] / #[OA\Post] / #[OA\Put] / #[OA\Delete] 属性注解
 *   - 全局信息（info / servers / tags / securitySchemes）集中在
 *     app/OpenApi/Spec.php 的 #[OA\OpenApi] 属性注解
 *   - 组件 schema 直接定义在 Entity 类上（#[OA\Schema] + #[OA\Property]）
 *     不再需要单独的 Schemas.php 手写数组
 *
 * 对应 Java / Spring Boot：
 *   - springdoc-openapi-ui 自动有 /swagger-ui.html
 *   - swagger-php 等价于 springdoc-openapi（注解 → OpenAPI spec）
 *   - swagger-ui 是纯前端资源（JS + CSS），本地加载即可
 *
 * 设计原则（与 playground 对齐）：
 *   - 控制器方法返回 PSR-7 ResponseInterface，不直接 header()/echo()/exit()
 *   - HTML 页面从 public/docs.html 静态文件读取，通过 StreamFactory 注入
 *   - spec JSON 由 SpecGenerator::generate() 生成，支持缓存
 */
class ApiDocsController
{
    public function __construct(
        private readonly string $uiPath,
    ) {
    }

    /**
     * GET /api/v1/docs — Swagger UI HTML 页面
     *
     * 页面功能：
     *   - 展示所有 /api/v1/* 接口
     *   - 支持 Authorize 注入 Bearer Token
     *   - 支持在浏览器里直接调接口测试
     */
    public function docs(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (!is_file($this->uiPath)) {
            $body = (new StreamFactory())->createStream('文档页文件缺失: ' . $this->uiPath);
            return $response
                ->withBody($body)
                ->withHeader('Content-Type', 'text/plain; charset=UTF-8')
                ->withStatus(500);
        }

        $body = (new StreamFactory())->createStreamFromFile($this->uiPath, 'rb');
        return $response
            ->withBody($body)
            ->withHeader('Content-Type', 'text/html; charset=UTF-8')
            ->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
    }

    /**
     * GET /api/v1/docs/spec — OpenAPI 3.0 规范 JSON
     *
     * 用途：
     *   - 给其他客户端/编辑器读取 spec
     *   - 便于自动化测试生成
     *   - 便于把 spec 同步到 API 网关
     */
    public function spec(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $spec = SpecGenerator::generate();

        $json = json_encode($spec, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        $body = (new StreamFactory())->createStream($json);

        return $response
            ->withBody($body)
            ->withHeader('Content-Type', 'application/json; charset=UTF-8')
            ->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
    }
}
