<?php

declare(strict_types=1);

namespace App\Entities;

use App\Entities\ActivityRepository;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;

/**
 * ============================================================
 * 活动实体（Doctrine ORM 版）
 * ============================================================
 *
 * 对应 Java / JPA：@Entity + @Table(name="activities") + @Column
 *
 * 时间字段用 datetime_immutable（不可变），比 datetime 更安全：
 *   - DateTime 是"可变"的：对象可以 setHour / modify 改变时间
 *   - DateTimeImmutable 每次操作返回新对象，避免"实体被无意间改时间"
 *
 * nullable 字段（location / end_time / description）用 string|null，
 * Doctrine 会自动在 SQL 里读写 NULL。
 */
#[ORM\Entity(repositoryClass: ActivityRepository::class)]
#[ORM\Table(name: 'activities')]
#[ORM\Index(name: 'idx_activities_start', columns: ['start_time'])]
#[ORM\HasLifecycleCallbacks]
#[OA\Schema(
    schema: 'Activity',
    title: '活动',
    description: '活动实体；schema 直接定义在 Entity 上（类 playground 的 DTO 注解方式）',
)]
class Activity
{
    #[ORM\Id]
    #[ORM\Column(type: 'bigint', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[OA\Property(type: 'integer', format: 'int64', example: 1)]
    private int $id;

    /** 活动名称（VARCHAR 120） */
    #[ORM\Column(type: 'string', length: 120)]
    #[OA\Property(type: 'string', example: 'PHP 8.5 技术分享')]
    private string $title;

    /** 地点（VARCHAR 120，可空） */
    #[ORM\Column(type: 'string', length: 120, nullable: true)]
    #[OA\Property(type: 'string', example: '线上直播', nullable: true)]
    private ?string $location = null;

    /** 开始时间（DATETIME，必填） */
    #[ORM\Column(name: 'start_time', type: 'datetime_immutable')]
    #[OA\Property(type: 'string', format: 'date-time', example: '2026-10-15 19:00:00')]
    private \DateTimeInterface $startTime;

    /** 结束时间（DATETIME，可空） */
    #[ORM\Column(name: 'end_time', type: 'datetime_immutable', nullable: true)]
    #[OA\Property(type: 'string', format: 'date-time', example: '2026-10-15 21:00:00', nullable: true)]
    private ?\DateTimeInterface $endTime = null;

    /** 活动描述（TEXT，可空） */
    #[ORM\Column(type: 'text', nullable: true)]
    #[OA\Property(type: 'string', example: '深入讲解 PHP 8.5 新特性', nullable: true)]
    private ?string $description = null;

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

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;
        return $this;
    }

    public function getStartTime(): \DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): self
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeInterface $endTime): self
    {
        $this->endTime = $endTime;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
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
    // 生命周期回调
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
