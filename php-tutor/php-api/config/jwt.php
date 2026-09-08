<?php
declare(strict_types=1);

return [
    'secret' => Env::get('JWT_SECRET', 'change-me'),
    'issuer' => Env::get('JWT_ISSUER', 'php-tutor-api'),
    'audience' => Env::get('JWT_AUDIENCE', 'php-tutor-api'),
    'access_ttl' => (int) Env::get('JWT_ACCESS_TTL', 3600),
    'refresh_ttl' => (int) Env::get('JWT_REFRESH_TTL', 604800),
    'algorithm' => 'HS256',
];