<?php

declare(strict_types=1);

namespace Service\Redis;

use Predis\Client;
use Predis\ClientInterface;

/**
 * Predis 客户端工厂：统一 Redis 连接配置。
 * 默认值对应本机开发环境（Redis 8.2.1 Windows 服务，requirepass 与 MySQL 同策略），
 * 生产环境通过环境变量覆盖：REDIS_HOST / REDIS_PORT / REDIS_PASSWORD / REDIS_DATABASE。
 * 连接是惰性的：首次命令时才真正建立 TCP 连接。
 */
final class RedisClient
{
    public function create(): ClientInterface
    {
        return new Client([
            'scheme' => 'tcp',
            'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
            'port' => (int) (getenv('REDIS_PORT') ?: 6379),
            'password' => getenv('REDIS_PASSWORD') ?: '123456',
            'database' => (int) (getenv('REDIS_DATABASE') ?: 0),
        ]);
    }
}
