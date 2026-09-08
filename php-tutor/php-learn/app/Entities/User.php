<?php

declare(strict_types=1);

namespace App\Entities;

use App\Entities\RefreshToken;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;

/**
 * ============================================================
 * 用户实体（Doctrine ORM 版）
 * ============================================================
 *
 * 对比 Java / JPA：
 *   - @ORM\Entity  → @Entity
 *   - @ORM\Table  → @Table(name="users")
 *   - @ORM\Column → @Column(...)
 *   - @ORM\OneToMany → @OneToMany
 *
 * 与手写 BaseModel 的差异：
 *   - 手写版：静态方法 Article::find(1) 直接调，无"实例"概念；
 *   - Doctrine 版：由 EntityManager 管理，用 $em->getRepository(User::class)->find(1)；
 *     但实体本身是"贫血 POJO"，与手写版的数组返回值不同（这里是对象）。
 *
 * 关键 Doctrine 概念：
 *   - Identity Map：同一个 id 在一次请求里只查一次，重复 get 会命中缓存；
 *   - Unit of Work：事务结束时自动 diff，决定 INSERT / UPDATE / DELETE；
 *   - 延迟加载：关联属性默认是 Proxy，真正访问时才发 SQL。
 *
 * PHP 8.x 特性：
 *   - readonly 属性：id 由数据库生成，写入后只读；
 *   - 构造器属性提升：把参数直接提升为属性。
 */
// ORM 3.x：索引用独立的 #[ORM\Index] / #[ORM\UniqueConstraint] 声明在类上，
// #[ORM\Table] 的 indexes/uniqueConstraints 参数已废弃（4.0 移除）
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\Index(name: 'idx_users_role', columns: ['role'])]
#[ORM\UniqueConstraint(name: 'uk_users_email', columns: ['email'])]
#[ORM\UniqueConstraint(name: 'uk_users_username', columns: ['username'])]
#[ORM\HasLifecycleCallbacks] // 启用生命周期回调（等价 JPA 的 @EntityListeners(true)）
#[OA\Schema(
    schema: 'User',
    title: '用户',
    description: '用户实体；schema 直接定义在 Entity 上（password 字段不暴露给 API）',
)]
class User
{
    /** @ORM\Id @ORM\Column(type="bigint", options:{"unsigned":true}) @ORM\GeneratedValue */
    #[ORM\Id]
    #[ORM\Column(type: 'bigint', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[OA\Property(type: 'integer', format: 'int64', example: 1)]
    private int $id;

    /** 用户名（登录名） */
    #[ORM\Column(type: 'string', length: 50)]
    #[OA\Property(type: 'string', example: 'admin')]
    private string $username;

    /** 邮箱（唯一） */
    #[ORM\Column(type: 'string', length: 100, unique: true)]
    #[OA\Property(type: 'string', format: 'email', example: 'admin@php-learn.local')]
    private string $email;

    /** bcrypt 哈希（password_hash() 生成，24 字符盐 + 密文） */
    #[ORM\Column(type: 'string', length: 255)]
    private string $password;

    /** 角色：user / admin */
    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'user'])]
    #[OA\Property(type: 'string', example: 'admin', enum: ['admin', 'user'])]
    private string $role = 'user';

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: true)]
    #[OA\Property(type: 'string', format: 'date-time', example: '2026-09-01 09:00:00')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * 一对多：一个用户可以有多条 Refresh Token
     *
     * owning side 是 RefreshToken.user，这里只是反向侧（inverse），
     * 用 $em->getRepository(RefreshToken::class)->findBy(['user' => $user]) 也能查。
     */
    #[ORM\OneToMany(
        targetEntity: RefreshToken::class,
       mappedBy: 'user',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    private Collection $refreshTokens;

    public function __construct()
    {
        // 初始化集合：避免访问时 null 报错
        $this->refreshTokens = new \Doctrine\Common\Collections\ArrayCollection();
    }

    // ------------------------------------------------------------------
    // Getters（Doctrine 不强制提供 setter，由 Unit of Work 直接写属性）
    // ------------------------------------------------------------------

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

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

    /** @return Collection<int, RefreshToken> */
    public function getRefreshTokens(): Collection
    {
        return $this->refreshTokens;
    }

    /** 判断是否是管理员（PHP 8 match 表达式的经典用法） */
    public function isAdmin(): bool
    {
        return match ($this->role) {
            'admin' => true,
            default => false,
        };
    }

    /** 校验密码是否匹配（密码永远不要明文比较） */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
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
