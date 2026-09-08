<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Entities\User;
use App\Helpers\Html;
use App\Helpers\JsonResponse;
use App\Helpers\MyLogger;
use App\Helpers\RequestBody;
use App\Middleware\ApiAuthenticate;
use App\Services\TokenService;
use Doctrine\ORM\EntityManager;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * ============================================================
 * API 认证控制器（Bearer Token）— Slim 4 + Doctrine
 * ============================================================
 *
 * 接口清单：
 *   POST /api/v1/auth/login      登录，签发 Access + Refresh Token
 *   POST /api/v1/auth/refresh    用 Refresh 换新的 Access（同时轮转 Refresh）
 *   POST /api/v1/auth/logout     登出，撤销所有 Refresh Token
 *   GET  /api/v1/auth/me         获取当前用户（需 Bearer Token）
 */
class AuthController
{
    public function __construct(
        private readonly EntityManager $em,
        private readonly TokenService  $tokens,
    ) {
    }

    /**
     * POST /api/v1/auth/login
     *
     * @summary 登录
     * @description 用登录名和密码换取 Access + Refresh Token
     * @tag Auth
     * @requestBody LoginRequest
     * @response 200 LoginResponse 登录成功
     * @response 401 账号或密码错误
     * @response 422 参数校验失败
     */
    #[OA\Post(
        path: '/auth/login',
        description: '用登录名和密码换取 Access + Refresh Token',
        summary: '登录',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['login', 'password'],
                properties: [
                    new OA\Property(property: 'login', description: '登录名（用户名或邮箱）', type: 'string', example: 'admin'),
                    new OA\Property(property: 'password', description: '密码', type: 'string', format: 'password', example: 'admin123'),
                ],
                type: 'object',
            ),
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: '登录成功',
                content: new OA\JsonContent(
                    required: ['accessToken', 'refreshToken'],
                    properties: [
                        new OA\Property(property: 'accessToken', description: 'Access Token（JWT，15 分钟有效）', type: 'string'),
                        new OA\Property(property: 'refreshToken', description: 'Refresh Token（7 天有效）', type: 'string'),
                        new OA\Property(property: 'tokenType', type: 'string', example: 'Bearer'),
                        new OA\Property(property: 'expiresIn', description: 'Access Token 剩余秒数', type: 'integer', example: 900),
                        new OA\Property(property: 'user', ref: '#/components/schemas/User'),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: 401,
                description: '账号或密码错误',
                content: new OA\JsonContent(
                    required: ['code', 'message', 'data'],
                    properties: [
                        new OA\Property(property: 'code', type: 'integer', example: 40101),
                        new OA\Property(property: 'message', type: 'string', example: '账号或密码错误'),
                        new OA\Property(property: 'data', type: 'object', nullable: true),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: 422,
                description: '参数校验失败',
                content: new OA\JsonContent(
                    required: ['code', 'message', 'data'],
                    properties: [
                        new OA\Property(property: 'code', type: 'integer', example: 42201),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(
                            property: 'data',
                            description: '字段级错误',
                            type: 'object',
                            nullable: true,
                            additionalProperties: new OA\AdditionalProperties(
                                type: 'array',
                                items: new OA\Items(type: 'string'),
                            ),
                        ),
                    ],
                    type: 'object',
                ),
            ),
        ],
    )]
    public function login(ServerRequestInterface $psr7): ResponseInterface
    {
        $body     = RequestBody::json($psr7);
        $login    = (string)($body['login'] ?? '');
        $password = (string)($body['password'] ?? '');

        if ($login === '' || $password === '') {
            return JsonResponse::validation([
                'login'    => ['登录名不能为空'],
                'password' => ['密码不能为空'],
            ]);
        }
        if (Html::charLen($login) > 128 || Html::charLen($password) > 128) {
            return JsonResponse::validation([
                'login'    => ['登录名过长'],
                'password' => ['密码过长'],
            ]);
        }

        $repo = $this->em->getRepository(User::class);
        $user = $repo->findByLogin(trim($login));

        // 防时序攻击：用户不存在时也跑一次 bcrypt，避免通过响应时间区分
        $dummyHash = '$2y$12$invalidhashforbencrypt';
        $valid = false;
        if ($user !== null) {
            $valid = $user->verifyPassword($password);
        } else {
            password_verify($password, $dummyHash);
        }

        if (!$valid) {
            MyLogger::warning('登录失败', [
                'login'  => $login,
                'remote' => $psr7->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1',
                'reason' => $user === null ? '用户不存在' : '密码错误',
            ]);
            return JsonResponse::validation([
                'login' => ['账号或密码错误'],
            ], '账号或密码错误');
        }

        $tokens = $this->tokens->issue($user);

        MyLogger::info('用户登录成功', [
            'userId'   => $user->getId(),
            'username' => $user->getUsername(),
            'remote'   => $psr7->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1',
        ]);

        return JsonResponse::ok($tokens, '登录成功');
    }

    /**
     * POST /api/v1/auth/refresh
     *
     * @summary 刷新令牌
     * @description 用 Refresh Token 换取新的 Access Token（同时轮转 Refresh Token）
     * @tag Auth
     * @requestBody RefreshRequest
     * @response 200 RefreshResponse 刷新成功
     * @response 401 Refresh Token 无效或已过期
     */
    #[OA\Post(
        path: '/auth/refresh',
        description: '用 Refresh Token 换取新的 Access Token（同时轮转 Refresh Token）',
        summary: '刷新令牌',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['refreshToken'],
                properties: [
                    new OA\Property(property: 'refreshToken', description: '刷新令牌', type: 'string'),
                ],
                type: 'object',
            ),
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: '刷新成功',
                content: new OA\JsonContent(
                    required: ['accessToken', 'refreshToken'],
                    properties: [
                        new OA\Property(property: 'accessToken', description: '新的 Access Token', type: 'string'),
                        new OA\Property(property: 'refreshToken', description: '轮转后的新 Refresh Token', type: 'string'),
                        new OA\Property(property: 'expiresIn', type: 'integer', example: 900),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: 401,
                description: 'Refresh Token 无效或已过期',
                content: new OA\JsonContent(
                    required: ['code', 'message', 'data'],
                    properties: [
                        new OA\Property(property: 'code', type: 'integer', example: 40102),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'data', type: 'object', nullable: true),
                    ],
                    type: 'object',
                ),
            ),
        ],
    )]
    public function refresh(ServerRequestInterface $psr7): ResponseInterface
    {
        $body         = RequestBody::json($psr7);
        $refreshToken = (string)($body['refreshToken'] ?? '');

        if ($refreshToken === '') {
            return JsonResponse::validation([
                'refreshToken' => ['刷新令牌不能为空'],
            ]);
        }

        try {
            $tokens = $this->tokens->refresh($refreshToken);
        } catch (\Throwable $e) {
            return JsonResponse::error(
                JsonResponse::CODE_INVALID_TOKEN,
                '刷新令牌无效或已过期，请重新登录',
                401
            );
        }

        return JsonResponse::ok($tokens, '刷新成功');
    }

    /**
     * POST /api/v1/auth/logout
     *
     * @summary 登出
     * @description 撤销当前用户的所有 Refresh Token（Access Token 等自然过期）
     * @tag Auth
     * @security
     * @response 204 登出成功（无 body）
     * @response 401 未登录
     */
    #[OA\Post(
        path: '/auth/logout',
        description: '撤销当前用户的所有 Refresh Token（Access Token 等自然过期）',
        summary: '登出',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 204,
                description: '登出成功（无 body）',
            ),
            new OA\Response(
                response: 401,
                description: '未登录',
                content: new OA\JsonContent(
                    required: ['code', 'message', 'data'],
                    properties: [
                        new OA\Property(property: 'code', type: 'integer', example: 40101),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'data', type: 'object', nullable: true),
                    ],
                    type: 'object',
                ),
            ),
        ],
    )]
    public function logout(ServerRequestInterface $psr7): ResponseInterface
    {
        $currentUser = ApiAuthenticate::currentUser();
        if ($currentUser === null) {
            return JsonResponse::error(JsonResponse::CODE_UNAUTHORIZED, '未登录', 401);
        }
        $userId      = (int)$currentUser['userId'];
        $body        = RequestBody::json($psr7);
        $singleToken = (string)($body['refreshToken'] ?? '');

        if ($singleToken !== '') {
            $this->tokens->revokeRefresh($singleToken);
        } else {
            $this->tokens->revokeAll($userId);
        }

        MyLogger::info('用户登出', [
            'userId' => $userId,
            'remote' => $psr7->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1',
        ]);

        return JsonResponse::noContent();
    }

    /**
     * GET /api/v1/auth/me
     *
     * @summary 获取当前用户
     * @description 返回当前登录用户的信息（需 Bearer Token）
     * @tag Auth
     * @security
     * @response 200 User 用户信息
     * @response 401 未登录或 token 失效
     */
    #[OA\Get(
        path: '/auth/me',
        description: '返回当前登录用户的信息（需 Bearer Token）',
        summary: '获取当前用户',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: '用户信息',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/User',
                ),
            ),
            new OA\Response(
                response: 401,
                description: '未登录或 token 失效',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                ),
            ),
        ],
    )]
    public function me(ServerRequestInterface $psr7): ResponseInterface
    {
        $currentUser = ApiAuthenticate::currentUser();
        if ($currentUser === null) {
            return JsonResponse::error(JsonResponse::CODE_UNAUTHORIZED, '未登录', 401);
        }
        $userId = (int)$currentUser['userId'];
        $user   = $this->em->find(User::class, $userId);

        if ($user === null) {
            return JsonResponse::error(40103, '用户不存在或已被删除', 401);
        }

        $createdAt = $user->getCreatedAt()?->format('Y-m-d H:i:s') ?? '';

        return JsonResponse::ok([
            'id'        => $user->getId(),
            'username'  => $user->getUsername(),
            'email'     => $user->getEmail(),
            'role'      => $user->getRole(),
            'createdAt' => $createdAt,
        ]);
    }
}
