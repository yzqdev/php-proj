<?php

declare(strict_types=1);

namespace Yzqde\Fox\Util;

use Predis\Client;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * 基于 Redis 的 PSR-6 CacheItemPool 实现。
 *
 * 供 Doctrine ORM 配置元数据/查询缓存使用。
 */
class RedisCachePool implements CacheItemPoolInterface
{
    private Client $redis;
    private string $prefix;

    /** @var array<string, CacheItem> 内存暂存（flush 前生效） */
    private array $deferred = [];

    public function __construct(Client $redis, string $prefix = 'fox:doctrine:')
    {
        $this->redis = $redis;
        $this->prefix = $prefix;
    }

    public function getItem(string $key): CacheItemInterface
    {
        if (isset($this->deferred[$key])) {
            return $this->deferred[$key];
        }

        $value = $this->redis->get($this->prefix . $key);

        $item = new CacheItem($key, null, false);

        if ($value !== null) {
            $item = new CacheItem($key, unserialize($value), true);
        }

        return $item;
    }

    /**
     * @return CacheItemInterface[]
     */
    public function getItems(array $keys = []): array
    {
        return array_combine($keys, array_map(fn(string $k) => $this->getItem($k), $keys));
    }

    public function hasItem(string $key): bool
    {
        if (isset($this->deferred[$key])) {
            return $this->deferred[$key]->isHit();
        }

        return $this->redis->exists($this->prefix . $key) > 0;
    }

    public function clear(): bool
    {
        $this->deferred = [];
        $keys = $this->redis->keys($this->prefix . '*');

        if (!empty($keys)) {
            $this->redis->del($keys);
        }

        return true;
    }

    public function deleteItem(string $key): bool
    {
        unset($this->deferred[$key]);
        $this->redis->del([$this->prefix . $key]);

        return true;
    }

    public function deleteItems(array $keys): bool
    {
        $deleted = true;
        foreach ($keys as $key) {
            $deleted = $this->deleteItem($key) && $deleted;
        }

        return $deleted;
    }

    public function save(CacheItemInterface $item): bool
    {
        unset($this->deferred[$item->getKey()]);

        return $this->persistItem($item);
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->deferred[$item->getKey()] = $item;

        return true;
    }

    public function commit(): bool
    {
        $ok = true;
        foreach ($this->deferred as $item) {
            $ok = $this->persistItem($item) && $ok;
        }
        $this->deferred = [];

        return $ok;
    }

    private function persistItem(CacheItemInterface $item): bool
    {
        $ttl = $item->expiresAfter();

        if ($ttl !== null && $ttl instanceof \DateInterval) {
            $seconds = (int) $ttl->format('%s');
        } elseif ($ttl !== null && is_int($ttl)) {
            $seconds = $ttl;
        } else {
            $seconds = 0;
        }

        $data = serialize($item->get());
        $key = $this->prefix . $item->getKey();

        if ($seconds > 0) {
            $this->redis->setex($key, $seconds, $data);
        } else {
            $this->redis->set($key, $data);
        }

        return true;
    }
}
