<?php

declare(strict_types=1);

namespace Yzqde\Thunder;

/**
 * 文件系统操作服务：递归复制 / 删除 / 移动 / 目录大小
 *
 * 全部为静态方法，供 cp / mv / rm 等命令复用；
 * 错误用 @ 抑制后返回 bool，由命令层负责向用户报告。
 */
final class Filesystem
{
    /**
     * 递归复制文件或目录
     *
     * @param string       $src       源路径（文件或目录）
     * @param string       $dst       目标路径
     * @param bool         $overwrite 目标已存在时是否覆盖
     * @param callable|null $onEach   每复制完成一个文件回调 fn(string $src, string $dst)
     */
    public static function copy(string $src, string $dst, bool $overwrite = false, ?callable $onEach = null): bool
    {
        if (is_dir($src)) {
            if (!is_dir($dst) && !@mkdir($dst, 0777, true)) {
                return false;
            }
            $it = new \FilesystemIterator($src, \FilesystemIterator::SKIP_DOTS);
            foreach ($it as $item) {
                if (!self::copy($item->getPathname(), $dst . '/' . $item->getFilename(), $overwrite, $onEach)) {
                    return false;
                }
            }

            return true;
        }

        if (!is_file($src)) {
            return false;
        }
        if (is_file($dst) && !$overwrite) {
            return false; // 目标存在且不允许覆盖
        }
        $dstDir = dirname($dst);
        if (!is_dir($dstDir) && !@mkdir($dstDir, 0777, true)) {
            return false;
        }

        $ok = @copy($src, $dst);
        if ($ok && $onEach !== null) {
            $onEach($src, $dst);
        }

        return $ok;
    }

    /** 递归删除文件或目录（符号链接只删链接本身） */
    public static function remove(string $path): bool
    {
        if (is_link($path)) {
            return @unlink($path);
        }
        if (is_dir($path)) {
            $it = new \FilesystemIterator($path, \FilesystemIterator::SKIP_DOTS);
            foreach ($it as $item) {
                if (!self::remove($item->getPathname())) {
                    return false;
                }
            }

            return @rmdir($path);
        }
        if (is_file($path)) {
            return @unlink($path);
        }

        return false; // 路径不存在
    }

    /**
     * 移动/重命名：优先 rename（同盘原子操作），
     * 失败（常见于 Windows 跨盘）时回退为“复制 + 删除”。
     */
    public static function move(string $src, string $dst, bool $overwrite = false, ?callable $onEach = null): bool
    {
        if (!file_exists($src) && !is_link($src)) {
            return false;
        }
        if (file_exists($dst) && !$overwrite) {
            return false;
        }

        if (@rename($src, $dst)) {
            if ($onEach !== null) {
                $onEach($src, $dst);
            }

            return true;
        }

        return self::copy($src, $dst, $overwrite, $onEach) && self::remove($src);
    }

    /** 目录总大小（递归求和，不含目录条目本身） */
    public static function dirSize(string $dir): int
    {
        $total = 0;
        $it = new \FilesystemIterator($dir, \FilesystemIterator::SKIP_DOTS);
        foreach ($it as $item) {
            $total += $item->isDir() && !$item->isLink()
                ? self::dirSize($item->getPathname())
                : $item->getSize();
        }

        return $total;
    }

    /**
     * 危险路径守卫：删除前检查
     *
     * 拒绝：盘符根目录（C:/）、文件系统根（/）、当前工作目录及其祖先。
     */
    public static function isDangerous(string $abs): bool
    {
        $norm = rtrim(str_replace('\\', '/', $abs), '/');

        // 盘符根 / 空串（根目录）
        if ($norm === '' || preg_match('#^[A-Za-z]:$#', $norm) === 1) {
            return true;
        }

        // 当前工作目录或其祖先（删掉会话基础目录会破坏后续所有操作）
        $cwd = rtrim(str_replace('\\', '/', (string) getcwd()), '/');

        return $norm === $cwd || str_starts_with($cwd . '/', $norm . '/');
    }
}
