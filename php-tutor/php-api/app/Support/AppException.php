<?php
declare(strict_types=1);

namespace App\Support;

use Throwable;

/**
 * Application-level exception with an HTTP status code and error code.
 */
class AppException extends \RuntimeException
{
    public function __construct(
        private readonly int $httpStatus = 400,
        private readonly int $errorCode = 500,
        string $message = '',
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }
}