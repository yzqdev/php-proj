<?php

declare(strict_types=1);

namespace Yzqde\DouyinSpider\Service;

use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Log\LoggerInterface;
use Yzqde\DouyinSpider\DTO\UserLoginDto;
use Yzqde\DouyinSpider\DTO\UserRegisterDto;
use Yzqde\DouyinSpider\Exception\AuthenticationException;
use Yzqde\DouyinSpider\Model\User;
use Yzqde\DouyinSpider\Repository\UserRepository;

use function env;

/**
 * 认证服务
 *
 * @phpstan-type TokenResponse array{token: string, refresh_token: string, expires_in: int}
 */
class AuthService
{
    private readonly string $jwtSecret;
    private readonly int $jwtTtl;
    private readonly int $jwtRefreshTtl;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $userRepository,
        private readonly LoggerInterface $logger,
    ) {
        $secret = env('JWT_SECRET');
        if ($secret === false || $secret === '') {
            throw new \RuntimeException('JWT_SECRET 环境变量未设置');
        }
        $this->jwtSecret = $secret;
        $this->jwtTtl = (int) (env('JWT_TTL') ?: 60);
        $this->jwtRefreshTtl = (int) (env('JWT_REFRESH_TTL') ?: 10080);
    }

    /**
     * 注册新用户
     */
    public function register(UserRegisterDto $dto): User
    {
        if ($this->userRepository->findByUsername($dto->username) !== null) {
            throw new \InvalidArgumentException('用户名已存在');
        }
        if ($this->userRepository->findByEmail($dto->email) !== null) {
            throw new \InvalidArgumentException('邮箱已被注册');
        }

        $user = new User();
        $user->setUsername($dto->username);
        $user->setEmail($dto->email);
        $user->setPassword(password_hash($dto->password, PASSWORD_BCRYPT));

        $this->em->persist($user);
        $this->em->flush();

        $this->logger->info('用户注册成功', ['userId' => $user->getId(), 'username' => $user->getUsername()]);

        return $user;
    }

    /**
     * 登录并签发 Token
     *
     * @return array{token: string, refresh_token: string, expires_in: int}
     */
    public function login(UserLoginDto $dto): array
    {
        $user = $this->userRepository->findByUsername($dto->username);
        if (!$user || !password_verify($dto->password, $user->getPassword())) {
            throw new AuthenticationException('用户名或密码错误');
        }

        $token = $this->createToken($user);
        $refreshToken = $this->createRefreshToken($user);

        $this->logger->info('用户登录成功', ['userId' => $user->getId(), 'username' => $user->getUsername()]);

        return [
            'token' => $token,
            'refresh_token' => $refreshToken,
            'expires_in' => $this->jwtTtl * 60,
        ];
    }

    /**
     * 刷新 Token
     *
     * @return array{token: string, refresh_token: string, expires_in: int}
     */
    public function refreshToken(string $refreshToken): array
    {
        try {
            $decoded = JWT::decode($refreshToken, new Key($this->jwtSecret, 'HS256'));
        } catch (\Throwable $e) {
            throw new AuthenticationException('无效的刷新令牌');
        }

        // 校验 token 类型，防止 access token 被当作 refresh token 使用
        if (!isset($decoded->type) || $decoded->type !== 'refresh') {
            throw new AuthenticationException('无效的刷新令牌');
        }

        $user = $this->userRepository->find((int) $decoded->sub);
        if (!$user) {
            throw new AuthenticationException('用户不存在');
        }

        return [
            'token' => $this->createToken($user),
            'refresh_token' => $this->createRefreshToken($user),
            'expires_in' => $this->jwtTtl * 60,
        ];
    }

    /**
     * 验证并解码 Token，返回用户实体
     */
    public function validateToken(string $token): ?User
    {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            $userId = (int) $decoded->sub;
            return $this->userRepository->find($userId) ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function createToken(User $user): string
    {
        $now = time();
        $payload = [
            'iss' => env('APP_URL') ?: 'localhost',
            'sub' => $user->getId(),
            'iat' => $now,
            'exp' => $now + $this->jwtTtl * 60,
            'role' => $user->getRole(),
            'username' => $user->getUsername(),
        ];

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    private function createRefreshToken(User $user): string
    {
        $now = time();
        $payload = [
            'iss' => env('APP_URL') ?: 'localhost',
            'sub' => $user->getId(),
            'iat' => $now,
            'exp' => $now + $this->jwtRefreshTtl * 60,
            'type' => 'refresh',
        ];

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }
}
