<?php
declare(strict_types=1);

return [
    'name' => Env::get('APP_NAME', 'php-tutor-api'),
    'env' => Env::get('APP_ENV', 'local'),
    'debug' => filter_var(Env::get('APP_DEBUG', true), FILTER_VALIDATE_BOOLEAN),
    'url' => Env::get('APP_URL', 'http://localhost'),
    'pagination' => [
        'per_page' => 15,
        'max_per_page' => 100,
    ],
];