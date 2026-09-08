<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

/**
 * ============================================================
 * CORS 中间件（跨域资源共享）— Slim PSR-15 版
 * ============================================================
 *
 * 对比 Java / Spring Boot：
 *   - @CrossOrigin / WebConfigurer.addCorsMappings
 *   - Spring Security 的 cors(Customizer.withDefaults())
 *
 * 关键安全点：
 *   - **禁止 * 配合凭证**：Access-Control-Allow-Origin: * 与 Allow-Credentials: true 不能同时用；
 *     本项目 Bearer Token 不用 Cookie，所以 supports_credentials=false 更安全；
 *   - **白名单严格匹配**：Origin 头是完整 URL（含 scheme://host:port），不带尾斜杠；
 *     白名单里的每一项要精确对应前端的实际域名。
 *
 * 中间件签名（PSR-15）：
 *   __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
 */
final class Cors
{
    public function __invoke(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $cors = Config::get('api.cors', []);
        $allowedOrigins  = $cors['allowed_origins'] ?? [];
        $allowedMethods  = $cors['allowed_methods'] ?? ['GET', 'POST'];
        $allowedHeaders  = $cors['allowed_headers'] ?? ['Authorization', 'Content-Type'];
        $supportsCred    = (bool)($cors['supports_credentials'] ?? false);
        $maxAge          = (int)($cors['max_age'] ?? 86400);

        $origin = $request->getHeaderLine('Origin');

        // 匹配白名单：只有 Origin 精确匹配时才返回 CORS 头
        $allowed = false;
        foreach ($allowedOrigins as $o) {
            if (strcasecmp($o, $origin) === 0) {
                $allowed = true;
                break;
            }
        }

        // 同源请求自动放行（Origin 与服务器自己的域名一致时）
        if (!$allowed && $origin !== '') {
            $serverHost = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            if (strcasecmp($origin, "{$scheme}://{$serverHost}") === 0) {
                $allowed = true;
            }
        }

        // OPTIONS 预检请求：直接返回 204，不进入 handler
        if ($request->getMethod() === 'OPTIONS') {
            $response = new Response();
            $response = $response->withHeader('Access-Control-Max-Age', (string)$maxAge);
            if ($allowed) {
                $response = $response
                    ->withHeader('Access-Control-Allow-Origin', $origin)
                    ->withHeader('Vary', 'Origin')
                    ->withHeader('Access-Control-Allow-Methods', implode(', ', $allowedMethods))
                    ->withHeader('Access-Control-Allow-Headers', implode(', ', $allowedHeaders));
                if ($supportsCred) {
                    $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
                }
            }
            return $response->withStatus(204);
        }

        // 无 Origin（同源请求或 curl 直连）不需要 CORS 头，直接放行
        if ($origin === '' || $allowed) {
            $response = $handler->handle($request);
            if ($allowed) {
                $response = $response
                    ->withHeader('Access-Control-Allow-Origin', $origin)
                    ->withHeader('Vary', 'Origin')
                    ->withHeader('Access-Control-Allow-Methods', implode(', ', $allowedMethods))
                    ->withHeader('Access-Control-Allow-Headers', implode(', ', $allowedHeaders));
                if ($supportsCred) {
                    $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
                }
            }
            return $response;
        }

        // Origin 不在白名单：拒绝（返回 403，不返回 CORS 头）
        $response = new Response();
        $response->getBody()->write(json_encode([
            'code'    => 40300,
            'message' => 'CORS 拒绝：来源不在白名单内',
            'data'    => null,
        ], JSON_UNESCAPED_UNICODE));
        return $response
            ->withHeader('Content-Type', 'application/json; charset=UTF-8')
            ->withStatus(403);
    }
}