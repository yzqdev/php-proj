<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\ApiException;
use App\Service\AuthService;
use App\Service\TokenStore;
use App\Service\UserStore;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * 认证模块控制器：注册 / 登录 / 登出 / 当前用户信息。
 */


final class AuthController
{
    /** 用户名规则：3-20 位，字母/数字/下划线 */
    private const USERNAME_RE = '/^[A-Za-z0-9_]{3,20}$/';

    public function __construct(
        private readonly UserStore $users,
        private readonly AuthService $auth,
        private readonly TokenStore $tokens,
        private readonly LoggerInterface $logger,
    ) {
    }
    #[OA\Get(
        path: '/api/auth/register',
        tags: ['Auth'],
        summary: '注册账号',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['username', 'password'],
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'demo_user'),
                    new OA\Property(property: 'password', type: 'string', example: 'secret123'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'demo@test.com'),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: '200', description: '注册成功'),
            new OA\Response(response: '400', description: '参数错误'),
            new OA\Response(response: '429', description: '请求过于频繁'),
            new OA\Response(response: '500', description: '服务器错误'),
        ]
    )]
    public function register(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = (array) $request->getParsedBody();
        $username = trim((string) ($body['username'] ?? ''));
        $password = (string) ($body['password'] ?? '');
        // 邮箱可选（约定仅 username/password）；填写时校验格式与唯一性
        $email = trim((string) ($body['email'] ?? ''));

        if ($username === '' || $password === '') {
            throw new ApiException('账号与密码不能为空', 400);
        }
        if (preg_match(self::USERNAME_RE, $username) !== 1) {
            throw new ApiException('用户名须为 3-20 位字母、数字或下划线', 400);
        }
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new ApiException('邮箱格式不正确', 400);
        }
        if (strlen($password) < 6 || strlen($password) > 64) {
            throw new ApiException('密码长度须为 6-64 位', 400);
        }

        $this->users->create($username, $password, $email);
        $this->logger->info('auth register', ['username' => $username]);

        return ResponseFactory::ok($response, null, '注册成功，请登录');
    }

    #[OA\Get(
        path: '/api/auth/login',
        tags: ['Auth'],
        summary: '登录获取令牌',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['username', 'password'],
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'demo_user'),
                    new OA\Property(property: 'password', type: 'string', example: 'secret123'),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: '200', description: '登录成功', content: new OA\JsonContent(ref: '#/components/schemas/AuthTokenData')),
            new OA\Response(response: '400', description: '参数错误'),
            new OA\Response(response: '429', description: '请求过于频繁'),
            new OA\Response(response: '500', description: '服务器错误'),
        ]
    )]

    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = (array) $request->getParsedBody();
        $identifier = trim((string) ($body['username'] ?? ''));
        $password = (string) ($body['password'] ?? '');

        if ($identifier === '' || $password === '') {
            throw new ApiException('账号与密码不能为空', 400);
        }

        // 统一双源认证（JSON 用户 / MySQL 演示用户）；失败抛 400，统一提示防账号枚举
        $result = $this->auth->authenticate($identifier, $password);
        $token = $this->tokens->issue($result['subject']);
        $this->logger->info('auth login', ['username' => $identifier]);

        return ResponseFactory::ok($response, [
            'token' => $token,
            'user' => $result['user'],
        ]);
    }
    #[OA\Get(
        path: '/api/auth/logout',
        tags: ['Auth'],
        summary: '退出登录',
        security: [['BearerAuth' => []]],
        responses: [
            new OA\Response(response: '200', description: '已退出登录'),
            new OA\Response(response: '401', description: '未登录'),
            new OA\Response(response: '500', description: '服务器错误'),
        ]
    )]
    public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $token = $this->requireToken($request);
        $this->tokens->revoke($token);

        return ResponseFactory::ok($response, null, '已退出登录');
    }
    #[OA\Get(
        path: '/api/auth/userInfo',
        tags: ['Auth'],
        summary: '获取当前用户信息',
        security: [['BearerAuth' => []]],
        responses: [
            new OA\Response(response: '200', description: '成功', content: new OA\JsonContent(ref: '#/components/schemas/AuthUserWrap')),
            new OA\Response(response: '401', description: '未登录'),
            new OA\Response(response: '500', description: '服务器错误'),
        ]
    )]
    public function userInfo(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $subject = $this->tokens->resolve($this->requireToken($request));
        if ($subject === null) {
            throw new ApiException('登录状态已失效，请重新登录', 401);
        }

        return ResponseFactory::ok($response, ['user' => $this->auth->userBySubject($subject)]);
    }

    /**
     * 从 Authorization: Bearer 头提取 token；缺失即 401。
     */
    private function requireToken(ServerRequestInterface $request): string
    {
        $header = trim((string) $request->getHeaderLine('Authorization'));
        if (preg_match('/^Bearer\s+(\S+)$/i', $header, $m) === 1) {
            return $m[1];
        }
        throw new ApiException('未登录', 401);
    }
}
