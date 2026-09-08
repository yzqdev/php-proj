<?php
declare(strict_types=1);

/**
 * Web 入口:加载 .env → 构建容器 → 组装应用 → 运行。
 */

use App\Database\Manager;
use DI\Bridge\Slim\Bridge;
use DI\ContainerBuilder;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/app.php';

$root = dirname(__DIR__);

if (is_file($root . '/.env')) {
    Dotenv::createImmutable($root)->safeLoad();
}

$container = (new ContainerBuilder())->addDefinitions($root . '/config/dependencies.php')->build();

$app = Bridge::create($container);
// 启动 Eloquent:设置全局连接解析器,必须在处理任何请求前完成
$container->get(Manager::class);
(require $root . '/routes/api.php')($app, $container);

$app->run();
