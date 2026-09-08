<?php
declare(strict_types=1);

namespace App\Validation\Rules;

use Respect\Validation\Validator as V;

/**
 * Article create/update rules.
 */
final class ArticleCreate
{
    /**
     * @return array<string, array{0: V, 1: string}>
     */
    public static function rules(): array
    {
        return [
            'title' => [
                V::stringType()->length(1, 200),
                '标题',
            ],
            'body' => [
                V::stringType()->length(1, 65535),
                '正文',
            ],
        ];
    }
}
