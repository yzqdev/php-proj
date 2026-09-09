<?php

declare(strict_types=1);

namespace Yzqde\Fox\Util;

use Predis\Client;

/**
 * Redis 连接工厂。
 *
 * 通过 create() 创建 Predis\Client 实例，
 * 连接参数从构造函数注入，支持自定义 host/port/password。
 */
class RedisConfig
{
    private string $host;
    private int $port;
    private string $password;
    private int $db;

    public function __construct(
        string $host = '127.0.0.1',
        int $port = 6379,
        string $password = '',
        int $db = 0,
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
        $this->db = $db;
    }

    /**
     * 创建并返回 Redis 客户端实例。
     */
    public function create(): Client
    {
        return new Client([
            'scheme' => 'tcp',
            'host' => $this->host,
            'port' => $this->port,
            'password' => $this->password,
            'database' => $this->db,
        ]);
    }
}
