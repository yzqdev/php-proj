<?php
declare(strict_types=1);

namespace App\Support;

use Slim\Handlers\ErrorHandler as SlimErrorHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * Application error handler (wired into ErrorMiddleware).
 *
 * - Forces application/json for every error response.
 * - Maps AppException::getHttpStatus() to the HTTP status
 *   (Slim only understands HttpException codes by default).
 * - Uses ExceptionRenderer for the uniform envelope body.
 */
final class ErrorHandler extends SlimErrorHandler
{
    private bool $debug;

    public function __construct(
        $callableResolver,
        $responseFactory,
        bool $debug = false,
        $logger = null
    ) {
        parent::__construct($callableResolver, $responseFactory, $logger);
        $this->debug = $debug;

        // Force JSON regardless of the client's Accept header.
        $this->forceContentType('application/json');
        $this->registerErrorRenderer('application/json', ExceptionRenderer::class);
        $this->setDefaultErrorRenderer('application/json', ExceptionRenderer::class);
    }

    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        // Slim's displayErrorDetails flag; renderer also sees it.
        return parent::__invoke(
            $request,
            $exception,
            $this->debug,
            $logErrors,
            $logErrorDetails
        );
    }

    protected function determineStatusCode(): int
    {
        if ($this->method === 'OPTIONS') {
            return 200;
        }

        if ($this->exception instanceof AppException) {
            return $this->exception->getHttpStatus();
        }

        return parent::determineStatusCode();
    }
}
