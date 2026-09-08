<?php
declare(strict_types=1);

namespace App\Validation;

use App\Support\AppException;

/**
 * Thrown when input validation fails; carries field-level Chinese messages.
 */
final class ValidationFailedException extends AppException
{
    /**
     * @param array<string, string[]> $errors field => messages
     */
    public function __construct(
        private readonly array $errors,
        string $message = '参数校验失败'
    ) {
        parent::__construct(422, 422, $message);
    }

    /** @return array<string, string[]> */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
