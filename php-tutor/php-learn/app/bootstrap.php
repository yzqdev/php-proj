<?php

/**
 * ============================================================
 * 应用引导（Bootstrap）— Slim 4 + php-di 版
 * ============================================================
 *
 * 类比 Java / Spring Boot：
 *   - 本文件等价于 SpringApplication.run() 之前的"环境准备" + "容器配置"；
 *   - Slim 4  = Spring Boot 的 Web 层（路由 + 中间件 + PSR-7）；
 *   - php-di   = Spring 的 IoC 容器（@Autowired 的 PHP 版）；
 *   - Monolog  = SLF4J + Logback（日志门面 + 实现）。
 *
 * 做的事（顺序不能乱）：
 *   1. 加载 Composer 自动加载器（PSR-4：App\ → app/）
 *   2. 开启错误/异常报告（本地可见细节，生产应关闭）
 *   3. 构建 php-di 容器 + Slim App
 *   4. 注册全局中间件（CORS / 错误处理 / 日志）
 *   5. 注册 API 路由（app/api/routes.php）
 *   6. 返回 App 对象，由 public/index.php 调用 $app->run()
 */
declare(strict_types=1);

// 1) Composer 自动加载器
require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Helpers\Config;

use App\Helpers\MyLogger;
use App\Middleware\Cors;
use App\Services\DoctrineServiceProvider;
use App\Services\Jwt;
use App\Services\TokenService;
use DI\ContainerBuilder;
use Doctrine\ORM\EntityManager;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Slim\Factory\AppFactory;
use Slim\Interfaces\ErrorRendererInterface;

// 2) 错误报告：本地开发要看见一切
ini_set('display_errors', '1');
error_reporting(E_ALL);

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

// 3) 构建 php-di 容器
$containerBuilder = new ContainerBuilder();
$containerBuilder->useAutowiring(true);          // 自动装配（构造函数注入）

// 全部依赖显式注册（autowire 不覆盖非平凡构造器的场景）
$containerBuilder->addDefinitions([
    // PSR-15 错误处理器：捕获所有异常，转成 JSON
    ErrorRendererInterface::class => \App\Middleware\ErrorRenderer::class,

    // Doctrine EntityManager（所有数据访问的统一入口）
    EntityManager::class => function (\Psr\Container\ContainerInterface $c): EntityManager {
        return DoctrineServiceProvider::createEntityManager();
    },

    // JWT 服务（构造器需要 secret/issuer/audience，无法自动装配，必须显式工厂）
    Jwt::class => function (\Psr\Container\ContainerInterface $c): Jwt {
        return Jwt::make();
    },
    Logger::class => function (): Logger {
        $logger = new Logger('app');
        $logger->pushHandler(new StreamHandler('php://stderr', Logger::DEBUG));
        $logDir = __DIR__ . '/../var/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        // 按天轮转：每天生成 app.log-YYYY-MM-DD.log，只保留最近 30 天
        // 文件名规则为 {filename}-{date}，此处传带 .log 后缀的名字，
        // 否则生成的文件是 app-YYYY-MM-DD（无后缀），LogController 读不到。
        $logger->pushHandler(new RotatingFileHandler(
            filename: $logDir . '/app.log',
            maxFiles: 30,
            level: Logger::DEBUG,
            useLocking: true,
        ));
        return $logger;
    },

    // TokenService（依赖 EntityManager + Jwt，两者已注册）
    TokenService::class => function (\Psr\Container\ContainerInterface $c): TokenService {
        return new TokenService(
            $c->get(EntityManager::class),
            $c->get(Jwt::class),
        );
    },
]);

$container = $containerBuilder->build();

// 4) 创建 Slim App（AppFactory 会自动用容器创建 ResponseFactory / ServerRequestFactory 等）
$app = AppFactory::create(null, $container);

// 5) 注册全局中间件（按执行顺序：CORS → 日志 → 路由）
$app->add(new Cors());
$app->add(new \App\Middleware\RequestLogger());

// 6) 注册路由（从 app/api/routes.php 加载）
// routes.php 返回一个闭包，需立即调用才能注册路由
$returned = require_once dirname(__DIR__) . '/app/api/routes.php';
if (is_callable($returned)) {
    $returned($app);
}

// 7) 全局异常处理器：Slim 会自动捕获 Throwable，由 ErrorRenderer 转 JSON
//    注意：此处理器绕过 Slim 中间件链，必须手动添加 CORS 头
//    否则浏览器会因为缺少 Access-Control-Allow-Origin 而拒绝响应
set_exception_handler(function (\Throwable $e): void {
    $debug = Config::get('app.debug', false);

    // 记录异常日志
    MyLogger::error($e::class . ': ' . $e->getMessage(), [
        'file'    => $e->getFile(),
        'line'    => $e->getLine(),
        'trace'   => substr((string)$e->getTraceAsString(), 0, 1200),
        'request' => ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN') . ' ' . ($_SERVER['REQUEST_URI'] ?? '/'),
    ]);

    // 输出 JSON（与 Slim ErrorRenderer 二选一，这里作为兜底）
    if (!headers_sent()) {
        $isBiz = $e instanceof \App\Exceptions\BusinessException;
        $httpStatus = $isBiz ? $e->getHttpStatus() : 500;
        $bizCode = $isBiz ? $e->getBizCode() : 50000;

        // 补偿 CORS 头——此处理器绕过了 Cors 中间件，必须手动添加
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if ($origin !== '') {
            $cors = Config::get('api.cors', []);
            $allowedOrigins = $cors['allowed_origins'] ?? [];
            foreach ($allowedOrigins as $o) {
                if (strcasecmp($o, $origin) === 0) {
                    header('Access-Control-Allow-Origin: ' . $origin);
                    header('Vary: Origin');
                    break;
                }
            }
        }

        $body = [
            'code'    => $bizCode,
            'message' => $debug ? $e->getMessage() : ($isBiz ? $e->getMessage() : '服务器内部错误'),
            'data'    => null,
        ];
        if ($debug) {
            $body['debug'] = [
                'type'  => $e::class,
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => substr((string)$e->getTraceAsString(), 0, 1200),
            ];
        }
        http_response_code($httpStatus);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    exit;
});

// 9) 导出 App 对象供入口脚本调用
return $app;