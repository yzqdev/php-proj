<?php

declare(strict_types=1);

namespace Yzqde\Fox\Util;

/**
 * 简易文件缓存，基于 php://temp 实现 PSR-16 风格接口。
 *
 * 缓存文件存储在 sys_get_temp_dir()/fox-cache/ 下，
 * 每个 key 对应一个 JSON 文件，包含 data + expire 时间戳。
 */
class FileCache
{
    private string $directory;

    public function __construct(?string $directory = null)
    {
        $this->directory = $directory ?? FileConst::cacheDir() . '/fox-cache';
    }

    /**
     * 获取缓存值，过期或不存在返回 null。
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $path = $this->getPath($key);

        if (!is_file($path)) {
            return $default;
        }

        $content = file_get_contents($path);

        if ($content === false) {
            return $default;
        }

        $entry = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        if ($entry['expire'] > 0 && $entry['expire'] < time()) {
            @unlink($path);

            return $default;
        }

        return $entry['data'];
    }

    /**
     * 设置缓存值。
     *
     * @param int $ttl 过期秒数，0 = 永不过期
     */
    public function set(string $key, mixed $value, int $ttl = 60): bool
    {
        $path = $this->getPath($key);
        $dir = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $data = [
            'expire' => $ttl > 0 ? time() + $ttl : 0,
            'data' => $value,
        ];

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // 写入临时文件再 rename，避免并发读到半写文件
        $tmp = $path . '.tmp.' . getmypid();
        $ok = file_put_contents($tmp, $json) !== false;

        if ($ok) {
            $ok = rename($tmp, $path);
        }

        if (!$ok && is_file($tmp)) {
            @unlink($tmp);
        }

        return $ok;
    }

    /**
     * 删除缓存条目。
     */
    public function delete(string $key): bool
    {
        $path = $this->getPath($key);

        if (!is_file($path)) {
            return true;
        }

        return @unlink($path);
    }

    /**
     * 清除所有缓存文件。
     */
    public function clear(): bool
    {
        if (!is_dir($this->directory)) {
            return true;
        }

        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        $ok = true;

        foreach ($it as $file) {
            if ($file->isDir()) {
                $ok = rmdir($file->getPathname()) && $ok;
            } else {
                $ok = @unlink($file->getPathname()) && $ok;
            }
        }

        return $ok;
    }

    /**
     * key → 文件路径的映射，key 做 MD5 以保证文件名安全。
     */
    private function getPath(string $key): string
    {
        return $this->directory . '/' . md5($key) . '.json';
    }
}
