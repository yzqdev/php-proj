<?php

declare(strict_types=1);

namespace Yzqde\DouyinSpider\Model;

use Doctrine\ORM\Mapping as ORM;
use Yzqde\DouyinSpider\Repository\ArticleRepository;

#[ORM\Entity(repositoryClass: \Yzqde\DouyinSpider\Repository\ArticleRepository::class)]
#[ORM\Table(name: 'articles')]
#[ORM\HasLifecycleCallbacks]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint', options: ['unsigned' => true])]
    /** @phpstan-ignore property.onlyRead */
    private int $id;

    #[ORM\Column(type: 'string', length: 200)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $summary = null;

    #[ORM\Column(name: 'view_count', type: 'integer')]
    private int $viewCount = 0;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'author_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?User $author = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function touchUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): void
    {
        $this->summary = $summary;
    }

    public function getViewCount(): int
    {
        return $this->viewCount;
    }

    public function incrementViewCount(): void
    {
        $this->viewCount++;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): void
    {
        $this->author = $author;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
