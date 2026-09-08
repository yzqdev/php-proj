<?php
declare(strict_types=1);

return [
    'driver' => Env::get('DB_CONNECTION', 'mysql'),
    'host' => Env::get('DB_HOST', 'localhost'),
    'port' => Env::get('DB_PORT', '3306'),
    'database' => Env::get('DB_DATABASE', 'php_tutor_api'),
    'username' => Env::get('DB_USERNAME', 'root'),
    'password' => Env::get('DB_PASSWORD', ''),
    'charset' => Env::get('DB_CHARSET', 'utf8mb4'),
    'collation' => Env::get('DB_COLLATION', 'utf8mb4_general_ci'),
    'prefix' => '',
    'strict' => true,
    'engine' => 'InnoDB',
];