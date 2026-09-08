<?php
declare(strict_types=1);

namespace App\Validation;

use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator as V;

/**
 * Wraps Respect\Validation and converts NestedValidationException into a
 * field => message array with Chinese field names (hard constraint 4).
 */
final class Validator
{
    /**
     * Validate input against a rule map: [field => [Validator, '中文名']].
     *
     * @param array<string, array{0: V, 1: string}> $rules
     *
     * @return array<string, mixed> The validated input.
     *
     * @throws ValidationFailedException When any field fails.
     */
    public static function validate(array $input, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => [$rule, $chineseName]) {
            $value = $input[$field] ?? null;

            try {
                $rule->setName($chineseName)->assert($value);
            } catch (NestedValidationException $e) {
                // Field-level messages via getMessages() (hard constraint 4).
                $messages = array_values($e->getMessages());
                $errors[$field] = $messages;
            }
        }

        if ($errors !== []) {
            throw new ValidationFailedException($errors);
        }

        return $input;
    }

    /**
     * Convenience: pick only the wanted keys from the request payload.
     */
    public static function pick(array $input, array $keys): array
    {
        $out = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $input)) {
                $out[$key] = $input[$key];
            }
        }
        return $out;
    }
}
