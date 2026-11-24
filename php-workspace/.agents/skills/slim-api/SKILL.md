---
name: slim-api
description: Slim 4 + PHP-DI + Monolog 构建轻量 REST API 的项目结构、中间件与依赖组合指南
---

# Slim 4 REST API 开发指南

## Overview

Slim 4 是 PSR-7 / PSR-15 微框架，只提供路由与中间件管道。它不内置 ORM、模板、认证，需要按需组合组件。适合轻量 API、微服务、内部服务与 API 网关。

典型组合：

```text
Slim 4 + PHP-DI + Monolog + Nyholm/Slim PSR-7 + PDO 或 Doctrine DBAL + firebase/php-jwt
```

## 项目结构

```text
app/
├── Application/
│   ├── Actions/          每个端点的 Action
│   ├── Middleware/
│   └── Handlers/         错误处理
├── Domain/
│   ├── Entity/
│   ├── Repository/       接口
│   └── Service/
├── Infrastructure/
│   ├── Persistence/      PDO/DBAL 实现
│   └── Http/             外部客户端
├── Support/
config/
├── container.php
├── middleware.php
├── routes.php
public/
└── index.php
tests/
```

## 入口与容器

```php
<?php
// public/index.php
declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$builder = new ContainerBuilder();
$builder->addDefinitions(__DIR__ . '/../config/container.php');
$container = $builder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

(require __DIR__ . '/../config/middleware.php')($app);
(require __DIR__ . '/../config/routes.php')($app);

$app->run();
```

- 容器在入口构建一次，通过 `AppFactory::setContainer()` 注入。
- 配置、中间件、路由分文件，便于维护。
- 生产环境开启容器编译缓存。

## 依赖配置（PHP-DI）

```php
<?php
// config/container.php
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

return [
    LoggerInterface::class => function () {
        $logger = new Logger('api');
        $logger->pushHandler(new StreamHandler(__DIR__ . '/../storage/logs/app.log'));
        return $logger;
    },

    PDO::class => function () {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $_ENV['DB_HOST'], $_ENV['DB_PORT'], $_ENV['DB_NAME']);
        return new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    },
];
```

- PHP-DI 支持自动装配，接口需显式绑定实现。
- 单例与请求级对象区分：数据库连接用单例，请求相关对象不要缓存。
- 避免在容器中写复杂业务逻辑。

## 路由

```php
<?php
// config/routes.php
use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\CreateUserAction;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app): void {
    $app->get('/', fn ($request, $response) => $response->write('ok'));

    $app->group('/api/v1', function (RouteCollectorProxy $group) {
        $group->get('/users', ListUsersAction::class);
        $group->post('/users', CreateUserAction::class);
        $group->get('/users/{id:[0-9]+}', GetUserAction::class);
    });
};
```

- 路由参数用正则约束类型。
- Action 作为可调用类，由容器解析，构造器注入依赖。
- 路由文件不要写业务逻辑。

## Action

```php
<?php
declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Domain\User\UserService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class CreateUserAction
{
    public function __construct(private readonly UserService $users) {}

    public function __invoke(Request $request, Response $response): Response
    {
        $payload = (array) $request->getParsedBody();
        $user = $this->users->create($payload);

        $response->getBody()->write(json_encode($user, JSON_THROW_ON_ERROR));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }
}
```

- Action 只做输入转换、调用 Service、构造响应。
- 校验在 Action 或独立 Validator 中完成，不散落在 Service。
- 响应统一封装，避免每个 Action 重复设置头。

## 中间件

```php
<?php
// config/middleware.php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\App;

return function (App $app): void {
    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();

    // 全局中间件：注意添加顺序（后添加的先执行）
    $app->add(function (Request $request, Handler $handler): Response {
        $response = $handler->handle($request);
        return $response->withHeader('X-Request-Id', bin2hex(random_bytes(8)));
    });

    $app->addErrorMiddleware(
        displayErrorDetails: ($_ENV['APP_ENV'] ?? 'prod') !== 'prod',
        logErrors: true,
        logErrorDetails: true,
    );
};
```

推荐顺序（外到内）：

```text
错误处理 → 请求 ID → CORS → 认证 → 限流 → 路由 → Action
```

- Slim 的 `add()` 是后进先出，注意实际执行顺序。
- CORS 预检请求需要在认证前放行。
- 错误中间件必须最外层，才能捕获后续异常。

## 错误处理

```php
<?php
namespace App\Application\Handlers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpException;
use Slim\Psr7\Factory\ResponseFactory;

final class JsonErrorHandler
{
    public function __invoke(
        Request $request,
        \Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails,
    ): Response {
        $status = $exception instanceof HttpException ? $exception->getCode() : 500;
        $body = [
            'error' => [
                'code' => $status,
                'message' => $displayErrorDetails ? $exception->getMessage() : 'Internal Server Error',
            ],
        ];

        $response = (new ResponseFactory())->createResponse($status);
        $response->getBody()->write(json_encode($body, JSON_THROW_ON_ERROR));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
```

- 生产环境不返回堆栈与内部消息。
- 统一错误结构，前端与网关好处理。
- 记录异常对象而非仅 message。

## 数据库集成

- 小项目：PDO + Repository，SQL 全部预处理。
- 中大型：Doctrine DBAL 或 ORM；DBAL 适合手写查询，ORM 适合领域模型。
- 事务放 Service 层，Repository 不开启事务。

```php
$pdo->beginTransaction();
try {
    $orders->insert($order);
    $stock->decrement($order->items);
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}
```

## JWT 认证

```php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$token = JWT::encode(
    ['sub' => $user->id, 'exp' => time() + 3600, 'iss' => 'acme-api'],
    $_ENV['JWT_SECRET'],
    'HS256',
);

$payload = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
```

- 校验 `exp`、`iss`、`aud`，拒绝 `alg: none`。
- 密钥从环境变量读取，HS256 密钥足够长，或改用 RS256。
- 中间件解析 `Authorization: Bearer`，把用户放入请求属性。
- 敏感操作仍需查库确认用户状态。

## 配置与环境

- 用 `vlucas/phpdotenv` 加载 `.env`（仅开发）。
- 配置读取集中在 `config/`，不在 Action 中散落 `$_ENV`。
- 生产环境由进程管理器注入环境变量。

## 测试

```php
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;

$app = require __DIR__ . '/../bootstrap/app.php';
$request = (new ServerRequestFactory())
    ->createServerRequest('GET', '/api/v1/users');

$response = $app->handle($request);
$this->assertSame(200, $response->getStatusCode());
```

- Action/Service 用单元测试，路由与中间件用集成测试。
- 数据库测试用独立测试库或事务回滚。
- 外部 HTTP 用 mock client 替换。

## 常见坑

- 中间件顺序错误导致认证先于 CORS 或错误处理。
- 忘记 `addBodyParsingMiddleware()`，`getParsedBody()` 返回 null。
- 容器中把请求相关对象注册为单例，造成数据串请求。
- 直接 `echo` 输出，绕过 PSR-7 响应。
- 生产环境开启 `displayErrorDetails`，泄漏堆栈。
- JWT 只校验签名不校验过期时间。

## References

- Slim 4 文档：<https://www.slimframework.com/docs/v4/>
- PHP-DI：<https://php-di.org/>
- Nyholm PSR-7：<https://github.com/Nyholm/psr7>
- Firebase JWT：<https://github.com/firebase/php-jwt>
- 错误处理：<https://www.slimframework.com/docs/v4/middleware/error-handling.html>
