<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Yzqde\Fox\Routes\Web;
use Yzqde\Fox\Services\Logger;

$logger = new Logger('fox');

$app = AppFactory::create();

$app->add(function ($request, $handler) use ($logger) {
    $logger->info('Request', [
        'method' => $request->getMethod(),
        'uri' => $request->getUri()->getPath(),
        'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
    ]);
    return $handler->handle($request);
});

$webRoutes = new Web($logger);
$webRoutes->register($app);

$app->run();
