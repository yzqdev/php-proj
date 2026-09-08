<?php

declare(strict_types=1);

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * ============================================================
 * Refresh Token 实体（Doctrine ORM 版）
 * ============================================================
 *
 * 对应 docs/sql/alter_add_refresh_tokens.sql 建的 refresh_tokens 表。
 *
 * 为什么 Refresh Token 存表而不是只用 JWT？
 *   - JWT 是"无状态签名"，服务端无法主动撤销（过期前一直有效）；
 *   - 密码泄露 / 账号被盗 / 用户改密码后，旧 token 应立刻失效；
 *   - 解法：Access Token 短过期（15 分钟）+ Refresh Token 存表（7 天）。
 *
 * 对比 Java / Spring Security：
 *   - OAuth2 RefreshToken 存 DB / Redis 都是标准做法；
 *   - Spring Data JPA 里对应 @Entity + @OneToOne / @ManyToOne。
 *
 * Doctrine 特点：
 *   - token 是 CHAR(64)，存 32 字节 hex；
 *   - expires_at 是 DATETIME，由 TokenService 计算后写入。
 */
// ORM 3.x：索引用独立的 #[ORM\Index] / #[ORM\UniqueConstraint] 声明在类上，
// #[ORM\Table] 的 indexes/uniqueConstraints 参数已废弃（4.0 移除）
#[ORM\Entity(repositoryClass: RefreshTokenRepository::class)]
#[ORM\Table(name: 'refresh_tokens')]
#[ORM\Index(name: 'idx_refresh_tokens_user_id', columns: ['user_id'])]
#[ORM\Index(name: 'idx_refresh_tokens_expires_at', columns: ['expires_at'])]
#[ORM\UniqueConstraint(name: 'uk_refresh_tokens_token', columns: ['token'])]
#[ORM\HasLifecycleCallbacks] // 启用生命周期回调，created_at 由 PrePersist 补齐
class RefreshToken
{
    /** @ORM\Id @ORM\Column(type="bigint", options:{"unsigned":true}) @ORM\GeneratedValue */
    #[ORM\Id]
    #[ORM\Column(type: 'bigint', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    private int $id;

    /** 关联的用户（多对一：多条 token 属于一个用户；inversedBy 对应 User#refreshTokens） */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'refreshTokens')]
    #[ORM\JoinColumn(name: 'user_id', nullable: false)]
    private User $user;

    /** 随机 32 字节 hex（64 字符） */
    #[ORM\Column(type: 'string', length: 64, unique: true)]
    private string $token;

    /** 过期时间 */
    #[ORM\Column(name: 'expires_at', type: 'datetime_immutable')]
    private \DateTimeInterface $expiresAt;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
    }

    // ------------------------------------------------------------------
    // Getters / Setters
    // ------------------------------------------------------------------

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getExpiresAt(): \DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeInterface $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /** 判断 token 是否已过期 */
    public function isExpired(): bool
    {
        return $this->expiresAt < new \DateTimeImmutable();
    }

    /** 插入前补 created_at（DB 列是 NOT NULL，Doctrine 不会用列 DEFAULT） */
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt ??= new \DateTimeImmutable();
    }
}