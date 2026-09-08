<?php
declare(strict_types=1);

use App\Middleware\RequestIdMiddleware;
use Tuupola\Middleware\CorsMiddleware;

/**
 * Middleware registration.
 *
 * LIFO: the LAST add() executes FIRST (hard constraint 6).
 *
 *   written here            execution order
 *   ------------            ---------------
 *   RequestIdMiddleware     3. runs inside error handling
 *   CorsMiddleware          1. runs FIRST — answers OPTIONS preflight
 *   (ErrorMiddleware        2. registered earlier in app.php)
 */
$app->add(new RequestIdMiddleware($container->get(Monolog\Logger::class)));

// CORS is added LAST so it executes FIRST and handles OPTIONS preflight.
// NOTE: the v1 config key is 'headers.allow' (v0 used 'allow.headers' —
// hard constraint 5: wrong key silently does nothing).
$app->add(new CorsMiddleware([
    'origin'         => ['*'],
    'methods'        => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    'headers.allow'  => ['Authorization', 'Content-Type', 'Accept', 'X-Request-Id'],
    'headers.expose' => ['X-Request-Id'],
    'credentials'    => false,
    'cache'          => 86400,
]));