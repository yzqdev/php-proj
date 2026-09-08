<?php
declare(strict_types=1);

/**
 * 应用级配置读取函数。
 *
 * 本文件仅定义函数,不产生任何副作用;所有函数都以 function_exists 保护,
 * 可被多次 require 而不会导致致命错误。
 */

if (!function_exists('base_path')) {
    /** 项目根目录,可拼接子路径。 */
    function base_path(string $path = ''): string
    {
        return dirname(__DIR__) . ($path !== '' ? DIRECTORY_SEPARATOR . $path : '');
    }
}

if (!function_exists('app_path')) {
    /** app/ 目录。 */
    function app_path(string $path = ''): string
    {
        return base_path('app' . ($path !== '' ? DIRECTORY_SEPARATOR . $path : ''));
    }
}

if (!function_exists('config_path')) {
    /** config/ 目录。 */
    function config_path(string $path = ''): string
    {
        return base_path('config' . ($path !== '' ? DIRECTORY_SEPARATOR . $path : ''));
    }
}

if (!function_exists('database_path')) {
    /** database/ 目录。 */
    function database_path(string $path = ''): string
    {
        return base_path('database' . ($path !== '' ? DIRECTORY_SEPARATOR . $path : ''));
    }
}

if (!function_exists('storage_path')) {
    /** storage/ 目录。 */
    function storage_path(string $path = ''): string
    {
        return base_path('storage' . ($path !== '' ? DIRECTORY_SEPARATOR . $path : ''));
    }
}

if (!function_exists('public_path')) {
    /** public/ 目录。 */
    function public_path(string $path = ''): string
    {
        return base_path('public' . ($path !== '' ? DIRECTORY_SEPARATOR . $path : ''));
    }
}

if (!function_exists('env')) {
    /** 读取环境变量,不存在时返回默认值。 */
    function env(string $key, mixed $default = null): mixed
    {
        $value = getenv($key);

        return $value === false ? $default : $value;
    }
}

if (!function_exists('env_bool')) {
    /** 读取环境变量并解析为布尔值。 */
    function env_bool(string $key, bool $default = false): bool
    {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }

        return match (strtolower($value)) {
            '1', 'true', 'yes', 'on' => true,
            '0', 'false', 'no', 'off' => false,
            default => $default,
        };
    }
}

if (!function_exists('app_config')) {
    /**
     * 读取应用配置。支持点号路径,如 app_config('jwt.secret')。
     *
     * @param string|null $key     配置键,为 null 时返回整个配置数组
     * @param mixed       $default 键不存在时返回的默认值
     */
    function app_config(?string $key = null, mixed $default = null): mixed
    {
        static $config = null;

        if ($config === null) {
            $env = env('APP_ENV', 'production');
            $config = [
                'env' => $env,
                'url' => rtrim((string) env('APP_URL', 'http://localhost:8080'), '/'),
                'debug' => env_bool('APP_DEBUG', $env !== 'production'),
                'jwt' => [
                    'secret' => (string) env('JWT_SECRET', ''),
                    'issuer' => (string) env('JWT_ISSUER', 'blog-api'),
                    'ttl' => max(60, (int) env('JWT_TTL', '3600')),
                ],
                'cors' => [
                    'origins' => array_values(array_filter(array_map('trim', explode(',', (string) env('CORS_ALLOW_ORIGINS', '*'))))),
                ],
                'log' => [
                    'level' => (string) env('LOG_LEVEL', 'debug'),
                    'file' => storage_path('logs/app.log'),
                ],
            ];
        }

        if ($key === null) {
            return $config;
        }

        $current = $config;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return $default;
            }
            $current = $current[$segment];
        }

        return $current;
    }
}
