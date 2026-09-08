<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Support\AppException;
use App\Validation\Validator as InputValidator;
use App\Validation\Rules\UserRegistration;
use App\Validation\Rules\UserLogin;
use App\Validation\Rules\TokenRefresh;
use App\Support\UnauthenticatedException;
use Respect\Validation\Exceptions\NestedValidationException;

/**
 * Registration / login / refresh business logic.
 */
final class AuthService
{
    public function __construct(private readonly TokenService $tokens)
    {
    }

    /**
     * @return array{user:User, access_token:array, refresh_token:array}
     */
    public function register(array $input): array
    {
        $data = InputValidator::validate($input, UserRegistration::rules());

        if (User::query()->where('email', $data['email'])->exists()) {
            throw new AppException(422, 4001, '该邮箱已被注册');
        }
        if (User::query()->where('username', $data['username'])->exists()) {
            throw new AppException(422, 4002, '该用户名已被占用');
        }

        $user = User::query()->create([
            'username' => $data['username'],
            'email'    => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
        ]);

        return $this->buildAuthPayload($user);
    }

    /**
     * @return array{user:User, access_token:array, refresh_token:array}
     */
    public function login(array $input): array
    {
        $data = InputValidator::validate($input, UserLogin::rules());

        $user = User::query()->where('email', $data['email'])->first();

        if ($user === null || !password_verify($data['password'], $user->password)) {
            throw new UnauthenticatedException('邮箱或密码错误');
        }

        return $this->buildAuthPayload($user);
    }

    /**
     * Exchange a valid refresh token for a new token pair.
     *
     * @return array{user:User, access_token:array, refresh_token:array}
     */
    public function refresh(array $input): array
    {
        $data = InputValidator::validate($input, TokenRefresh::rules());

        $userId = $this->tokens->getUserIdFromRefreshToken($data['refresh_token']);

        $user = User::query()->find($userId);
        if ($user === null) {
            throw new UnauthenticatedException('用户不存在');
        }

        return $this->buildAuthPayload($user);
    }

    /**
     * @return array{user:User, access_token:array, refresh_token:array}
     */
    private function buildAuthPayload(User $user): array
    {
        return [
            'user'          => $user,
            'access_token'  => $this->tokens->issue($user->id, TokenService::TYPE_ACCESS),
            'refresh_token' => $this->tokens->issue($user->id, TokenService::TYPE_REFRESH),
        ];
    }
}
