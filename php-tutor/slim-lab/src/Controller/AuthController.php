<?php

declare(strict_types=1);

namespace App\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use App\Exception\ApiException;
use App\Auth\AuthService;
use App\Auth\TokenStore;
use App\Auth\UserStore;

/**
 * 认证模块控制器：注册 / 登录 / 登出 / 当前用户信息。
 *
 * 响应信封与全站一致 {code, message, data}：
 *   POST /api/auth/register  {username, email?, password} → data=null
 *   POST /api/auth/login     {username, password}         → data={token, user{...}}
 *   POST /api/auth/logout    （需 Bearer token）           → data=null
 *   GET  /api/auth/userInfo  （需 Bearer token）           → data={user{...}}
 *
 * 登录支持两类账号：JSON 注册用户（storage/users.json）与
 * MySQL 演示用户（users 表，name/email 均可作账号，须已设置密码）。
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

    public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $token = $this->requireToken($request);
        $this->tokens->revoke($token);

        return ResponseFactory::ok($response, null, '已退出登录');
    }

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
