<?php
declare(strict_types=1);

namespace App\Support;

use Throwable;

class NotFoundException extends AppException
{
    public function __construct(string $message = 'Not found')
    {
        parent::__construct(404, 404, $message);
    }

    /**
     * 401 for invalid/expired/forged tokens. Kept here because the
     * renderer maps AppException by status, and token problems must
     * surface as Unauthorized rather than Not Found.
     */
    public static function invalidToken(?Throwable $previous = null): UnauthenticatedException
    {
        return new UnauthenticatedException('Token 无效或已过期', $previous);
    }
}