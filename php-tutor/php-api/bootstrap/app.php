<?php
declare(strict_types=1);

use App\Middleware\RequestIdMiddleware;
use App\Support\Env;
use App\Support\ExceptionRenderer;
use DI\Bridge\Slim\Bridge;
use DI\ContainerBuilder;
use Illuminate\Database\Capsule\Manager as Capsule;
use Monolog\Logger;
use Slim\App;
use Slim\Middleware\ErrorMiddleware;

/**
 * Builds the application: container -> App -> error middleware -> mw -> routes.
 * Returns a closure so `require` yields the App, not a plain int.
 */
return function (): App {
    // Boot Eloquent before anything touches the models.
    $containerBuilder = new ContainerBuilder();
    $containerBuilder->addDefinitions(__DIR__ . '/dependencies.php');
    $container = $containerBuilder->build();
    $container->get(Capsule::class);

    $app = Bridge::create($container); // hard constraint 1: PHP-DI bridge only

    // Parse JSON/form bodies BEFORE anything reads getParsedBody().
    $app->addBodyParsingMiddleware();

    // Global error handling -> uniform JSON envelope (hard constraint 10).
    // Custom ErrorHandler maps AppException statuses + forces JSON.
    $errorMiddleware = new ErrorMiddleware(
        $app->getCallableResolver(),
        $app->getResponseFactory(),
        (bool) Env::get('APP_DEBUG', true),
        true,  // logErrors
        false, // logExceptionDetails
    );
    $errorMiddleware->setDefaultErrorHandler(
        new \App\Support\ErrorHandler(
            $app->getCallableResolver(),
            $app->getResponseFactory(),
            (bool) Env::get('APP_DEBUG', true),
            $container->get(Logger::class)
        )
    );
    $app->add($errorMiddleware);

    // Register middleware (LIFO: last added runs FIRST).
    // $container is available to the included file.
    require __DIR__ . '/middleware.php';

    require __DIR__ . '/../routes/index.php';

    return $app;
};
