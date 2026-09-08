<?php
declare(strict_types=1);

/**
 * 数据填充脚本:php scripts/seed.php
 */

use App\Database\Manager;
use App\Database\Seeders\DatabaseSeeder;
use DI\ContainerBuilder;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/app.php';

$root = dirname(__DIR__);
if (is_file($root . '/.env')) {
    Dotenv::createImmutable($root)->safeLoad();
}

$container = (new ContainerBuilder())->addDefinitions($root . '/config/dependencies.php')->build();
$container->get(Manager::class);
$container->get(DatabaseSeeder::class)->run();

echo "Seeded 5 users with 3~5 published posts each.\n";
