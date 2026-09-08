<?php
declare(strict_types=1);

/**
 * 数据库连接配置,供 App\Database\Manager(Eloquent Capsule)使用。
 * 依赖 config/app.php 中的 env() 辅助函数。
 */

if (!function_exists('database_config')) {
    /**
     * 返回 Eloquent Capsule 可识别的连接配置数组。
     * sqlite 驱动与 mysql/pgsql 的参数结构不同,这里做归一化。
     */
    function database_config(): array
    {
        $driver = (string) env('DB_CONNECTION', 'mysql');

        if ($driver === 'sqlite') {
            return [
                'driver' => 'sqlite',
                'database' => (string) env('DB_DATABASE', ':memory:'),
                'prefix' => '',
                'foreign_key_constraints' => true,
            ];
        }

        return [
            'driver' => $driver,
            'host' => (string) env('DB_HOST', '127.0.0.1'),
            'port' => (int) env('DB_PORT', '3306'),
            'database' => (string) env('DB_DATABASE', 'blog'),
            'username' => (string) env('DB_USERNAME', 'root'),
            'password' => (string) env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
        ];
    }
}
