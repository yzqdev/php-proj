<?php
declare(strict_types=1);

namespace App\Validation;

use Respect\Validation\Validatable;
use Respect\Validation\Validator as v;

/**
 * 各接口的校验规则集,集中维护便于复用与一致性。
 */
final class Rules
{
    /** @return array<string, Validatable> */
    public static function register(): array
    {
        return [
            'name' => v::stringType()->notEmpty()->length(2, 100),
            'email' => v::stringType()->notEmpty()->email()->length(5, 190),
            'password' => v::stringType()->notEmpty()->length(8, 72),
        ];
    }

    /** @return array<string, Validatable> */
    public static function login(): array
    {
        return [
            'email' => v::stringType()->notEmpty()->email()->length(5, 190),
            'password' => v::stringType()->notEmpty(),
        ];
    }

    /** @return array<string, Validatable> */
    public static function postCreate(): array
    {
        return [
            'title' => v::stringType()->notEmpty()->length(1, 190),
            'content' => v::stringType()->notEmpty(),
            'status' => v::optional(v::in(['draft', 'published'])),
        ];
    }

    /** @return array<string, Validatable> */
    public static function postUpdate(): array
    {
        return [
            'title' => v::optional(v::stringType()->notEmpty()->length(1, 190)),
            'content' => v::optional(v::stringType()->notEmpty()),
            'status' => v::optional(v::in(['draft', 'published'])),
        ];
    }

    /** @return array<string, Validatable> */
    public static function postListQuery(): array
    {
        return [
            'page' => v::optional(v::intVal()->min(1)),
            'per_page' => v::optional(v::intVal()->between(1, 100)),
            'author_id' => v::optional(v::intVal()->min(1)),
            'q' => v::optional(v::stringType()->length(1, 100)),
        ];
    }
}
