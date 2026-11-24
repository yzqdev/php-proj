<?php

declare(strict_types=1);

use Slim\App;
use Yzqde\Wolf\Controller\HelloController;

return static function (App $app): void {
    $app->get('/hello', [HelloController::class, 'hello']);
};
