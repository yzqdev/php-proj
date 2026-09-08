<?php

declare(strict_types=1);

namespace Yzqde\Playground\Database\Migrations;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Table;

/**
 * 创建 persons 人员表
 *
 * up / down 签名与 doctrine/migrations 官方框架兼容,新增迁移时沿用此格式即可:
 *   文件名 时间戳_下划线描述.php,类名描述部分转 PascalCase,
 *   类内只实现 up(AbstractSchemaManager) 与 down(AbstractSchemaManager)。
 */
final class CreatePersonsTable
{
    private const TABLE = 'persons';

    public function up(AbstractSchemaManager $schema): void
    {
        $table = new Table(self::TABLE);
        $table->addColumn('id', 'bigint', ['unsigned' => true, 'autoincrement' => true])
            ->setComment('主键');
        $table->addColumn('name', 'string', ['length' => 100])
            ->setNotnull(true)
            ->setComment('姓名');
        $table->addColumn('email', 'string', ['length' => 255])
            ->setNotnull(true)
            ->setComment('邮箱');
        $table->addColumn('phone', 'string', ['length' => 20])
            ->setNotnull(false)
            ->setComment('手机号,允许为空');
        $table->addColumn('created_at', 'datetime')
            ->setNotnull(true)
            ->setComment('创建时间');
        $table->addColumn('updated_at', 'datetime')
            ->setNotnull(false)
            ->setComment('最近更新时间,未更新过为空');
        $table->setPrimaryKey(['id']);
        // email 与创建时间是列表与检索的高频字段,显式建索引避免全表扫描
        $table->addIndex(['email'], 'idx_persons_email');
        $table->addIndex(['created_at'], 'idx_persons_created_at');

        $schema->createTable($table);
    }

    public function down(AbstractSchemaManager $schema): void
    {
        // dropTable 对不存在的表会直接抛异常,先判断存在性,让回滚在人工介入后仍可用
        if ($schema->tablesExist([self::TABLE])) {
            $schema->dropTable(self::TABLE);
        }
    }
}
