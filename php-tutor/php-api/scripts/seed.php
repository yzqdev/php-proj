<?php
declare(strict_types=1);

/**
 * Seeder runner — replaces Laravel's `artisan db:seed`.
 *
 * Usage: php scripts/seed.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Support\Env;
use App\Database\Seeders\DatabaseSeeder;
use Illuminate\Database\Capsule\Manager as Capsule;

// Load .env
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
require_once __DIR__ . '/../app/Support/Env.php';

// Boot Eloquent Capsule (same config as migrate.php)
$capsule = new Capsule();
$capsule->addConnection([
    'driver'   => Env::get('DB_CONNECTION', 'mysql'),
    'host'     => Env::get('DB_HOST', 'localhost'),
    'port'     => Env::get('DB_PORT', '3306'),
    'database' => Env::get('DB_DATABASE', 'php_tutor_api'),
    'username' => Env::get('DB_USERNAME', 'root'),
    'password' => Env::get('DB_PASSWORD', ''),
    'charset'  => Env::get('DB_CHARSET', 'utf8mb4'),
    'collation'=> Env::get('DB_COLLATION', 'utf8mb4_general_ci'),
    'prefix'   => '',
    'strict'   => true,
    'engine'   => 'InnoDB',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

(new DatabaseSeeder())->run();
