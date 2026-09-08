<?php

declare(strict_types=1);

namespace Yzqde\Playground\Service;

use finfo;
use Random\RandomException;
use Yzqde\Playground\Dto\Image;
use Yzqde\Playground\Exception\ImageNotFoundException;
use Yzqde\Playground\Exception\InvalidUploadException;

/**
 * 图片存储服务:上传校验、入库、列表、删除、文件定位
 *
 * 本项目没有数据库,文件系统就是数据层,因此不设 Repository;
 * 元数据(原始文件名等)以「存储名 + .json」旁车文件形式保存。
 */
final class ImageService
{
    /** 允许的图片扩展名 => 外链输出时使用的 MIME */
    public const MIME_BY_EXT = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'bmp' => 'image/bmp',
    ];

    public function __construct(
        private readonly string $storagePath,
        private readonly int $maxSize,
    ) {
        if (!is_dir($this->storagePath) && !mkdir($this->storagePath, 0775, true)) {
            throw new \RuntimeException('存储目录不可用:' . $this->storagePath);
        }
    }

    /**
     * @return list<Image> 按上传时间倒序,只含图片(其余文件仍可下载)
     */
    public function list(): array
    {
        $images = [];
        foreach (scandir($this->storagePath) ?: [] as $name) {
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $path = $this->storagePath . '/' . $name;
            if ($name === '.' || $name === '..' || !isset(self::MIME_BY_EXT[$ext]) || !is_file($path)) {
                continue;
            }
            $images[] = new Image(
                name: $name,
                original: $this->readOriginalName($path),
                size: (int)filesize($path),
                time: (int)filemtime($path),
            );
        }
        usort($images, fn(Image $a, Image $b) => $b->time <=> $a->time);
        return $images;
    }

    /**
     * 校验并保存上传的图片,成功后写入元数据
     *
     * @throws InvalidUploadException|RandomException 校验未通过(类型、大小、错误码)
     */
    public function store(UploadedFile $upload): Image
    {
        if ($upload->error !== UPLOAD_ERR_OK) {
            throw new InvalidUploadException('上传失败,错误码:' . $upload->error . '(常见原因:超过大小限制)');
        }
        if ($upload->size > $this->maxSize) {
            throw new InvalidUploadException('文件超过大小限制(最大 ' . $this->formatSize($this->maxSize) . ')');
        }

        $ext = strtolower(pathinfo($upload->clientName ?? '', PATHINFO_EXTENSION));
        if (!isset(self::MIME_BY_EXT[$ext])) {
            throw new InvalidUploadException('只允许上传图片:' . implode(', ', array_keys(self::MIME_BY_EXT)));
        }

        // 双重内容校验:finfo 按字节流判断真实类型,getimagesizefromstring 确认是可渲染位图,
        // 防止把 PHP/HTML 脚本改名为 .png 上传(扩展名校验单独挡不住)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($upload->content);
        if ($mime === false || !in_array($mime, self::MIME_BY_EXT, true) || getimagesizefromstring($upload->content) === false) {
            throw new InvalidUploadException('文件内容不是有效的图片');
        }

        // 随机存储名:避免覆盖、避免路径注入;原始名只保存在元数据里
        $storedName = bin2hex(random_bytes(16)) . '.' . $ext;
        if (file_put_contents($this->storagePath . '/' . $storedName, $upload->content) === false) {
            throw new \RuntimeException('保存文件失败,请检查存储目录写权限');
        }

        $metaPath = $this->storagePath . '/' . $storedName . '.json';
        $meta = ['original' => $upload->clientName ?? $storedName, 'time' => time()];
        if (file_put_contents($metaPath, json_encode($meta, JSON_UNESCAPED_UNICODE)) === false) {
            throw new \RuntimeException('写入元数据失败,请检查存储目录写权限');
        }

        return new Image(
            name: $storedName,
            original: $meta['original'],
            size: strlen($upload->content),
            time: $meta['time'],
        );
    }

    /**
     * 删除图片及其元数据
     *
     * @throws ImageNotFoundException 文件不存在或扩展名不在白名单
     */
    public function delete(string $name): void
    {
        $path = $this->resolve($name, imageOnly: true);
        unlink($path);
        // 元数据可能不存在(旧文件),缺省即可
        $metaPath = $path . '.json';
        if (is_file($metaPath)) {
            unlink($metaPath);
        }
    }

    /**
     * 定位存储文件的真实路径,统一做目录穿越防护
     *
     * @param bool $imageOnly true 时只允许图片扩展名(删除接口用它防止删掉任意文件)
     * @throws ImageNotFoundException 文件不存在或校验未通过
     */
    public function resolve(string $name, bool $imageOnly = false): string
    {
        // basename 去掉路径部分,挡住 ?name=../../.env 一类穿越
        $name = basename($name);
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if ($name === '' || str_starts_with($name, '.') || ($imageOnly && !isset(self::MIME_BY_EXT[$ext]))) {
            throw new ImageNotFoundException('文件不存在');
        }

        $realBase = realpath($this->storagePath);
        $realPath = realpath($this->storagePath . '/' . $name);
        // 前缀比对确认文件确实位于存储目录内;realpath 同时把分隔符统一,Windows 下必须
        if ($realBase === false || $realPath === false
            || !str_starts_with($realPath, $realBase . DIRECTORY_SEPARATOR) || !is_file($realPath)) {
            throw new ImageNotFoundException('文件不存在');
        }
        return $realPath;
    }

    private function readOriginalName(string $path): string
    {
        $metaPath = $path . '.json';
        // 旧文件可能没有元数据,缺失或解析失败时回退到存储名
        $meta = is_file($metaPath) ? json_decode((string)file_get_contents($metaPath), true) : null;
        return is_array($meta) && isset($meta['original']) && is_string($meta['original'])
            ? $meta['original']
            : basename($path);
    }

    private function formatSize(int $bytes): string
    {
        return round($bytes / 1024 / 1024, 2) . ' MB';
    }
}
