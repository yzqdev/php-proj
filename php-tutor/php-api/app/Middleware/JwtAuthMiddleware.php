<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Services\TokenService;
use App\Support\UnauthenticatedException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Validates the Bearer token and attaches user_id to the request.
 */
final class JwtAuthMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly TokenService $tokens)
    {
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $header = $request->getHeaderLine('Authorization');

        if ($header === '' || !preg_match('/^Bearer\s+(\S+)$/i', $header, $m)) {
            throw new UnauthenticatedException('缺少 Authorization: Bearer <token> 请求头');
        }

        $userId = $this->tokens->getUserIdFromAccessToken($m[1]);

        $request = $request
            ->withAttribute('user_id', $userId)
            ->withAttribute('token', $m[1]);

        return $handler->handle($request);
    }
}
