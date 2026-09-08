<?php
declare(strict_types=1);

/**
 * Migration runner — replaces Laravel's `artisan migrate`.
 *
 * Usage:
 *   php scripts/migrate.php          # run all pending migrations
 *   php scripts/migrate.php rollback # run all "down" callbacks
 *   php scripts/migrate.php refresh  # rollback then up
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Support\Env;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

// Load .env
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
require_once __DIR__ . '/../app/Support/Env.php';

// Boot Eloquent Capsule
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

// Ensure migrations tracking table exists
if (!$capsule->schema()->hasTable('migrations')) {
    $capsule->schema()->create('migrations', function (Blueprint $table) {
        $table->id();
        $table->string('migration', 255)->unique();
        $table->integer('batch')->default(0);
        $table->timestamps();
    });
}

$mode = $argv[1] ?? 'up';
$schema = $capsule->schema();
$migrationDir = __DIR__ . '/../database/migrations';
$files = glob($migrationDir . '/*.php');
sort($files);

if ($mode === 'rollback') {
    $batch = (int) $capsule->table('migrations')->max('batch');
    $ran = $capsule->table('migrations')
        ->where('batch', $batch)
        ->orderBy('id', 'desc')
        ->get();

    foreach ($ran as $row) {
        $path = $migrationDir . '/' . $row->migration;
        if (!is_file($path)) continue;
        $m = require $path;
        $m['down']($schema);
        $capsule->table('migrations')->where('migration', $row->migration)->delete();
        echo "Rolled back: {$row->migration}" . PHP_EOL;
    }
    echo "Rollback complete." . PHP_EOL;
    exit(0);
}

if ($mode === 'refresh') {
    // Rollback all batches
    while ($capsule->table('migrations')->count() > 0) {
        $batch = (int) $capsule->table('migrations')->max('batch');
        $ran = $capsule->table('migrations')
            ->where('batch', $batch)
            ->orderBy('id', 'desc')
            ->get();
        foreach ($ran as $row) {
            $path = $migrationDir . '/' . $row->migration;
            if (!is_file($path)) continue;
            $m = require $path;
            $m['down']($schema);
            $capsule->table('migrations')->where('migration', $row->migration)->delete();
        }
    }
    echo "All migrations rolled back." . PHP_EOL;
    $mode = 'up';
}

// Run pending migrations (up)
$batch = (int) $capsule->table('migrations')->max('batch') + 1;
$ran = $capsule->table('migrations')->pluck('migration')->toArray();
$now = (new DateTime())->format('Y-m-d H:i:s');

foreach ($files as $file) {
    $name = basename($file);
    if (in_array($name, $ran, true)) continue;

    $m = require $file;
    $m['up']($schema);
    $capsule->table('migrations')->insert([
        'migration' => $name,
        'batch' => $batch,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    echo "Migrated: {$name}" . PHP_EOL;
}

echo "Migration complete." . PHP_EOL;