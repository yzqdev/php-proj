<?php
declare(strict_types=1);

namespace App\Support;

use App\Validation\ValidationFailedException;
use Throwable;

/**
 * Slim 4 error renderer: returns the JSON body string.
 *
 * Uniform envelope: { code, message, errors, request_id }
 */
final class ExceptionRenderer
{
    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
        if ($exception instanceof ValidationFailedException) {
            $code    = Response::CODE_VALIDATION;
            $message = $exception->getMessage() ?: '参数校验失败';
            $errors  = $exception->getErrors();
        } elseif ($exception instanceof AppException) {
            $code    = $exception->getErrorCode();
            $message = $exception->getMessage() ?: 'Error';
            $errors  = null;
        } else {
            $code    = Response::CODE_SERVER_ERROR;
            $message = $displayErrorDetails
                ? ($exception->getMessage() ?: $exception::class)
                : 'Internal Server Error';
            $errors  = $displayErrorDetails
                ? ['exception' => $exception::class, 'file' => $exception->getFile(), 'line' => $exception->getLine()]
                : null;
        }

        $payload = [
            'code'       => $code,
            'message'    => $message,
            'request_id' => \App\Middleware\RequestIdMiddleware::$currentRequestId ?: null,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return json_encode(
            $payload,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }
}
