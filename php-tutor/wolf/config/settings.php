<?php

declare(strict_types=1);

return [
    'app' => [
        'name' => 'wolf',
        'env' => getenv('APP_ENV') ?: 'dev',
    ],
    'logger' => [
        'name' => 'wolf',
        'level' => \Monolog\Level::Debug,
    ],
];
