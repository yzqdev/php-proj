<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * 配置读取器（单例 + 惰性加载）
 *
 * 设计意图：
 *   - 全局只有一个配置数组，避免每个类都 require 一遍配置文件；
 *   - 用静态方法暴露，调用点写起来像 Java 的 Spring 容器 getBean：
 *         Config::get('db.host')
 *
 * PHP 8.x 特性演示：
 *   - readonly 属性：只在构造时赋值一次，之后任何修改都会抛
 *     TypeError（比 Java 里靠"约定 + final"更硬）。
 *   - ?? 空合并运算符：Java 里对应 Optional.or()，比 ? : 更干净。
 */
final class Config
{
    /** @var array<string, mixed>|null */
    private static ?array $config = null;

    private function __construct()
    {
        // 私有构造器：单例，禁止 new
    }

    /** 读取整份配置（带缓存） */
    public static function all(): array
    {
        if (self::$config === null) {
            $path = dirname(__DIR__, 2) . '/config/config.php';
            if (!is_file($path)) {
                // 首次运行友好提示，避免一句 "Failed opening..." 让人摸不着头脑
                throw new \RuntimeException(
                    "找不到配置文件 {$path}，请先执行：Copy-Item config\\config.example.php config\\config.php"
                );
            }
            self::$config = require $path;
        }
        return self::$config;
    }

    /**
     * 用 "db.host" 这样的点号路径取值（类似 Java 的 Map.getOrDefault / 属性路径）
     *
     * @param mixed $default 取不到时的默认值
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = self::all();
        // explode 把点号路径切成数组：'db.host' => ['db', 'host']
        foreach (explode('.', $key) as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }
        return $value;
    }
}
