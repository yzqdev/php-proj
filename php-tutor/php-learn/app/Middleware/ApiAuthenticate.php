<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exceptions\BusinessException;
use App\Helpers\JsonResponse;
use App\Services\TokenService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

/**
 * ============================================================
 * API 鉴权中间件（Bearer Token）— Slim PSR-15 版
 * ============================================================
 *
 * 对比 Java：Spring Security 的 JwtAuthenticationFilter
 *
 * 中间件做的事：
 *   1. 从 Authorization 头解析 Bearer Token
 *   2. 用 TokenService::verifyAccess() 校验（内部验 JWT 签名 + 过期）
 *   3. 把用户信息挂到 $_SERVER['API_USER'] 供 action 通过 currentUser() 读取
 *   4. 校验失败直接返回 401 JSON，不进入 action
 */
final class ApiAuthenticate
{
    public function __invoke(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // 1) 解析 Authorization 头
        $authHeader = $request->getHeaderLine('Authorization');

        // 兼容 nginx 环境（有时需要重写 fastcgi.conf 才能保留 Authorization 头）
        if ($authHeader === '') {
            $authHeader = $request->getHeaderLine('X-Auth-Token');
            if ($authHeader !== '' && !str_starts_with($authHeader, 'Bearer ')) {
                $authHeader = 'Bearer ' . $authHeader;
            }
        }

        // 2) 提取 token（Bearer xxx）
        $token = self::extractBearer($authHeader);
        if ($token === null) {
            return self::sendUnauthorized(JsonResponse::CODE_UNAUTHORIZED, '未提供访问令牌');
        }

        // 3) 校验 Token
        try {
            $user = TokenService::make()->verifyAccess($token);
        } catch (\Throwable $e) {
            if ($e instanceof BusinessException) {
                return self::sendUnauthorized($e->getBizCode(), $e->getMessage());
            }
            return self::sendUnauthorized(JsonResponse::CODE_INVALID_TOKEN, '令牌无效或已过期');
        }

        // 4) 把用户信息挂到 $_SERVER 供 action 读取
        $_SERVER['API_USER'] = $user;

        return $handler->handle($request);
    }

    /**
     * 从 Authorization 头提取 Bearer Token
     *
     * 期望格式：Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6...
     * 大小写不敏感（Bearer 也接受 bearer / BEARER）
     *
     * @return string|null 提取失败返回 null
     */
    private static function extractBearer(string $header): ?string
    {
        if (preg_match('/^Bearer\s+(\S+)$/i', $header, $m)) {
            return $m[1];
        }
        return null;
    }

    /**
     * 发送 401 响应并结束请求
     */
    private static function sendUnauthorized(int $bizCode, string $message): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(json_encode([
            'code'    => $bizCode,
            'message' => $message,
            'data'    => null,
        ], JSON_UNESCAPED_UNICODE));
        return $response
            ->withHeader('Content-Type', 'application/json; charset=UTF-8')
            ->withStatus(401);
    }

    /**
     * 便捷：获取当前登录用户（在 action 里调）
     *
     * @return array{userId:int, username:string, role:string}|null
     */
    public static function currentUser(): ?array
    {
        return $_SERVER['API_USER'] ?? null;
    }

    /**
     * 便捷：获取当前用户 ID（未登录返回 null）
     */
    public static function currentUserId(): ?int
    {
        $user = $_SERVER['API_USER'] ?? null;
        return $user !== null ? (int)($user['userId'] ?? 0) : null;
    }
}