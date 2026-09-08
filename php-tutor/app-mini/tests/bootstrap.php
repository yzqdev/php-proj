<?php
declare(strict_types=1);

/**
 * 测试引导:固定测试环境变量(内存 SQLite),加载自动加载与配置函数。
 */

putenv('APP_ENV=testing');
putenv('APP_DEBUG=true');
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=:memory:');
putenv('JWT_SECRET=test-secret-key-with-sufficient-length-0123456789');
putenv('JWT_ISSUER=blog-api-test');
putenv('JWT_TTL=3600');
putenv('CORS_ALLOW_ORIGINS=*');
putenv('LOG_LEVEL=error');

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/app.php';
