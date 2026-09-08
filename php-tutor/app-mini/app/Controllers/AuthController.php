<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Http\ApiResponder;
use App\Services\AuthService;
use App\Validation\Rules;
use App\Validation\Validator;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * 认证接口:注册、登录、当前用户。
 */
final class AuthController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly Validator $validator,
    ) {
    }

    #[OA\Post(path: '/api/v1/auth/register', tags: ['Auth'], summary: '注册新用户')]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/RegisterRequest'))]
    #[OA\Response(response: 201, description: '注册成功', content: new OA\JsonContent(ref: '#/components/schemas/UserPublic'))]
    #[OA\Response(response: 422, description: '参数校验失败', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function register(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $this->validator->validate((array) $request->getParsedBody(), Rules::register());
        $user = $this->authService->register($data);

        return ApiResponder::success($response, $user, 201);
    }

    #[OA\Post(path: '/api/v1/auth/login', tags: ['Auth'], summary: '登录获取 access token')]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/LoginRequest'))]
    #[OA\Response(response: 200, description: '登录成功', content: new OA\JsonContent(ref: '#/components/schemas/LoginResponse'))]
    #[OA\Response(response: 401, description: '凭据错误', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    #[OA\Response(response: 422, description: '参数校验失败', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $this->validator->validate((array) $request->getParsedBody(), Rules::login());
        $result = $this->authService->login($data['email'], $data['password']);

        return ApiResponder::success($response, [
            'token' => $result['token'],
            'user' => $result['user'],
        ]);
    }

    #[OA\Get(path: '/api/v1/auth/me', tags: ['Auth'], summary: '获取当前登录用户', security: [['bearerAuth' => []]])]
    #[OA\Response(response: 200, description: '当前用户', content: new OA\JsonContent(ref: '#/components/schemas/UserPublic'))]
    #[OA\Response(response: 401, description: '未认证', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function me(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user = $this->authService->me((int) $request->getAttribute('userId'));

        return ApiResponder::success($response, $user);
    }
}
