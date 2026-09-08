<?php
declare(strict_types=1);

namespace App\Support;

class ForbiddenException extends AppException
{
    public function __construct(string $message = 'Forbidden')
    {
        parent::__construct(403, 403, $message);
    }
}