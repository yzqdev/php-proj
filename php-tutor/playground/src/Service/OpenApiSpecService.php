<?php

declare(strict_types=1);

namespace Yzqde\Playground\Service;

use OpenApi\Generator;
use Psr\Log\LoggerInterface;

/**
 * OpenAPI 规范生成与缓存
 *
 * 首次请求扫描 src/ 生成规范并落盘,之后命中缓存直接返回。
 * 缓存按修改时间失效:src/ 下任一文件变更(注解更新)或依赖清单变更(如 swagger-php 升级)
 * 即视为过期,下次请求重新生成。因此文档注解改完无需任何手工步骤。
 */
final class OpenApiSpecService
{
    private const SPEC_VERSION = '3.0.3';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly bool $debug,
        private readonly string $srcDir,
        private readonly string $cachePath,
        private readonly string $lockFile,
    ) {
    }

    /**
     * @param bool $forceRefresh 忽略缓存强制重新生成(供 /openapi.json?refresh=1 使用)
     *
     * @throws RuntimeException 生成或落盘失败
     */
    public function json(bool $forceRefresh = false): string
    {
        if (!$forceRefresh && $this->cacheValid()) {
            return $this->readCache();
        }

        return $this->build($forceRefresh);
    }

    /**
     * 重建规范。持锁后再复查一次缓存:可能在排队等锁的期间已被其他进程重建完毕。
     * 锁为尽力而为,拿不到时仅记录告警后继续,不因文档生成而让整个接口不可用。
     */
    private function build(bool $forceRefresh): string
    {
        $lock = $this->acquireLock();

        try {
            if (!$forceRefresh && $this->cacheValid()) {
                return $this->readCache();
            }

            $json = $this->render();
            $this->writeCache($json);

            return $json;
        } finally {
            $this->releaseLock($lock);
        }
    }

    private function render(): string
    {
        $generator = (new Generator($this->logger))->setVersion(self::SPEC_VERSION);
        $spec = $generator->generate([$this->srcDir], null, $this->debug);

        if ($spec === null) {
            throw new RuntimeException('OpenAPI 规范生成失败:扫描 src/ 未产出规范');
        }

        $json = $spec->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new RuntimeException('OpenAPI 规范序列化失败');
        }

        return $json;
    }

    private function readCache(): string
    {
        $json = file_get_contents($this->cachePath);

        // 缓存内容损坏时抛错让调用方走重建路径,不返回脏数据
        if ($json === false || $json === '') {
            throw new RuntimeException('OpenAPI 规范缓存损坏:' . $this->cachePath);
        }

        return $json;
    }

    private function cacheValid(): bool
    {
        if (!is_file($this->cachePath) || !is_readable($this->cachePath)) {
            return false;
        }

        return filemtime($this->cachePath) >= $this->newestSourceMtime();
    }

    /**
     * 取扫描根目录与依赖清单中最新的修改时间戳,任一变更即需重建
     */
    private function newestSourceMtime(): int
    {
        $newest = 0;
        $roots = [
            $this->srcDir,
            dirname(__DIR__) . '/vendor/composer/installed.json',
        ];

        foreach ($roots as $root) {
            if (is_file($root)) {
                $newest = max($newest, (int)filemtime($root));
                continue;
            }
            if (!is_dir($root)) {
                continue;
            }

            foreach (
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
                ) as $file
            ) {
                $newest = max($newest, $file->getMTime());
            }
        }

        return $newest;
    }

    /**
     * 先写临时文件再原子重命名,避免并发请求读到写了一半的缓存
     */
    private function writeCache(string $json): void
    {
        $dir = dirname($this->cachePath);
        if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
            throw new RuntimeException('规范缓存目录不可用:' . $dir);
        }

        $temp = $this->cachePath . '.tmp';
        if (file_put_contents($temp, $json) === false || !rename($temp, $this->cachePath)) {
            throw new RuntimeException('OpenAPI 规范缓存写入失败:' . $this->cachePath);
        }
    }

    /** @return resource|false */
    private function acquireLock(): mixed
    {
        $lock = fopen($this->lockFile, 'c');
        if ($lock === false || !flock($lock, LOCK_EX)) {
            $this->logger->warning('无法获取 OpenAPI 规范生成锁,跳过互斥继续生成', [
                'lock' => $this->lockFile,
            ]);

            return false;
        }

        return $lock;
    }

    /** @param resource|false $lock */
    private function releaseLock(mixed $lock): void
    {
        if ($lock !== false) {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}
