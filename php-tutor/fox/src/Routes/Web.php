<?php

declare(strict_types=1);

namespace Yzqde\Fox\Routes;

use Slim\App;
use Yzqde\Fox\Controllers\HomeController;
use Yzqde\Fox\Controllers\LogController;
use Yzqde\Fox\Controllers\RainController;
use Yzqde\Fox\Controllers\SwaggerController;
use Yzqde\Fox\Controllers\UserController;
use Yzqde\Fox\Services\Logger;

class Web
{
    public function __construct(private Logger $logger)
    {
    }

    public function register(App $app): void
    {
        $homeController = new HomeController($this->logger);
        $swaggerController = new SwaggerController($this->logger);
        $logController = new LogController($this->logger);
        $userController = new UserController($this->logger);

        $app->get('/', [$homeController, 'index']);
        $app->get('/hello/{name}', [$homeController, 'hello']);
        $app->get('/swagger', [$swaggerController, 'index']);
        $app->get('/swagger/json', [$swaggerController, 'json']);

        $app->get('/api/logs', [$logController, 'index']);
        $app->get('/api/logs/{date}', [$logController, 'show']);

        $app->get('/api/users', [$userController, 'index']);
        $app->get('/api/users/{id}', [$userController, 'show']);
        $app->post('/api/users', [$userController, 'store']);
        $app->put('/api/users/{id}', [$userController, 'update']);
        $app->delete('/api/users/{id}', [$userController, 'destroy']);

        $app->get("/api/rain/getIndex",[RainController::class, 'getIndex']);
    }
}
