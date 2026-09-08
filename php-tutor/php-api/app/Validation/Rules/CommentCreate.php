<?php
declare(strict_types=1);

namespace App\Validation\Rules;

use Respect\Validation\Validator as V;

/**
 * Comment create rules.
 */
final class CommentCreate
{
    /**
     * @return array<string, array{0: V, 1: string}>
     */
    public static function rules(): array
    {
        return [
            'content' => [
                V::stringType()->length(1, 65535),
                '评论内容',
            ],
        ];
    }
}
