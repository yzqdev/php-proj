<?php

declare(strict_types=1);

namespace Yzqde\Playground;

/**
 * 应用配置:统一从环境变量读取,业务代码不允许直接触碰 $_ENV / getenv
 */
final readonly class Settings
{
    public function __construct(
        public string $appName,
        public bool $debug,
        public int $maxUploadSize,
        public string $storagePath,
        public bool $logViewAllowAny,
        public string $dbHost,
        public int $dbPort,
        public string $dbName,
        public string $dbUser,
        public string $dbPassword,
        public string $dbCharset,
    ) {
    }

    /**
     * @param string $baseDir 项目根目录,用于解析相对的存储路径
     */
    public static function fromEnv(string $baseDir): self
    {
        $appName = self::env('APP_NAME', '简单图床');
        $maxUploadSize = (int)self::env('UPLOAD_MAX_SIZE', (string)(10 * 1024 * 1024));
        $storagePath = self::env('STORAGE_PATH', 'storage/uploads');
        $debug = filter_var(self::env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOL);
        $logViewAllowAny = (bool)filter_var(self::env('LOG_VIEW_ALLOW_ANY', '0'), FILTER_VALIDATE_BOOL);
        $dbHost = self::env('DB_HOST', '127.0.0.1');
        $dbPort = (int)self::env('DB_PORT', '3306');
        $dbName = self::env('DB_NAME', '');
        $dbUser = self::env('DB_USER', 'root');
        $dbPassword = self::env('DB_PASSWORD', '');
        $dbCharset = self::env('DB_CHARSET', 'utf8mb4');

        // 相对路径以项目根目录为基准;绝对路径(含 Windows 盘符)原样使用
        $isAbsolutePath = str_starts_with($storagePath, '/')
            || (bool)preg_match('/^[A-Za-z]:/', $storagePath);
        $storagePath = $isAbsolutePath ? $storagePath : $baseDir . '/' . $storagePath;

        return new self(
            $appName,
            $debug,
            $maxUploadSize,
            $storagePath,
            $logViewAllowAny,
            $dbHost,
            $dbPort,
            $dbName,
            $dbUser,
            $dbPassword,
            $dbCharset,
        );
    }

    private static function env(string $key, string $default): string
    {
        $value = $_ENV[$key] ?? getenv($key);
        return is_string($value) && $value !== '' ? $value : $default;
    }
}
