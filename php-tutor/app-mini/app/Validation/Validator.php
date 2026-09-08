<?php
declare(strict_types=1);

namespace App\Validation;

use App\Exceptions\ValidationException;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validatable;

/**
 * Respect/Validation 封装:按字段规则校验输入,失败抛出 ValidationException(422)。
 */
final class Validator
{
    /**
     * @param array<string, mixed>       $data  待校验输入(请求体或查询参数)
     * @param array<string, Validatable> $rules 字段 => Respect 规则
     *
     * @return array<string, mixed> 校验通过的数据(保留原始值)
     *
     * @throws ValidationException
     */
    public function validate(array $data, array $rules): array
    {
        $validated = [];
        $errors = [];

        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            try {
                $rule->assert($value);
                $validated[$field] = $value;
            } catch (NestedValidationException $exception) {
                $errors[$field] = array_values($exception->getMessages());
            }
        }

        if ($errors !== []) {
            throw new ValidationException('参数校验失败', $errors);
        }

        return $validated;
    }
}
