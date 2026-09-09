<?php
declare(strict_types=1);

use App\Support\Env;
use App\Support\LoggerFactory;
use App\Services\ArticleService;
use App\Services\AuthService;
use App\Services\CommentService;
use App\Services\TokenService;
use Illuminate\Database\Capsule\Manager as Capsule;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * PHP-DI definitions. Controllers and middleware are auto-wired via
 * constructor injection — only non-trivial services are declared here.
 */
return [
    // ------------------------------------------------------------------
    // Database: standalone Eloquent Capsule (hard constraint 2).
    // ------------------------------------------------------------------
    Capsule::class => function () {
        $capsule = new Capsule();
        $capsule->addConnection([
            'driver'    => (string) Env::get('DB_CONNECTION', 'mysql'),
            'host'      => (string) Env::get('DB_HOST', 'localhost'),
            'port'      => (string) Env::get('DB_PORT', '3306'),
            'database'  => (string) Env::get('DB_DATABASE', 'php_tutor_api'),
            'username'  => (string) Env::get('DB_USERNAME', 'root'),
            'password'  => (string) Env::get('DB_PASSWORD', ''),
            'charset'   => (string) Env::get('DB_CHARSET', 'utf8mb4'),
            'collation' => (string) Env::get('DB_COLLATION', 'utf8mb4_general_ci'),
            'prefix'    => '',
            'strict'    => true,
            'engine'    => 'InnoDB',
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        return $capsule;
    },

    // ------------------------------------------------------------------
    // Logger: Monolog v3 with Level enum + UidProcessor (hard constraint 7).
    // ------------------------------------------------------------------
    Logger::class => function (): Logger {
        return LoggerFactory::create();
    },

    LoggerInterface::class => fn (Logger $logger) => $logger,

    // ------------------------------------------------------------------
    // Services
    // ------------------------------------------------------------------
    TokenService::class => fn () => new TokenService(),
    AuthService::class  => fn (TokenService $tokens) => new AuthService($tokens),

    ArticleService::class => DI\autowire(),
    CommentService::class => DI\autowire(),

    // ------------------------------------------------------------------
    // Swagger
    // ------------------------------------------------------------------
    \App\Controllers\SwaggerController::class => DI\autowire(),
];
