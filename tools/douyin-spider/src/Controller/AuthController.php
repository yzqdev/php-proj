<?php

declare(strict_types=1);

namespace Yzqde\DouyinSpider\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yzqde\DouyinSpider\DTO\UserLoginDto;
use Yzqde\DouyinSpider\DTO\UserRegisterDto;
use Yzqde\DouyinSpider\Service\AuthService;

use function Yzqde\DouyinSpider\Support\json;

class AuthController
{
    public function __construct(
        private readonly AuthService $authService,
    ) {
    }

    /**
     * POST /api/auth/register
     */
    public function register(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = (array) $request->getParsedBody();

        $dto = new UserRegisterDto(
            username: $body['username'] ?? '',
            email: $body['email'] ?? '',
            password: $body['password'] ?? '',
        );

        if ($dto->username === '' || $dto->email === '' || $dto->password === '') {
            return json($response, ['code' => -1, 'message' => '用户名、邮箱和密码不能为空', 'data' => null], 400);
        }

        if (strlen($dto->password) < 6) {
            return json($response, ['code' => -1, 'message' => '密码长度至少6位', 'data' => null], 400);
        }

        $user = $this->authService->register($dto);

        return json($response, [
            'code' => 0,
            'message' => '注册成功',
            'data' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'role' => $user->getRole(),
                'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            ],
        ], 201);
    }

    /**
     * POST /api/auth/login
     */
    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = (array) $request->getParsedBody();

        $dto = new UserLoginDto(
            username: $body['username'] ?? '',
            password: $body['password'] ?? '',
        );

        if ($dto->username === '' || $dto->password === '') {
            return json($response, ['code' => -1, 'message' => '用户名和密码不能为空', 'data' => null], 400);
        }

        $tokens = $this->authService->login($dto);

        return json($response, [
            'code' => 0,
            'message' => '登录成功',
            'data' => $tokens,
        ]);
    }

    /**
     * POST /api/auth/refresh
     */
    public function refreshToken(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = (array) $request->getParsedBody();
        $refreshToken = $body['refresh_token'] ?? '';

        if ($refreshToken === '') {
            return json($response, ['code' => -1, 'message' => '请提供 refresh_token', 'data' => null], 400);
        }

        $tokens = $this->authService->refreshToken($refreshToken);

        return json($response, [
            'code' => 0,
            'message' => 'token 刷新成功',
            'data' => $tokens,
        ]);
    }
}
