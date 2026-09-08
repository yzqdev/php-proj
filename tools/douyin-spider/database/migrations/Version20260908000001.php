<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * 初始化基础表结构：users、articles
 */
final class Version20260908000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '创建用户表和文章表';
    }

    public function up(Schema $schema): void
    {
        // users 表
        $this->addSql('
            CREATE TABLE users (
                id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
                username VARCHAR(50) NOT NULL,
                email VARCHAR(100) NOT NULL,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(20) NOT NULL DEFAULT \'user\',
                created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\',
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME ON users (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON users (email)');

        // articles 表
        $this->addSql('
            CREATE TABLE articles (
                id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
                author_id BIGINT UNSIGNED DEFAULT NULL,
                title VARCHAR(200) NOT NULL,
                content LONGTEXT NOT NULL,
                summary VARCHAR(500) DEFAULT NULL,
                view_count INT NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\',
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('CREATE INDEX IDX_ARTICLES_AUTHOR ON articles (author_id)');
        $this->addSql('CREATE INDEX IDX_ARTICLES_CREATED ON articles (created_at)');
        $this->addSql('ALTER TABLE articles ADD CONSTRAINT FK_23A07E3DF675F31B FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE articles DROP FOREIGN KEY FK_23A07E3DF675F31B');
        $this->addSql('DROP TABLE articles');
        $this->addSql('DROP TABLE users');
    }
}
