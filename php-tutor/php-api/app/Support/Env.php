<?php
declare(strict_types=1);

namespace App\Support;

/**
 * Environment accessor. dotenv loads in immutable mode (hard constraint 8),
 * all config reads go through this class — no scattered getenv() in
 * business code.
 *
 * Loaded via Composer autoload; no manual require needed.
 */
final class Env
{
    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        return $default;
    }
}
