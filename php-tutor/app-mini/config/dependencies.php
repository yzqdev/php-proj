<?php
declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DocsController;
use App\Controllers\PostController;
use App\Controllers\UserController;
use App\Database\Manager;
use App\Database\Seeders\DatabaseSeeder;
use App\Http\ApiErrorHandler;
use App\Http\ApiErrorRenderer;
use App\Middleware\AccessLogMiddleware;
use App\Middleware\AuthMiddleware;
use App\Security\JwtService;
use App\Services\AuthService;
use App\Services\PostService;
use App\Services\UserService;
use App\Validation\Validator;
use Lcobucci\Clock\Clock;
use Lcobucci\Clock\SystemClock;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

require_once __DIR__ . '/app.php';
require_once __DIR__ . '/database.php';

// 所有依赖在此集中声明;业务代码一律构造函数注入。
// CallableResolverInterface 由 php-di/slim-bridge 的 Bridge::create() 写入容器,故此处不再定义。
return [
    // Eloquent 数据库管理器(单例:PHP-DI 对同一 key 只解析一次)
    Manager::class => DI\factory(static function (): Manager {
        $manager = new Manager(database_config());
        $manager->boot();

        return $manager;
    }),

    // 日志:Monolog 写入 storage/logs/app.log
    LoggerInterface::class => DI\factory(static function (): LoggerInterface {
        $level = match (strtolower((string) app_config('log.level', 'debug'))) {
            'error' => Level::Error,
            'warning', 'warn' => Level::Warning,
            'info' => Level::Info,
            default => Level::Debug,
        };
        $file = (string) app_config('log.file');
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        return new Logger('app', [new StreamHandler($file, $level)]);
    }),

    // PSR-17 工厂(AppFactory::createFromContainer 会用到)
    ResponseFactoryInterface::class => DI\factory(static fn (): ResponseFactory => new ResponseFactory()),
    ServerRequestFactoryInterface::class => DI\factory(static fn (): ServerRequestFactory => new ServerRequestFactory()),

    // JWT 时钟(HS256 用 UTC)
    Clock::class => DI\factory(static fn (): SystemClock => new SystemClock(new DateTimeZone('UTC'))),
    JwtService::class => DI\factory(static function (ContainerInterface $c): JwtService {
        return new JwtService(
            (string) app_config('jwt.secret'),
            (string) app_config('jwt.issuer'),
            (int) app_config('jwt.ttl'),
            $c->get(Clock::class),
        );
    }),

    // 业务服务(可自动装配构造参数)
    AuthService::class => DI\autowire(),
    PostService::class => DI\autowire(),
    UserService::class => DI\autowire(),
    Validator::class => DI\autowire(),

    // 中间件
    AuthMiddleware::class => DI\autowire(),
    AccessLogMiddleware::class => DI\autowire(),

    // 错误处理
    ApiErrorRenderer::class => DI\autowire(),
    ApiErrorHandler::class => DI\autowire(),

    // 控制器(DocsController 需要 app url,故显式构造)
    AuthController::class => DI\autowire(),
    PostController::class => DI\autowire(),
    UserController::class => DI\autowire(),
    DocsController::class => DI\factory(static fn (): DocsController => new DocsController((string) app_config('url'))),

    // 数据填充
    DatabaseSeeder::class => DI\autowire(),
];
