<?php
declare(strict_types=1);

namespace App\Support;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response as SlimResponse;
use Slim\Psr7\Headers;
use Psr\Http\Message\StreamInterface;

/**
 * Unified JSON response envelope.
 *
 * Every API response follows this shape:
 *   success: { "data": ..., "request_id": "..." }
 *   error:   { "code": 4xx/5xx, "message": "...", "errors": {...}, "request_id": "..." }
 */
final class Response
{
    public const CODE_SUCCESS = 0;
    public const CODE_VALIDATION = 422;
    public const CODE_UNAUTHORIZED = 401;
    public const CODE_FORBIDDEN = 403;
    public const CODE_NOT_FOUND = 404;
    public const CODE_SERVER_ERROR = 500;

    private SlimResponse $response;

    public function __construct(?ResponseInterface $response = null)
    {
        $this->response = $response ?? new SlimResponse();
    }

    public static function json(mixed $data, int $status = 200): ResponseInterface
    {
        $body = json_encode([
            'data' => $data,
            'request_id' => $_SERVER['HTTP_X_REQUEST_ID'] ?? null,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $response = (new SlimResponse())
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($status);

        $response->getBody()->write($body);
        return $response;
    }

    public static function success(mixed $data, int $status = 200): ResponseInterface
    {
        return self::json($data, $status);
    }

    public static function error(
        int $code,
        string $message,
        mixed $errors = null,
        int $status = 200
    ): ResponseInterface {
        $payload = [
            'code' => $code,
            'message' => $message,
            'request_id' => $_SERVER['HTTP_X_REQUEST_ID'] ?? null,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        $body = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $response = (new SlimResponse())
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($status);

        $response->getBody()->write($body);
        return $response;
    }

    public static function validation(array $errors, string $message = 'Validation failed'): ResponseInterface
    {
        return self::error(
            self::CODE_VALIDATION,
            $message,
            $errors,
            422
        );
    }

    public static function unauthorized(string $message = 'Unauthorized'): ResponseInterface
    {
        return self::error(self::CODE_UNAUTHORIZED, $message, null, 401);
    }

    public static function forbidden(string $message = 'Forbidden'): ResponseInterface
    {
        return self::error(self::CODE_FORBIDDEN, $message, null, 403);
    }

    public static function notFound(string $message = 'Not Found'): ResponseInterface
    {
        return self::error(self::CODE_NOT_FOUND, $message, null, 404);
    }

    public static function serverError(string $message = 'Internal Server Error'): ResponseInterface
    {
        return self::error(self::CODE_SERVER_ERROR, $message, null, 500);
    }
}