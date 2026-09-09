<?php

declare(strict_types=1);

use App\Auth\AuthService;
use App\Auth\TokenStore;
use App\Auth\UserStore;
use App\Controller\ApiErrorHandler;
use App\Controller\AuthController;
use App\Controller\CloudDriveController;
use App\Controller\DoctrineController;
use App\Controller\LogController;
use App\Controller\PhpDemoController;
use App\Doctrine\EntityManagerFactory;
use App\Log\LoggerFactory;
use App\Log\LogStore;
use App\Redis\RedisClient;
use App\Service\DoctrineDemoService;
use App\Service\PhpDemoService;
use DI\ContainerBuilder;
use Doctrine\ORM\EntityManager;
use Predis\ClientInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ResponseFactory as SlimResponseFactory;
use function DI\autowire;
use function DI\factory;

/**
 * 统一入口（前端控制器）。
 * 所有请求（/api/*.php、/docs、旧版 /?module=、未匹配路径）都经由 Slim 分发。
 */
require __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
    ResponseFactoryInterface::class => factory(static fn(): SlimResponseFactory => new SlimResponseFactory()),
    LoggerInterface::class => factory([LoggerFactory::class, 'create']),
    EntityManager::class => factory(static fn(): EntityManager => (new EntityManagerFactory())->create()),
    PhpDemoService::class => autowire(),
    DoctrineDemoService::class => autowire(),
    PhpDemoController::class => autowire(),
    DoctrineController::class => autowire(),
    CloudDriveController::class => autowire(),
    UserStore::class => factory(static fn(): UserStore => new UserStore(UserStore::defaultPath())),
    TokenStore::class => autowire(),
    AuthService::class => autowire(),
    ClientInterface::class => factory([RedisClient::class, 'create']),
    AuthController::class => autowire(),
    LogStore::class => factory(static fn(): LogStore => new LogStore(LogStore::defaultDir())),
    LogController::class => autowire(),
]);
$container = $containerBuilder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// 全局错误兜底：统一输出与迁移前一致的 JSON 信封（不泄露堆栈等内部信息）；
// Slim 内部日志关闭（logErrors=false），异常统一由 ApiErrorHandler 用 PSR-3 日志器记录
$errorMiddleware = $app->addErrorMiddleware(false, false, false);
$errorMiddleware->setDefaultErrorHandler(new ApiErrorHandler(
    $container->get(ResponseFactoryInterface::class),
    $container->get(LoggerInterface::class),
));

// 注册全部路由（routes.php 内最后添加 RequestLogger / Cors，使其们位于最外层）
(require __DIR__ . '/../app/routes.php')($app);

$app->run();
