<?php

declare(strict_types=1);

namespace Yzqde\Fox\Util;

use Predis\Client;

/**
 * 基于 Redis 的缓存实现，接口与 FileCache 保持一致。
 *
 * 缓存数据以 JSON 序列化后存入 Redis，过期由 Redis 自动处理（EX 参数）。
 * key 统一加 fox: 前缀，避免与业务 key 冲突。
 */
class RedisCache
{
    private Client $redis;
    private string $prefix;

    public function __construct(Client $redis, string $prefix = 'fox:cache:')
    {
        $this->redis = $redis;
        $this->prefix = $prefix;
    }

    /**
     * 获取缓存值，过期或不存在返回 null。
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $raw = $this->redis->get($this->prefix . $key);

        if ($raw === null) {
            return $default;
        }

        $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

        // 兼容旧格式（带 expire 字段的 FileCache 格式）
        if (is_array($decoded) && array_key_exists('expire', $decoded)) {
            return $decoded['data'] ?? $default;
        }

        return $decoded;
    }

    /**
     * 设置缓存值。
     *
     * @param int $ttl 过期秒数，0 = 永不过期
     */
    public function set(string $key, mixed $value, int $ttl = 60): bool
    {
        $payload = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($ttl > 0) {
            $this->redis->setex($this->prefix . $key, $ttl, $payload);
        } else {
            $this->redis->set($this->prefix . $key, $payload);
        }

        return true;
    }

    /**
     * 删除缓存条目。
     */
    public function delete(string $key): bool
    {
        return (bool) $this->redis->del([$this->prefix . $key]);
    }

    /**
     * 清除所有 fox:cache: 前缀的缓存。
     */
    public function clear(): bool
    {
        $keys = $this->redis->keys($this->prefix . '*');

        if (empty($keys)) {
            return true;
        }

        $this->redis->del($keys);

        return true;
    }
}
