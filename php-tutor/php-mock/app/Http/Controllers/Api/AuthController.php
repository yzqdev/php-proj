<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * 认证接口：注册 / 登录，签发 Sanctum token
 *
 * Sanctum token 类比 JWT：无状态 bearer token，前端放 Authorization: Bearer <token>
 */
class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => (string) $request->input('name'),
            'email' => (string) $request->input('email'),
            'password' => (string) $request->input('password'), // User 模型 cast 中自动 password_hash
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return ApiResponse::success(['token' => $token], status: 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', (string) $request->input('email'))->first();

        // 登录失败统一提示，不区分账号是否存在（防用户枚举攻击）
        if ($user === null || ! Auth::validate($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['账号或密码错误'],
            ]);
        }

        return ApiResponse::success(['token' => $user->createToken('api')->plainTextToken]);
    }
}
