<?php

/**
 * ============================================================
 * 配置文件【模板】—— 复制成 config.php 后修改为自己的真实值
 * ============================================================
 *
 * 使用步骤：
 *   1) Copy-Item config\config.example.php config\config.php
 *   2) 修改 config\config.php 里的 db.password / api.jwt.secret
 *   3) config.php 已在 .gitignore 中，不会被提交
 *
 * 环境变量支持：
 *   - 敏感字段（DB 密码、JWT 密钥）优先从环境变量读取，
 *     回退到本文件的默认值，本地开发无需额外配置；
 *   - 生产环境只需要注入环境变量，不需要改代码。
 *
 * 对应 Java / Spring Boot：
 *   - 相当于 application.yml + @Value("${DB_PASSWORD}") 组合
 */
declare(strict_types=1);

// 环境变量读取助手（局部闭包，不污染全局命名空间）
// 用 $_ENV + getenv() 双保险：Windows / Linux 一致
$env = static function (string $key, mixed $default = null): mixed {
    $value = $_ENV[$key] ?? getenv($key);
    return $value === false || $value === null || $value === '' ? $default : $value;
};

return [
    // 应用名称与运行环境（debug=true 时错误会打印细节，仅本地使用）
    'app' => [
        'name'  => $env('APP_NAME', 'php-learn'),
        'env'   => $env('APP_ENV', 'local'),
        'debug' => filter_var($env('APP_DEBUG', true), FILTER_VALIDATE_BOOL),
    ],

    // 数据库连接：对应 Java 的 spring.datasource.*
    // 关键点：pdo driver 是 mysql；charset 必须在连接时设置，
    // 否则会出现「中文乱码」或「Incorrect string value」错误。
    'db' => [
        'host'     => $env('DB_HOST', '127.0.0.1'),
        'port'     => (int)$env('DB_PORT', 3306),
        'name'     => $env('DB_NAME', 'php_learn'),
        'username' => $env('DB_USERNAME', 'root'),
        // 敏感字段：优先环境变量，回退本地开发默认
        'password' => $env('DB_PASSWORD', '123456'),
        'charset'  => $env('DB_CHARSET', 'utf8mb4'),
    ],

    // 分页默认每页条数
    'paging' => [
        'per_page' => (int)$env('PAGING_PER_PAGE', 8),
    ],

    // 日志配置：Monolog 日志库
    'logger' => [
        'path'      => $env('LOG_PATH', dirname(__DIR__) . '/logs'),
        'level'     => $env('LOG_LEVEL', 'debug'),
        'max_files' => (int)$env('LOG_MAX_FILES', 30),
    ],

    // ============================================================
    // Doctrine ORM 配置
    // ============================================================
    //
    // 关键点：
    //   - 连接参数不再重复写，DoctrineServiceProvider 从 'db' 节点读取
    //   - spring.datasource.url 对应关系：只维护一份 db 配置
    'doctrine' => [
        'metadata_dirs' => [
            dirname(__DIR__) . '/app/Entities',
        ],
        'proxy_dir' => dirname(__DIR__) . '/storage/proxies',
        'cache' => [
            'metadata' => 'array',   // 开发期：每次请求重新解析，改完注解立刻生效
        ],
    ],

    // ============================================================
    // API 层配置（前后端分离 / Bearer Token 鉴权）
    // ============================================================
    'api' => [
        'cors' => [
            'allowed_origins' => [
                'http://localhost:5173',
                'http://127.0.0.1:5173',
                'http://localhost:4173',
                'http://127.0.0.1:4173',
            ],
            'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
            'allowed_headers' => [
                'Authorization',
                'Content-Type',
                'X-Requested-With',
                'Accept',
                'Origin',
                'X-Request-Id',
            ],
            'supports_credentials' => false,
            'max_age' => 86400,
        ],

        // JWT 配置（Access Token 用 HS256 手写实现，见 app/Services/Jwt.php）
        'jwt' => [
            // 密钥：base64 编码的 32 字节随机值
            // 生成方式：php -r "echo base64_encode(random_bytes(32));"
            // 生产环境强烈建议用环境变量 JWT_SECRET 注入
            'secret'     => $env('JWT_SECRET', 'REPLACE_WITH_YOUR_BASE64_SECRET'),
            'issuer'     => $env('JWT_ISSUER', 'php-learn'),
            'audience'   => $env('JWT_AUDIENCE', 'php-learn-web'),
            // Access Token 有效期（秒），15 分钟
            'access_ttl'  => (int)$env('JWT_ACCESS_TTL', 900),
            // Refresh Token 有效期（秒），7 天
            'refresh_ttl' => (int)$env('JWT_REFRESH_TTL', 604800),
        ],

        // 文件上传
        'upload' => [
            'storage_driver' => 'local',
            'storage_path'   => dirname(__DIR__) . '/storage/uploads',
            'url_base'       => $env('UPLOAD_URL_BASE', 'http://127.0.0.1:8000/uploads'),
        ],
    ],
];
