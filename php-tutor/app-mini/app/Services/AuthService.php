<?php
declare(strict_types=1);

namespace App\Services;

use App\Exceptions\UnauthorizedException;
use App\Exceptions\ValidationException;
use App\Models\User;
use App\Security\JwtService;

/**
 * 认证业务:注册、登录、当前用户。Controller 只负责调用本类。
 */
final class AuthService
{
    public function __construct(private readonly JwtService $jwt)
    {
    }

    /**
     * 注册新用户。
     *
     * @param array{name: string, email: string, password: string} $data 已校验数据
     *
     * @throws ValidationException 邮箱已存在
     */
    public function register(array $data): User
    {
        $email = strtolower(trim($data['email']));
        if (User::query()->where('email', $email)->exists()) {
            throw new ValidationException('邮箱已被注册', ['email' => ['该邮箱已被注册']]);
        }

        return User::create([
            'name' => trim($data['name']),
            'email' => $email,
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
        ]);
    }

    /**
     * 校验凭据并签发 token。
     *
     * @return array{token: string, user: User}
     *
     * @throws UnauthorizedException 凭据错误
     */
    public function login(string $email, string $password): array
    {
        $user = User::query()->where('email', strtolower(trim($email)))->first();
        if ($user === null || !password_verify($password, $user->password)) {
            throw new UnauthorizedException('邮箱或密码错误');
        }

        return [
            'token' => $this->jwt->issue((int) $user->id),
            'user' => $user,
        ];
    }

    /**
     * @throws UnauthorizedException 用户已不存在
     */
    public function me(int $userId): User
    {
        $user = User::query()->find($userId);
        if ($user === null) {
            throw new UnauthorizedException('用户已不存在');
        }

        return $user;
    }
}
