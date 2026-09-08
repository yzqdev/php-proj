<?php
declare(strict_types=1);

namespace App\Database;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

/**
 * Eloquent Capsule 封装,在容器中注册为单例。
 * 业务代码一律通过本类访问数据库,禁止直接操作 PDO 或拼接 SQL。
 */
final class Manager
{
    private readonly Capsule $capsule;

    private bool $booted = false;

    /**
     * @param array<string, mixed> $config Eloquent 连接配置(见 config/database.php)
     */
    public function __construct(private readonly array $config)
    {
        $this->capsule = new Capsule();
    }

    /** 注册连接、设为全局并启动 Eloquent;重复调用无副作用。 */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        $this->capsule->addConnection($this->config);
        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();
        $this->booted = true;
    }

    public function getCapsule(): Capsule
    {
        return $this->capsule;
    }

    public function connection(): Connection
    {
        return $this->capsule->getConnection();
    }

    public function schema(): SchemaBuilder
    {
        return $this->capsule->getConnection()->getSchemaBuilder();
    }

    public function table(string $table): Builder
    {
        return $this->capsule->getConnection()->table($table);
    }
}
