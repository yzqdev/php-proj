<?php

declare(strict_types=1);

namespace Yzqde\Thunder\Command;

use Symfony\Component\Console\Command\Command;

/**
 * 命令基类：提供路径解析、字节格式化等公共能力
 */
abstract class BaseCommand extends Command
{
    /**
     * 把用户输入的路径解析为绝对路径
     *
     * 支持：~ 家目录展开、相对路径、.. 段归一化；Windows / *nix 通用。
     * 返回值统一用 / 分隔（PHP 在 Windows 上同样接受）。
     */
    protected function resolvePath(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            $path = '.';
        }

        // 1) 家目录 ~ 展开（Windows 用 USERPROFILE，*nix 用 HOME）
        $home = getenv('USERPROFILE') ?: getenv('HOME') ?: '';
        if ($home !== '' && ($path === '~' || str_starts_with($path, '~/'))) {
            $path = $home . substr($path, 1);
        }

        // 2) 提取路径前缀（盘符 / UNC / 根），剩余部分做逐段归一化
        $prefix = '';
        $rest = $path;
        if (preg_match('#^([A-Za-z]:)[\\\\/]?#', $path, $m) === 1) {
            $prefix = $m[1] . '/';
            $rest = substr($path, strlen($m[0]));
        } elseif (str_starts_with($path, '//')) {
            $prefix = '//';
            $rest = substr($path, 2);
        } elseif (str_starts_with($path, '/') || str_starts_with($path, '\\')) {
            $prefix = '/';
            $rest = ltrim($path, '/\\');
        } else {
            // 相对路径：挂到当前工作目录下
            $prefix = rtrim(str_replace('\\', '/', (string) getcwd()), '/') . '/';
            $rest = str_replace('\\', '/', $path);
        }

        // 3) 逐段消解 . 与 ..（空栈时 pop 无效，自然实现“到根为止”）
        $stack = [];
        foreach (explode('/', $rest) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                array_pop($stack);
                continue;
            }
            $stack[] = $segment;
        }

        // 栈空说明是根/盘符本身，保留前缀自带的斜杠；否则去掉尾斜杠
        return $stack === [] ? $prefix : rtrim($prefix . implode('/', $stack), '/');
    }

    /**
     * 列出目录直接子项（可选过滤隐藏文件），目录在前、文件在后，各自按名称排序
     *
     * @return \SplFileInfo[]
     */
    protected function listEntries(string $dir, bool $showAll = false): array
    {
        $entries = [];
        foreach (new \FilesystemIterator($dir, \FilesystemIterator::SKIP_DOTS) as $item) {
            if (!$showAll && str_starts_with($item->getFilename(), '.')) {
                continue;
            }
            $entries[] = $item;
        }
        usort($entries, static fn(\SplFileInfo $a, \SplFileInfo $b): int =>
            [$b->isDir(), $a->getFilename()] <=> [$a->isDir(), $b->getFilename()]);

        return $entries;
    }

    /** 字节数格式化（1024 进制，保留 1 位小数） */
    protected function formatBytes(int|float $bytes): string
    {
        $bytes = max(0.0, (float) $bytes);
        $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB'];
        $pow = $bytes > 0 ? (int) floor(log($bytes, 1024)) : 0;
        $pow = min($pow, count($units) - 1);

        return round($bytes / 1024 ** $pow, 1) . ' ' . $units[$pow];
    }
}
