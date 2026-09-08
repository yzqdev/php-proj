<?php
declare(strict_types=1);

namespace App\Middleware;

use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Assigns a request-id (from the client's X-Request-Id or generated),
 * exposes it as a request attribute AND resets the Monolog UidProcessor
 * to it — so every log record of this request carries the same id
 * (hard constraint 7 / DoD 5).
 */
final class RequestIdMiddleware implements MiddlewareInterface
{
    /** Shared with the ExceptionRenderer so error envelopes carry the id. */
    public static string $currentRequestId = '';

    public function __construct(private readonly Logger $logger)
    {
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $requestId = $request->getHeaderLine('X-Request-Id');
        if ($requestId === '') {
            $requestId = bin2hex(random_bytes(8));
        }
        self::$currentRequestId = $requestId;

        // Reset UidProcessor uid to our request-id so log lines correlate.
        foreach ($this->logger->getProcessors() as $processor) {
            if ($processor instanceof \Monolog\Processor\UidProcessor) {
                $requestId = substr($requestId, 0, 32); // UidProcessor limit
                $processor->reset();
                $prop = new \ReflectionProperty($processor, 'uid');
                $prop->setValue($processor, $requestId);
            }
        }

        $request = $request->withAttribute('request_id', $requestId);

        try {
            $this->logger->info('request.start', [
                'method' => $request->getMethod(),
                'uri'    => (string) $request->getUri(),
            ]);

            $response = $handler->handle($request);

            $this->logger->info('request.end', ['status' => $response->getStatusCode()]);
        } catch (\Throwable $e) {
            // ErrorMiddleware will convert this into the JSON envelope;
            // make sure the failure itself is logged with the request-id.
            $this->logger->error('request.error', [
                'exception' => $e::class,
                'message'   => $e->getMessage(),
            ]);
            throw $e;
        }

        return $response
            ->withHeader('X-Request-Id', $requestId);
    }
}
