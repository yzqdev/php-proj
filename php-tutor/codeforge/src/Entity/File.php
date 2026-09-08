<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * 文件记录实体
 *
 * 对应数据库 files 表，使用 Doctrine ORM 管理。
 * 真实文件保存在 var/uploads 目录中（不在 Web 根目录内），
 * 本实体只持有其相对路径，磁盘映射由 FileStorage 负责。
 */
#[ORM\Entity(repositoryClass: FileRepository::class)]
#[ORM\Table(name: 'files')]
class File
{
    /** 文件记录 ID（自增主键） */
    #[ORM\Id]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    /** 上传时的原始文件名 */
    #[ORM\Column(type: 'string', length: 255, options: ['default' => ''])]
    private string $originalName = '';

    /** 相对上传根目录的存储路径，例如 2026/09/xxxx.jpg */
    #[ORM\Column(type: 'string', length: 255, options: ['default' => ''])]
    private string $path = '';

    /** 实际识别到的 MIME 类型 */
    #[ORM\Column(type: 'string', length: 150, options: ['default' => ''])]
    private string $mime = '';

    /** 文件字节数 */
    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    private int $size = 0;

    /** 创建时间 */
    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTime $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function setOriginalName(string $originalName): void
    {
        $this->originalName = $originalName;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getMime(): string
    {
        return $this->mime;
    }

    public function setMime(string $mime): void
    {
        $this->mime = $mime;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * 从存储路径派生文件扩展名（小写，不含点）
     */
    public function getExtension(): string
    {
        return strtolower(pathinfo($this->path, PATHINFO_EXTENSION));
    }

    /**
     * 转换为数组（用于 JSON 序列化）
     *
     * @return array{ id: int|null, original_name: string, extension: string, mime: string, size: int, created_at: string }
     */
    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'original_name' => $this->originalName,
            'extension'     => $this->getExtension(),
            'mime'          => $this->mime,
            'size'          => $this->size,
            'created_at'    => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}