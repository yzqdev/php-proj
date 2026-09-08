<?php
declare(strict_types=1);

/**
 * 迁移执行脚本。
 *
 * 用法:
 *   php scripts/migrate.php            # 执行未运行的迁移
 *   php scripts/migrate.php rollback    # 回滚最近一条迁移
 */

use App\Database\Manager;
use App\Database\Migrator;
use DI\ContainerBuilder;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/app.php';

$root = dirname(__DIR__);
if (is_file($root . '/.env')) {
    Dotenv::createImmutable($root)->safeLoad();
}

$container = (new ContainerBuilder())->addDefinitions($root . '/config/dependencies.php')->build();
$manager = $container->get(Manager::class);
$migrator = new Migrator($manager);

$migrationsPath = $root . '/database/migrations';

$action = $argv[1] ?? 'migrate';
if ($action === 'rollback') {
    $rolledBack = $migrator->rollback($migrationsPath);
    echo $rolledBack === null ? "Nothing to roll back.\n" : "Rolled back: {$rolledBack}\n";
    exit(0);
}

$executed = $migrator->run($migrationsPath);
echo $executed === [] ? "Nothing to migrate.\n" : "Migrated: " . implode(', ', $executed) . "\n";
