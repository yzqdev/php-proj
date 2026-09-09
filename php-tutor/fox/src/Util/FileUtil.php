<?php

declare(strict_types=1);

namespace Yzqde\Fox\Util;

/**
 * 文件操作工具类。
 *
 * 提供常用的文件/目录操作方法，全部为静态方法，方便直接调用。
 */
class FileUtil
{
    // ==================== 文件信息 ====================

    /**
     * 获取文件扩展名（小写，不含点号）。
     */
    public static function getExtension(string $path): string
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION));
    }

    /**
     * 获取文件名（不含扩展名）。
     */
    public static function getBasename(string $path): string
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * 获取文件的 MIME 类型。
     */
    public static function getMimeType(string $path): string
    {
        $ext = self::getExtension($path);

        return FileConst::MIME_TYPES[$ext] ?? 'application/octet-stream';
    }

    /**
     * 获取人类可读的文件大小。
     *
     * @return string 如 "1.5 MB", "320 KB"
     */
    public static function humanSize(int $bytes): string
    {
        if ($bytes >= FileConst::TB) {
            return round($bytes / FileConst::TB, 2) . ' TB';
        }
        if ($bytes >= FileConst::GB) {
            return round($bytes / FileConst::GB, 2) . ' GB';
        }
        if ($bytes >= FileConst::MB) {
            return round($bytes / FileConst::MB, 2) . ' MB';
        }
        if ($bytes >= FileConst::KB) {
            return round($bytes / FileConst::KB, 2) . ' KB';
        }

        return $bytes . ' B';
    }

    /**
     * 获取文件完整信息。
     */
    public static function getFileInfo(string $path): ?array
    {
        if (!is_file($path)) {
            return null;
        }

        return [
            'name' => basename($path),
            'path' => $path,
            'extension' => self::getExtension($path),
            'mime_type' => self::getMimeType($path),
            'size' => filesize($path),
            'size_human' => self::humanSize(filesize($path)),
            'created_at' => date('Y-m-d H:i:s', filectime($path)),
            'modified_at' => date('Y-m-d H:i:s', filemtime($path)),
        ];
    }

    // ==================== 文件读写 ====================

    /**
     * 安全读取文件内容。
     */
    public static function read(string $path, int $flags = 0): ?string
    {
        if (!is_file($path)) {
            return null;
        }

        $content = file_get_contents($path, false, null, 0, null, [
            'http' => ['ignore_errors' => true],
        ]);

        // 忽略上面的 stream context，直接用简单方式
        $content = file_get_contents($path);

        return $content === false ? null : $content;
    }

    /**
     * 写入文件内容（自动创建目录）。
     */
    public static function write(string $path, string $content, bool $append = false): bool
    {
        $dir = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $mode = $append ? FILE_APPEND : 0;

        return file_put_contents($path, $content, $mode | LOCK_EX) !== false;
    }

    /**
     * 追加内容到文件。
     */
    public static function append(string $path, string $content): bool
    {
        return self::write($path, $content, true);
    }

    // ==================== 文件操作 ====================

    /**
     * 复制文件。
     */
    public static function copy(string $source, string $dest, bool $overwrite = true): bool
    {
        if (!$overwrite && is_file($dest)) {
            return false;
        }

        $dir = dirname($dest);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return copy($source, $dest);
    }

    /**
     * 移动/重命名文件。
     */
    public static function move(string $source, string $dest): bool
    {
        $dir = dirname($dest);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return rename($source, $dest);
    }

    /**
     * 删除文件。
     */
    public static function delete(string $path): bool
    {
        if (!is_file($path)) {
            return true;
        }

        return @unlink($path);
    }

    /**
     * 安全删除文件（先清空内容再删除）。
     */
    public static function secureDelete(string $path): bool
    {
        if (!is_file($path)) {
            return true;
        }

        // 覆写内容
        $size = filesize($path);
        file_put_contents($path, str_repeat("\0", $size));

        return @unlink($path);
    }

    // ==================== 目录操作 ====================

    /**
     * 列出目录下的文件和子目录。
     *
     * @return array{name: string, type: string, size: int, size_human: string, modified_at: string}[]
     */
    public static function listDir(string $path, bool $includeHidden = false): array
    {
        if (!is_dir($path)) {
            return [];
        }

        $items = [];
        $scan = scandir($path);

        if ($scan === false) {
            return [];
        }

        foreach ($scan as $name) {
            if ($name === '.' || $name === '..') {
                continue;
            }

            if (!$includeHidden && str_starts_with($name, '.')) {
                continue;
            }

            $fullPath = rtrim($path, '/\\') . DIRECTORY_SEPARATOR . $name;
            $isDir = is_dir($fullPath);
            $size = $isDir ? 0 : filesize($fullPath);

            $items[] = [
                'name' => $name,
                'type' => $isDir ? 'directory' : 'file',
                'size' => $size,
                'size_human' => $isDir ? '-' : self::humanSize($size),
                'modified_at' => date('Y-m-d H:i:s', filemtime($fullPath)),
            ];
        }

        return $items;
    }

    /**
     * 递归列出目录下所有文件。
     *
     * @return string[] 所有文件的完整路径
     */
    public static function listAllFiles(string $path): array
    {
        if (!is_dir($path)) {
            return [];
        }

        $files = [];
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
        );

        foreach ($it as $file) {
            if ($file->isFile()) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * 创建目录（递归）。
     */
    public static function mkdir(string $path, int $mode = 0755): bool
    {
        if (is_dir($path)) {
            return true;
        }

        return mkdir($path, $mode, true);
    }

    /**
     * 清空目录（保留目录本身）。
     */
    public static function clearDir(string $path): bool
    {
        if (!is_dir($path)) {
            return true;
        }

        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        $ok = true;

        foreach ($it as $file) {
            if ($file->isDir()) {
                $ok = rmdir($file->getPathname()) && $ok;
            } else {
                $ok = @unlink($file->getPathname()) && $ok;
            }
        }

        return $ok;
    }

    /**
     * 递归删除整个目录。
     */
    public static function rmdir(string $path): bool
    {
        if (!is_dir($path)) {
            return true;
        }

        return self::clearDir($path) && rmdir($path);
    }

    // ==================== 安全相关 ====================

    /**
     * 清理文件名，移除危险字符。
     */
    public static function sanitizeFilename(string $filename): string
    {
        // 移除路径分隔符和控制字符
        $filename = basename($filename);
        $filename = preg_replace('/[^\w\-.]/', '_', $filename);

        // 移除连续下划线
        $filename = preg_replace('/_+/', '_', $filename);

        return trim($filename, '_.');
    }

    /**
     * 检查文件扩展名是否在允许列表中。
     */
    public static function isAllowedExtension(string $path, ?array $allowed = null): bool
    {
        $ext = self::getExtension($path);
        $allowed ??= FileConst::ALLOWED_UPLOAD_EXTS;

        return in_array($ext, $allowed, true);
    }

    /**
     * 检查路径是否在指定目录内（防目录穿越）。
     */
    public static function isInsideDirectory(string $path, string $directory): bool
    {
        $realPath = realpath($path);
        $realDir = realpath($directory);

        if ($realPath === false || $realDir === false) {
            return false;
        }

        return str_starts_with($realPath, $realDir . DIRECTORY_SEPARATOR) || $realPath === $realDir;
    }

    /**
     * 生成唯一文件名（时间戳 + 随机串）。
     */
    public static function uniqueName(string $extension): string
    {
        return date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    }

    // ==================== 哈希 ====================

    /**
     * 计算文件的 MD5 哈希。
     */
    public static function md5(string $path): ?string
    {
        if (!is_file($path)) {
            return null;
        }

        return md5_file($path);
    }

    /**
     * 计算文件的 SHA256 哈希。
     */
    public static function sha256(string $path): ?string
    {
        if (!is_file($path)) {
            return null;
        }

        return hash_file('sha256', $path);
    }
}
