<?php
declare(strict_types=1);

namespace App\Http;

use App\Exceptions\ApiException;
use Slim\Exception\HttpException;
use Slim\Interfaces\ErrorRendererInterface;
use Throwable;

/**
 * 全局错误渲染器:把任何异常渲染为统一 JSON 信封。
 */
final class ApiErrorRenderer implements ErrorRendererInterface
{
    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
        if ($exception instanceof ApiException) {
            $payload = [
                'success' => false,
                'error' => [
                    'code' => $exception->getErrorCode(),
                    'message' => $exception->getMessage(),
                ],
            ];
            if ($exception->getDetails() !== []) {
                $payload['error']['details'] = $exception->getDetails();
            }

            return $this->encode($payload);
        }

        if ($exception instanceof HttpException) {
            return $this->encode([
                'success' => false,
                'error' => [
                    'code' => 'http_error',
                    'message' => $exception->getTitle(),
                    'details' => ['description' => $exception->getDescription()],
                ],
            ]);
        }

        return $this->encode([
            'success' => false,
            'error' => [
                'code' => 'internal_error',
                'message' => $displayErrorDetails ? $exception->getMessage() : '服务器内部错误',
            ],
        ]);
    }

    /** @param array<string, mixed> $payload */
    private function encode(array $payload): string
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            $json = '{"success":false,"error":{"code":"internal_error","message":"响应编码失败"}}';
        }

        return $json;
    }
}
