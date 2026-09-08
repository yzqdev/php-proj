<?php
declare(strict_types=1);

namespace App\Support;

use Throwable;

class UnauthenticatedException extends AppException
{
    public function __construct(string $message = 'Authentication required', ?Throwable $previous = null)
    {
        parent::__construct(401, 401, $message, $previous);
    }
}