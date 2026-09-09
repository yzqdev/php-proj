<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Services\AuthService;
use App\Support\NotFoundException;
use App\Support\Response;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Auth controller consolidating register, login, refresh, and me endpoints.
 */
final readonly class AuthController
{
    public function __construct(private AuthService $auth)
    {
    }

    #[OA\Post(
        path: '/api/v1/auth/register',
        summary: '用户注册',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['username', 'email', 'password'],
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'johndoe'),
                    new OA\Property(property: 'email', type: 'string', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123'),
                ]
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(response: '201', description: '注册成功，返回用户信息和 JWT Token',
                content: new OA\JsonContent(ref: '#/components/schemas/TokenResponse')
            ),
            new OA\Response(response: '422', description: '验证失败'),
        ]
    )]
    public function register(ServerRequestInterface $request): ResponseInterface
    {
        $input = (array) $request->getParsedBody();

        $payload = $this->auth->register($input);

        return Response::success([
            'user' => $payload['user']->only(['id', 'username', 'email', 'created_at']),
            'access_token' => $payload['access_token'],
            'refresh_token' => $payload['refresh_token'],
        ], 201);
    }

    #[OA\Post(
        path: '/api/v1/auth/login',
        summary: '用户登录',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['username', 'password'],
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'johndoe'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123'),
                ]
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(response: '200', description: '登录成功，返回用户信息和 JWT Token',
                content: new OA\JsonContent(ref: '#/components/schemas/TokenResponse')
            ),
            new OA\Response(response: '401', description: '用户名或密码错误'),
            new OA\Response(response: '422', description: '验证失败'),
        ]
    )]
    public function login(ServerRequestInterface $request): ResponseInterface
    {
        $input = (array) $request->getParsedBody();

        $payload = $this->auth->login($input);

        return Response::success([
            'user' => $payload['user']->only(['id', 'username', 'email', 'created_at']),
            'access_token' => $payload['access_token'],
            'refresh_token' => $payload['refresh_token'],
        ]);
    }

    #[OA\Post(
        path: '/api/v1/auth/refresh',
        summary: '刷新 Token',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['refresh_token'],
                properties: [
                    new OA\Property(property: 'refresh_token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGc...'),
                ]
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(response: '200', description: '刷新成功，返回新的 Token 对',
                content: new OA\JsonContent(ref: '#/components/schemas/TokenResponse')
            ),
            new OA\Response(response: '401', description: 'Refresh Token 无效或已过期'),
        ]
    )]
    public function refresh(ServerRequestInterface $request): ResponseInterface
    {
        $input = (array) $request->getParsedBody();

        $payload = $this->auth->refresh($input);

        return Response::success([
            'user' => $payload['user']->only(['id', 'username', 'email', 'created_at']),
            'access_token' => $payload['access_token'],
            'refresh_token' => $payload['refresh_token'],
        ]);
    }

    #[OA\Get(
        path: '/api/v1/auth/me',
        summary: '获取当前用户信息',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: '200', description: '成功返回当前用户信息',
                content: new OA\JsonContent(ref: '#/components/schemas/User')
            ),
            new OA\Response(response: '401', description: '未认证'),
        ]
    )]
    public function me(ServerRequestInterface $request): ResponseInterface
    {
        $userId = (int) $request->getAttribute('user_id', 0);

        $user = User::query()->find($userId);
        if ($user === null) {
            throw new NotFoundException('用户不存在');
        }

        return Response::success(
            $user->only(['id', 'username', 'email', 'created_at', 'updated_at'])
        );
    }
}
