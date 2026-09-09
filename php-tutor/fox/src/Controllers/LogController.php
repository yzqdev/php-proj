<?php

declare(strict_types=1);

namespace Yzqde\Fox\Controllers;

use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yzqde\Fox\Services\Logger;
use Yzqde\Fox\Services\LogReader;
use Yzqde\Fox\Support\BaseResponse;

#[OA\Schema(
    schema: 'LogEntry',
    properties: [
        new OA\Property(property: 'datetime', type: 'string', example: '2026-09-08 18:15:17', nullable: true),
        new OA\Property(property: 'level', type: 'string', example: 'INFO', nullable: true),
        new OA\Property(property: 'message', type: 'string', example: 'Request', nullable: true),
        new OA\Property(property: 'context', nullable: true),
        new OA\Property(property: 'extra', nullable: true),
        new OA\Property(property: 'raw', type: 'string', description: 'The untouched log line'),
    ]
)]
#[OA\Schema(
    schema: 'LogDay',
    properties: [
        new OA\Property(property: 'date', type: 'string', example: '2026-09-08'),
        new OA\Property(property: 'file', type: 'string', example: 'app-2026-09-08.log'),
        new OA\Property(property: 'size', type: 'integer'),
        new OA\Property(property: 'entries', type: 'integer'),
        new OA\Property(property: 'first', type: 'string', nullable: true),
        new OA\Property(property: 'last', type: 'string', nullable: true),
        new OA\Property(property: 'levels', type: 'object', additionalProperties: new OA\AdditionalProperties(type: 'integer')),
    ]
)]
class LogController
{
    private LogReader $reader;

    public function __construct(private Logger $logger)
    {
        $this->reader = new LogReader();
    }

    #[OA\Get(
        path: '/api/logs',
        description: 'One entry per rotated log file, newest first, with size, entry count and a level breakdown.',
        summary: 'List available log days',

        tags: ['Logs'],
        responses: [
            new OA\Response(response: '200', description: 'Success', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse'))
        ]
    )]
    public function index(Request $request, Response $response): Response
    {
        $days = array_values($this->reader->days());

        $this->logger->info('Log days listed', ['days' => count($days)]);

        return BaseResponse::list($response, $days, count($days));
    }

    #[OA\Get(

        path: '/api/logs/{date}',
        description: 'Parses the rotated log file for that date. Pass raw=1 to get the untouched file content as text.',
        summary: 'Get the log entries of a day',
        tags: ['Logs'],
        parameters: [
            new OA\Parameter(name: 'date', description: 'Day in Y-m-d format', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: '2026-09-08')),
            new OA\Parameter(name: 'level', description: 'Only entries of this level', in: 'query', schema: new OA\Schema(type: 'string', enum: LogReader::LEVELS)),
            new OA\Parameter(name: 'keyword', description: 'Only entries whose raw line contains this text', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer', default: 500, minimum: 1, maximum: 5000)),
            new OA\Parameter(name: 'offset', in: 'query', schema: new OA\Schema(type: 'integer', default: 0, minimum: 0)),
            new OA\Parameter(name: 'raw', description: '1 = return the raw file content as text/plain', in: 'query', schema: new OA\Schema(type: 'integer', default: 0)),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Success - Returns JSON enveloped list or raw plain text based on raw query param',
                content: [
                    // 1. 默认返回 JSON 结构
                    new OA\MediaType(
                        mediaType: 'application/json',
                        schema: new OA\Schema(ref: '#/components/schemas/ApiResponse')
                    ),
                    // 2. 当 raw=1 时返回 text/plain 文本
                    new OA\MediaType(
                        mediaType: 'text/plain',
                        schema: new OA\Schema(type: 'string', description: 'Raw log content as text/plain')
                    ),
                ]
            ), new OA\Response(response: '400', description: 'Invalid date, level, limit or offset', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: '404', description: 'No log file for that date', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse'))
        ]
    )]
    public function show(Request $request, Response $response, array $args): Response
    {
        $date = (string)($args['date'] ?? '');

        if (!$this->reader->isValidDate($date)) {
            return BaseResponse::error($response, 'Invalid date, expected Y-m-d');
        }

        if (!$this->reader->exists($date)) {
            return BaseResponse::error($response, "No log file for $date", BaseResponse::NOT_FOUND);
        }

        $params = $request->getQueryParams();

        if ($this->truthy($params['raw'] ?? null)) {
            // A file dump is not a JSON API payload, so it skips the envelope.
            $response->getBody()->write($this->reader->raw($date) ?? '');
            return $response->withHeader('Content-Type', 'text/plain; charset=UTF-8');
        }

        $level = isset($params['level']) && $params['level'] !== '' ? strtoupper((string)$params['level']) : null;
        if ($level !== null && !in_array($level, LogReader::LEVELS, true)) {
            return BaseResponse::error($response, 'Invalid level, expected one of: ' . implode(', ', LogReader::LEVELS));
        }

        $keyword = isset($params['keyword']) && $params['keyword'] !== '' ? (string)$params['keyword'] : null;
        $limit = $this->reader->normalizeLimit((int)($params['limit'] ?? 500));
        $offset = $this->reader->normalizeOffset((int)($params['offset'] ?? 0));

        $result = $this->reader->query($date, $level, $keyword, $limit, $offset);
        if ($result === null) {
            return BaseResponse::error($response, "No log file for $date", BaseResponse::NOT_FOUND);
        }

        $this->logger->info('Log day read', ['date' => $date, 'total' => $result['total'], 'returned' => count($result['entries'])]);

        return BaseResponse::list($response, $result['entries'], $result['total'], $limit, $offset);
    }

    private function truthy(mixed $value): bool
    {
        return $value === true || $value === '1' || $value === 1 || $value === 'true' || $value === 'yes';
    }
}
