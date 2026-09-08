<?php

declare(strict_types=1);

namespace Yzqde\Playground\Service;

use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;
use RuntimeException;
use Yzqde\Playground\Dto\Person;

/**
 * 人员管理服务（Doctrine DBAL 持久化）
 *
 * 对比 Java / Spring Boot：
 *   - 等价于 Spring 的 @Service PersonService
 *   - 注入 DBAL Connection ↔ Spring 注入 EntityManager
 *   - Controller 只做参数提取和响应组装，业务逻辑全在 Service
 *   - 全部走参数绑定或查询构造器,表名与列名为类内常量,无外部输入拼接
 */
final class PersonService
{
    private const TABLE = 'persons';
    private const ALIAS = 'p';

    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * 人员列表（按 ID 倒序）
     *
     * @return Person[]
     */
    public function list(): array
    {
        $rows = $this->select()->orderBy(self::ALIAS . '.id', 'DESC')->fetchAllAssociative();

        return array_map(fn(array $row) => $this->toPerson($row), $rows);
    }

    /**
     * 按 ID 查找
     */
    public function find(int $id): ?Person
    {
        $row = $this->select()
            ->where(self::ALIAS . '.id = :id')
            ->setParameter('id', $id, ParameterType::INTEGER)
            ->setMaxResults(1)
            ->fetchAssociative();

        return $row === false ? null : $this->toPerson($row);
    }

    /**
     * 新增人员
     */
    public function create(string $name, string $email, ?string $phone = null): Person
    {
        $this->connection->insert(self::TABLE, [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'created_at' => new DateTimeImmutable(),
        ], [
            'created_at' => Types::DATETIME_IMMUTABLE,
        ]);

        $created = $this->find((int)$this->connection->lastInsertId());
        if ($created === null) {
            throw new RuntimeException('人员写入后未能读回,请检查数据库状态');
        }

        return $created;
    }

    /**
     * 更新人员
     */
    public function update(int $id, string $name, string $email, ?string $phone = null): ?Person
    {
        $affected = (int)$this->connection->update(self::TABLE, [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'updated_at' => new DateTimeImmutable(),
        ], [
            'id' => $id,
        ], [
            'id' => ParameterType::INTEGER,
            'updated_at' => Types::DATETIME_IMMUTABLE,
        ]);

        return $affected === 0 ? null : $this->find($id);
    }

    /**
     * 删除人员
     */
    public function delete(int $id): bool
    {
        return (int)$this->connection->delete(self::TABLE, ['id' => $id], ['id' => ParameterType::INTEGER]) > 0;
    }

    /**
     * 统一的查询入口:固定投影列,调用方只需追加 where / orderBy
     */
    private function select(): QueryBuilder
    {
        return $this->connection->createQueryBuilder()
            ->select(
                self::ALIAS . '.id',
                self::ALIAS . '.name',
                self::ALIAS . '.email',
                self::ALIAS . '.phone',
                self::ALIAS . '.created_at',
            )
            ->from(self::TABLE, self::ALIAS);
    }

    /**
     * 数据库行转 DTO
     *
     * @param array<string, mixed> $row
     */
    private function toPerson(array $row): Person
    {
        // 库里是 MySQL 的 'Y-m-d H:i:s' 格式,转回 ISO 8601 以保持 API 输出与 OpenAPI 声明一致
        $createdAt = DateTime::createFromFormat('Y-m-d H:i:s', (string)$row['created_at']);
        if ($createdAt === false) {
            throw new RuntimeException('人员数据损坏:创建时间无法解析');
        }

        return new Person(
            id: (int)$row['id'],
            name: (string)$row['name'],
            email: (string)$row['email'],
            phone: $row['phone'] === null ? null : (string)$row['phone'],
            createdAt: $createdAt->format(DATE_ATOM),
        );
    }
}
