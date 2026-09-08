<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * 用户实体
 *
 * 对应数据库 users 表，使用 Doctrine ORM 管理。
 * 不包含业务逻辑，仅作为数据载体。
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User
{
    /** 用户 ID（自增主键） */
    #[ORM\Id]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    /** 用户姓名 */
    #[ORM\Column(type: 'string', length: 100, options: ['default' => ''])]
    private string $name = '';

    /** 用户邮箱（唯一约束） */
    #[ORM\Column(type: 'string', length: 150, unique: true, options: ['default' => ''])]
    private string $email = '';

    /** 创建时间 */
    #[ORM\Column(type: 'datetime', nullable: false)]
    private DateTime $createdAt;

    /** 更新时间 */
    #[ORM\Column(type: 'datetime', nullable: false)]
    private DateTime $updatedAt;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * 更新实体字段（由 Doctrine 在 persist 前自动调用）
     */
    #[ORM\PreUpdate]
    public function updateTimestamps(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * 转换为数组（用于 JSON 序列化）
     *
     * @return array{ id: int|null, name: string, email: string, created_at: string, updated_at: string }
     */
    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}