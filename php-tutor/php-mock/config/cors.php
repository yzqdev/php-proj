<?php

declare(strict_types=1);

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    // 允许任意来源访问 /api/*（公开 Mock 接口供任何前端联调）
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['X-Mock-Data'],

    'max_age' => 0,

    'supports_credentials' => false,

];
