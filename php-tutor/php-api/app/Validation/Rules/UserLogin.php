<?php
declare(strict_types=1);

namespace App\Validation\Rules;

use Respect\Validation\Validator as V;

/**
 * Login rules.
 */
final class UserLogin
{
    /**
     * @return array<string, array{0: V, 1: string}>
     */
    public static function rules(): array
    {
        return [
            'email' => [
                V::email(),
                '邮箱',
            ],
            'password' => [
                V::stringType()->length(1, 64),
                '密码',
            ],
        ];
    }
}
