<?php
declare(strict_types=1);

namespace App\Validation\Rules;

use Respect\Validation\Validator as V;

/**
 * Registration rules. Each rule is paired with a Chinese field name so
 * validation errors read naturally (hard constraint 4).
 */
final class UserRegistration
{
    /**
     * @return array<string, array{0: V, 1: string}>
     */
    public static function rules(): array
    {
        return [
            'username' => [
                V::stringType()->length(3, 50)->alnum('_')->noWhitespace(),
                '用户名',
            ],
            'email' => [
                V::email(),
                '邮箱',
            ],
            'password' => [
                V::stringType()->length(8, 64),
                '密码',
            ],
        ];
    }
}
