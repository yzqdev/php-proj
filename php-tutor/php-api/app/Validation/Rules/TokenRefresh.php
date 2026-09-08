<?php
declare(strict_types=1);

namespace App\Validation\Rules;

use Respect\Validation\Validator as V;

/**
 * Refresh-token request rules.
 */
final class TokenRefresh
{
    /**
     * @return array<string, array{0: V, 1: string}>
     */
    public static function rules(): array
    {
        return [
            'refresh_token' => [
                V::stringType()->length(20, 4096),
                '刷新令牌',
            ],
        ];
    }
}
