<?php

declare(strict_types=1);

namespace FoxMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260909000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '创建 users 表';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('users');
        $table->addColumn('id', 'integer', [
            'autoincrement' => true,
            'notnull' => true,
        ]);
        $table->addColumn('name', 'string', [
            'length' => 100,
            'notnull' => true,
        ]);
        $table->addColumn('email', 'string', [
            'length' => 255,
            'notnull' => true,
        ]);
        $table->addColumn('created_at', 'datetime', [
            'notnull' => true,
            'default' => 'CURRENT_TIMESTAMP',
        ]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['email'], 'uniq_users_email');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('users');
    }
}
