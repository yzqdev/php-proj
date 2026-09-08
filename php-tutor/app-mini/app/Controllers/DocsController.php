<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\ApiException;
use OpenApi\Generator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * 文档接口:Swagger UI 页面(CDN)与 OpenAPI 规范 JSON。
 */
final class DocsController
{
    public function __construct(private readonly string $appUrl)
    {
    }

    public function openapi(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            // swagger-php 4.x 在 PHP 8.5 上会触发 SplObjectStorage::contains() 弃用提示,
            // 该提示会污染 JSON 输出,故扫描期间临时关闭弃用级错误报告。
            $previousReporting = error_reporting(error_reporting() & ~E_DEPRECATED & ~E_USER_DEPRECATED);
            try {
                $openapi = Generator::scan([app_path()]);
            } finally {
                error_reporting($previousReporting);
            }

            $spec = json_decode($openapi->toJson(), true, 512, JSON_THROW_ON_ERROR);
            // 注入运行时 server 地址(注解只能写常量,故在此动态注入)
            $spec['servers'] = [['url' => $this->appUrl]];
            $body = json_encode($spec, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (ApiException) {
            throw new ApiException('OpenAPI 规范生成失败', 500, 'openapi_error');
        } catch (Throwable $exception) {
            throw new ApiException('OpenAPI 规范生成失败: ' . $exception->getMessage(), 500, 'openapi_error', [], $exception);
        }

        $response->getBody()->write($body);

        return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
    }

    public function docs(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $html = <<<'HTML'
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>多用户博客 REST API - Swagger UI</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js" crossorigin></script>
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            window.ui = SwaggerUIBundle({
                url: '/docs/openapi.json',
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [SwaggerUIBundle.presets.apis],
                layout: 'BaseLayout'
            });
        });
    </script>
</body>
</html>
HTML;

        $response->getBody()->write($html);

        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
