<?php
declare(strict_types=1);

namespace Tests;

use App\Database\Manager;
use App\Database\Migrator;
use DI\Bridge\Slim\Bridge;
use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;

/**
 * 功能测试基类:每个用例独立构建内存 SQLite 应用,迁移后即可发请求。
 */
abstract class TestCase extends BaseTestCase
{
    protected ContainerInterface $container;

    protected App $app;

    protected Manager $db;

    protected function setUp(): void
    {
        parent::setUp();

        $root = dirname(__DIR__);
        $this->container = (new ContainerBuilder())->addDefinitions($root . '/config/dependencies.php')->build();
        $this->app = Bridge::create($this->container);
        (require $root . '/routes/api.php')($this->app, $this->container);

        $this->db = $this->container->get(Manager::class);
        (new Migrator($this->db))->run($root . '/database/migrations');
    }

    /**
     * 发起一次请求。
     *
     * @param array<string, mixed> $body    JSON 请求体(非空时设置 Content-Type)
     * @param array<string, string> $headers 额外请求头
     */
    protected function request(string $method, string $path, array $body = [], array $headers = [], ?string $token = null): ResponseInterface
    {
        $request = (new ServerRequestFactory())->createServerRequest($method, $path);

        if ($body !== []) {
            $request = $request->withHeader('Content-Type', 'application/json');
            $stream = (new StreamFactory())->createStream((string) json_encode($body, JSON_UNESCAPED_UNICODE));
            $request = $request->withBody($stream);
        }

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if ($token !== null) {
            $request = $request->withHeader('Authorization', 'Bearer ' . $token);
        }

        return $this->app->handle($request);
    }

    /** @return array<string, mixed> */
    protected function decode(ResponseInterface $response): array
    {
        return json_decode((string) $response->getBody(), true) ?? [];
    }

    /** @return array<string, mixed> 响应 data 字段 */
    protected function registerUser(string $name = 'Alice', string $email = 'alice@example.com', string $password = 'password123'): array
    {
        $response = $this->request('POST', '/api/v1/auth/register', [
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        return $this->decode($response)['data'] ?? [];
    }

    protected function login(string $email = 'alice@example.com', string $password = 'password123'): string
    {
        $response = $this->request('POST', '/api/v1/auth/login', [
            'email' => $email,
            'password' => $password,
        ]);

        return (string) ($this->decode($response)['data']['token'] ?? '');
    }
}
