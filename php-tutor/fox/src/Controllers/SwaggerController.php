<?php

declare(strict_types=1);

namespace Yzqde\Fox\Controllers;

use OpenApi\Generator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yzqde\Fox\Services\Logger;

class SwaggerController
{
    // Pinned so a floating `@5` tag can never silently swap the bundle and break the page again.
    private const SWAGGER_UI_VERSION = '5.32.15';
    private const CDN = 'https://registry.npmmirror.com/swagger-ui-dist/5.32.15/files';
    private const CACHE_TTL = 3600;

    private string $cacheDir;
    private string $cacheFile;

    public function __construct(private Logger $logger)
    {
        $this->cacheDir = __DIR__ . '/../../cache';
        $this->cacheFile = $this->cacheDir . '/swagger.json';

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function index(Request $request, Response $response, $args): Response
    {
        $this->logger->info('Swagger docs accessed');
        $json = $this->encodeSpec($this->spec(), $this->requestBaseUrl($request), htmlSafe: true);

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fox API Documentation</title>
    <link rel="stylesheet" href="{$this->cdn('/swagger-ui.css')}">
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="{$this->cdn('/swagger-ui-bundle.js')}"></script>
    <script>
        SwaggerUIBundle({
            spec: $json,
            dom_id: '#swagger-ui',
            layout: "BaseLayout"
        });
    </script>
</body>
</html>
HTML;

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html');
    }

    public function json(Request $request, Response $response, $args): Response
    {
        $json = $this->encodeSpec($this->spec(), $this->requestBaseUrl($request));
        $response->getBody()->write($json);
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function spec(): array
    {
        $fresh = $this->cachedSpec();
        if ($fresh !== null) {
            return $fresh;
        }

        try {
            $openapi = $this->scanSpec();
        } catch (\Throwable $e) {
            // A broken spec should not take the docs page down; fall back to the stale cache.
            $this->logger->error('Swagger spec scan failed', ['error' => $e->getMessage()]);
            $stale = $this->cachedSpec(PHP_INT_MAX);
            if ($stale !== null) {
                return $stale;
            }
            throw $e;
        }

        $json = json_encode($openapi, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new \RuntimeException('Failed to encode OpenAPI spec: ' . json_last_error_msg());
        }
        file_put_contents($this->cacheFile, $json);

        return (array) json_decode($json, true);
    }

    private function cachedSpec(?int $maxAge = null): ?array
    {
        if (PHP_SAPI === 'cli-server') {
            return null;
        }
        if (!is_file($this->cacheFile) || !is_readable($this->cacheFile)) {
            return null;
        }

        $maxAge ??= self::CACHE_TTL;
        $age = time() - (filemtime($this->cacheFile) ?: 0);
        if ($age > $maxAge) {
            return null;
        }

        $cached = file_get_contents($this->cacheFile);
        if ($cached === false) {
            return null;
        }

        $decoded = json_decode($cached, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function scanSpec(): object
    {
        // zircote/swagger-php still calls SplObjectStorage::contains() and ::attach(),
        // both deprecated in PHP 8.5. Left alone those notices get rendered straight into
        // the document we are trying to serve, so swallow only that class of notice here.
        set_error_handler(
            static fn (int $severity): bool => ($severity & (E_DEPRECATED | E_USER_DEPRECATED)) !== 0,
            E_DEPRECATED | E_USER_DEPRECATED
        );

        try {
            return  (new \OpenApi\Generator())->generate([__DIR__ . '/../']);
        } finally {
            restore_error_handler();
        }
    }

    private function encodeSpec(array $spec, string $baseUrl, bool $htmlSafe = false): string
    {
        if ($spec !== []) {
            $spec['servers'] = [['url' => $baseUrl]];
        }

        $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        if ($htmlSafe) {
            // Blocks a "</script>" in any description from truncating the inline spec.
            $flags |= JSON_HEX_TAG | JSON_HEX_AMP;
        }

        return json_encode($spec, $flags) ?: '{}';
    }

    private function requestBaseUrl(Request $request): string
    {
        $uri = $request->getUri();
        return $uri->getScheme() . '://' . $uri->getHost() . ($uri->getPort() ? ':' . $uri->getPort() : '');
    }

    private function cdn(string $asset): string
    {
        return self::CDN . $asset;
    }
}
