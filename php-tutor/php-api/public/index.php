<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Immutable dotenv (hard constraint 8): existing $_ENV is never overwritten.
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

require_once __DIR__ . '/../app/Support/Env.php';

$app = (require __DIR__ . '/../bootstrap/app.php')();

$app->run();
