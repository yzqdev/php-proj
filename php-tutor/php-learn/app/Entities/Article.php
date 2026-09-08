<?php

declare(strict_types=1);

namespace App\Entities;

use App\Entities\ArticleRepository;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;

/**
 * ============================================================
 * 文章实体（Doctrine ORM 版）
 * ============================================================
 *
 * 对应 Java / JPA：
 *   - #[ORM\Entity]    → @Entity
 *   - #[ORM\Table]     → @Table(name="articles")
 *   - #[ORM\Column]    → @Column(...)
 *   - #[ORM\HasLifecycleCallbacks] → @EntityListeners(true)
 *
 * 与旧 BaseModel 的差异：
 *   - BaseModel 返回关联数组（['title' => 'x']）
 *   - 这里是对象（$article->getTitle()），Unit of Work 事务结束自动 diff
 *
 * PHP 8.x 特性：
 *   - readonly 属性：id 由数据库生成，写入后只读
 *   - DateTimeImmutable：不可变时间对象（比 DateTime 更安全）
 */
#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\Table(name: 'articles')]
#[ORM\Index(name: 'idx_articles_category', columns: ['category'])]
#[ORM\Index(name: 'idx_articles_status', columns: ['status'])]
#[ORM\Index(name: 'idx_articles_created', columns: ['created_at'])]
#[ORM\HasLifecycleCallbacks]
#[OA\Schema(
    schema: 'Article',
    title: '文章',
    description: '文章实体；schema 直接定义在 Entity 上（类 playground 的 DTO 注解方式）',
)]
class Article
{
    /** 主键（bigint unsigned，自增） */
    #[ORM\Id]
    #[ORM\Column(type: 'bigint', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[OA\Property(type: 'integer', format: 'int64', example: 1)]
    private int $id;

    /** 标题（VARCHAR 120） */
    #[ORM\Column(type: 'string', length: 120)]
    #[OA\Property(type: 'string', example: 'PHP 8.5 新特性')]
    private string $title;

    /** 正文（TEXT） */
    #[ORM\Column(type: 'text')]
    #[OA\Property(type: 'string', example: 'PHP 8.5 引入了...')]
    private string $body;

    /** 分类：php / java / db / other（varchar 20，默认 other） */
    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'other'])]
    #[OA\Property(type: 'string', example: 'php', enum: ['php', 'java', 'db', 'other'])]
    private string $category = 'other';

    /** 状态：draft / published（varchar 20，默认 published） */
    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'published'])]
    #[OA\Property(type: 'string', example: 'published', enum: ['draft', 'published'])]
    private string $status = 'published';

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: true)]
    #[OA\Property(type: 'string', format: 'date-time', example: '2026-09-01 09:00:00')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable', nullable: true)]
    #[OA\Property(type: 'string', format: 'date-time', example: '2026-09-06 19:00:00')]
    private ?\DateTimeInterface $updatedAt = null;

    // ------------------------------------------------------------------
    // Getters / Setters
    // ------------------------------------------------------------------

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;
        return $this;
    }

    /**
     * 分类中文标签（swagger-php 会自动扫描此 getter，补充到 schema）
     *
     * @OA\Property 在 getter 上声明，因为 categoryLabel 不是数据库列，
     * 而是由 Resource 层 / getter 计算的虚拟字段。
     */
    #[OA\Property(property:"aaa", description: '分类中文标签', type: 'string', example: 'PHP')]
    public function getCategoryLabel(): string
    {
        return match ($this->category) {
            'php'   => 'PHP',
            'java'  => 'Java',
            'db'    => 'Database',
            default => 'Other',
        };
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    // ------------------------------------------------------------------
    // 生命周期回调（等价 JPA 的 @PrePersist / @PreUpdate）
    // DB 的 created_at/updated_at 是 NOT NULL，Doctrine 插入时必须显式赋值
    // （不会自动用列 DEFAULT），所以在这里补齐时间戳。
    // ------------------------------------------------------------------

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $now = new \DateTimeImmutable();
        $this->createdAt ??= $now;
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
