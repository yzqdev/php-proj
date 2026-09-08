<?php
declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Services\AuthService;
use App\Support\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * POST /api/v1/auth/register
 */
final class RegisterController
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $input = (array) $request->getParsedBody();

        $payload = $this->auth->register($input);

        return Response::success([
            'user' => $payload['user']->only(['id', 'username', 'email', 'created_at']),
            'access_token' => $payload['access_token'],
            'refresh_token' => $payload['refresh_token'],
        ], 201);
    }
}
