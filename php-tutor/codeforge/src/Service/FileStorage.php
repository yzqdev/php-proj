<?php

declare(strict_types=1);

namespace App\Service;

use InvalidArgumentException;
use RuntimeException;

/**
 * 上传文件的磁盘存储服务
 *
 * 文件统一落在 var/uploads 下（不在 Web 根目录内），按「年/月」分子目录归档，
 * 文件名由调用方随机生成，因此无法通过 URL 直接访问，只能走下载路由读取。
 *
 * 全部方法只接受相对路径，内部统一做目录穿越校验，
 * 路径中的斜杠一律规范化为 / 便于与数据库中的 path 列保持一致。
 */
final class FileStorage
{
    /** 上传根目录：项目根目录/var/uploads */
    private const ROOT = __DIR__ . '/../../var/uploads';

    /** 新建目录权限（不放宽到 0777，避免组内及其他用户可写） */
    private const DIR_PERMS = 0755;

    /** 相对路径中禁止出现的分段 */
    private const RESERVED_SEGMENTS = ['.', '..'];

    /**
     * 写入上传文件
     *
     * @param string $content      文件内容
     * @param string $relativePath 相对上传根目录的路径，例如 2026/09/xxxx.jpg
     *
     * @return void
     *
     * @throws InvalidArgumentException 路径非法时
     * @throws RuntimeException         目录创建失败或写入失败时
     */
    public function store(string $content, string $relativePath): void
    {
        $absolute = $this->resolve($relativePath);

        $directory = dirname($absolute);
        if (!is_dir($directory) && !mkdir($directory, self::DIR_PERMS, true) && !is_dir($directory)) {
            throw new RuntimeException("无法创建目录：{$directory}");
        }

        $written = file_put_contents($absolute, $content, LOCK_EX);
        if ($written === false) {
            throw new RuntimeException("文件写入失败：{$absolute}");
        }

        // 二次确认写入字节数，避免磁盘写满等场景下留下半截文件
        if ($written !== strlen($content)) {
            throw new RuntimeException("文件写入字节数不符：{$absolute}");
        }
    }

    /**
     * 删除上传文件，并顺手清理已经为空的归档目录
     *
     * @param string $relativePath 相对上传根目录的路径
     *
     * @return bool 文件存在并删除成功返回 true，原本不存在返回 false
     *
     * @throws InvalidArgumentException 路径非法时
     * @throws RuntimeException         删除失败时
     */
    public function delete(string $relativePath): bool
    {
        $absolute = $this->resolve($relativePath);

        if (!is_file($absolute)) {
            return false;
        }

        if (!unlink($absolute)) {
            throw new RuntimeException("文件删除失败：{$absolute}");
        }

        $this->removeEmptyDirs(dirname($absolute));

        return true;
    }

    /**
     * 判断上传文件是否存在
     *
     * @param string $relativePath 相对上传根目录的路径
     */
    public function exists(string $relativePath): bool
    {
        try {
            return is_file($this->resolve($relativePath));
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    /**
     * 将相对路径解析为绝对路径（含越界校验，不要求文件已存在）
     *
     * @param string $relativePath 相对上传根目录的路径
     *
     * @return string 绝对路径
     *
     * @throws InvalidArgumentException 路径为空、含 .. 或逃出上传目录时
     */
    public function resolve(string $relativePath): string
    {
        $normalized = str_replace('\\', '/', trim($relativePath));

        if ($normalized === '' || str_starts_with($normalized, '/')) {
            throw new InvalidArgumentException("非法的文件路径：{$relativePath}");
        }

        foreach (explode('/', $normalized) as $segment) {
            if (in_array($segment, self::RESERVED_SEGMENTS, true)) {
                throw new InvalidArgumentException("非法的文件路径：{$relativePath}");
            }
        }

        $base = $this->baseDir();
        $absolute = $base . '/' . $normalized;

        // 向上找到最深的已存在祖先目录，确认它仍在上传根目录之内（防御符号链接越界）。
        // 归档子目录在 store() 中才会创建，因此不能要求 dirname() 已经存在。
        $ancestor = dirname($absolute);
        while ($ancestor !== $base && $ancestor !== dirname($base) && realpath($ancestor) === false) {
            $ancestor = dirname($ancestor);
        }

        $realAncestor = str_replace('\\', '/', (string) realpath($ancestor));
        if ($realAncestor === '' || ($realAncestor !== $base && !str_starts_with($realAncestor, $base . '/'))) {
            throw new InvalidArgumentException("文件路径超出上传目录：{$relativePath}");
        }

        return $absolute;
    }

    /**
     * 获取上传根目录（绝对路径，必要时创建）
     *
     * @return string 规范化后的根目录路径
     *
     * @throws RuntimeException 目录不存在且无法创建时
     */
    public function baseDir(): string
    {
        $base = str_replace('\\', '/', self::ROOT);

        if (!is_dir($base) && !mkdir($base, self::DIR_PERMS, true) && !is_dir($base)) {
            throw new RuntimeException("上传目录不存在且无法创建：{$base}");
        }

        $real = str_replace('\\', '/', (string) realpath($base));
        if ($real === '') {
            throw new RuntimeException("上传目录不可访问：{$base}");
        }

        return $real;
    }

    /**
     * 自下而上删除空的归档目录，遇到非空目录或到达根目录即停止
     *
     * @param string $directory 待清理的起始目录（即文件的直接所在目录）
     */
    private function removeEmptyDirs(string $directory): void
    {
        $base = $this->baseDir();
        $current = $directory;

        while ($current !== $base && $current !== dirname($base)) {
            $entries = scandir($current);

            // 空目录的 scandir 结果恰好是 ['.', '..']；非空则停止清理，避免 rmdir 告警
            if ($entries === false || $entries !== ['.', '..']) {
                break;
            }

            if (!rmdir($current)) {
                break;
            }

            $current = dirname($current);
        }
    }
}
