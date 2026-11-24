<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\App;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$container = (new ContainerBuilder())
    ->addDefinitions(__DIR__ . '/../config/container.php')
    ->build();

AppFactory::setContainer($container);

/** @var App<Psr\Container\ContainerInterface> $app */
$app = AppFactory::create();

/** @var callable(App<Psr\Container\ContainerInterface>): void $routes */
$routes = require __DIR__ . '/../routes/web.php';
$routes($app);

$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$app->run();
