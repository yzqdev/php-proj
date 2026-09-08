<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\File;
use App\Exception\FileUploadException;
use App\Repository\FileRepository;
use finfo;
use InvalidArgumentException;
use Monolog\Logger;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

/**
 * 文件上传业务逻辑层
 *
 * 编排「校验 → 落盘 → 入库 → 失败回滚」的完整上传流程：
 * 先做大小与扩展名白名单校验，再用 finfo 从内容识别真实 MIME 并与扩展名比对，
 * 落盘成功后才写数据库记录；写库失败时删除已落盘文件，避免产生孤儿文件。
 *
 * 依赖注入：FileRepository、FileStorage、Monolog 由 PHP-DI autowiring 自动解析。
 */
final class FileService
{
    /** 单文件最大字节数：8 MiB */
    private const MAX_SIZE_BYTES = 8 * 1024 * 1024;

    /** 原始文件名最大长度（字符数） */
    private const MAX_NAME_LENGTH = 200;

    /** 随机文件名的十六进制字符数（32 位 = 128 bit 熵） */
    private const NAME_BYTES = 16;

    /** 列表默认返回条数 */
    public const DEFAULT_LIMIT = 100;

    /** 兜底 MIME：Windows 与旧版 Office 文档常被 finfo 识别为此类型 */
    private const MIME_UNKNOWN = 'application/octet-stream';

    /**
     * 允许上传的扩展名 → 可接受的 MIME 类型集合。
     *
     * 扩展名是第一道白名单，MIME 由 finfo 从文件内容识别；
     * 两者不一致时拒绝，除非 finfo 只给出 application/octet-stream（见 upload 方法）。
     */
    private const array ALLOWED = [
        // 图片
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
        'gif'  => ['image/gif'],
        'webp' => ['image/webp'],
        'bmp'  => ['image/bmp', 'image/x-ms-bmp'],
        'ico'  => ['image/x-icon', 'image/vnd.microsoft.icon'],
        // 文本
        'txt'  => ['text/plain'],
        'md'   => ['text/plain', 'text/markdown'],
        'json' => ['application/json', 'text/json'],
        'xml'  => ['application/xml', 'text/xml'],
        'csv'  => ['text/csv', 'text/plain', 'application/vnd.ms-excel'],
        // 文档
        'pdf'  => ['application/pdf'],
        'doc'  => ['application/msword'],
        'docx' => ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'xls'  => ['application/vnd.ms-excel'],
        'xlsx' => ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'ppt'  => ['application/vnd.ms-powerpoint'],
        'pptx' => ['application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'],
        // 压缩包
        'zip'  => ['application/zip', 'application/x-zip-compressed'],
        'rar'  => ['application/x-rar-compressed', 'application/vnd.rar'],
        '7z'   => ['application/x-7z-compressed', 'application/x-7z'],
        'tar'  => ['application/x-tar'],
        'gz'   => ['application/gzip', 'application/x-gzip'],
    ];

    public function __construct(
        private FileRepository $repository,
        private FileStorage $storage,
        private readonly Logger $logger,
    ) {
    }

    // ─── 上传与删除 ──────────────────────────────────────

    /**
     * 上传一个文件
     *
     * 处理顺序：上传状态 → 大小 → 文件名与扩展名白名单 → 内容 MIME 比对 → 落盘 → 入库。
     * 任一步失败都抛出 FileUploadException，由控制器统一转成 422 响应。
     *
     * @param UploadedFileInterface $uploaded PSR-7 上传文件对象（来自请求的 file 字段）
     *
     * @return File 持久化后的文件记录
     *
     * @throws FileUploadException 校验不通过或保存失败时
     */
    public function upload(UploadedFileInterface $uploaded): File
    {
        $error = $uploaded->getError();
        if ($error !== UPLOAD_ERR_OK) {
            $this->logger->warning('PHP 上传失败', ['error_code' => $error]);
            throw new FileUploadException($this->uploadErrorMessage($error));
        }

        $size = (int) $uploaded->getSize();
        if ($size <= 0) {
            throw new FileUploadException('上传的文件内容为空');
        }

        $maxBytes = $this->maxUploadBytes();
        if ($size > $maxBytes) {
            $this->logger->warning('上传文件过大', ['size' => $size, 'max' => $maxBytes]);
            throw new FileUploadException(
                sprintf('文件过大：%s，最大允许 %s', $this->humanSize($size), $this->humanSize($maxBytes)),
            );
        }

        $originalName = $this->sanitizeName($uploaded->getClientFilename());
        if ($originalName === '') {
            throw new FileUploadException('文件名无效');
        }

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if ($extension === '') {
            throw new FileUploadException('文件缺少扩展名');
        }

        $allowed = self::ALLOWED[$extension] ?? null;
        if ($allowed === null) {
            $this->logger->warning('上传文件类型不在白名单内', ['extension' => $extension, 'name' => $originalName]);
            throw new FileUploadException(sprintf('不支持的文件类型：.%s', $extension));
        }

        $content = (string) $uploaded->getStream();
        $mime = $this->detectMime($content);

        if (!in_array($mime, $allowed, true)) {
            // finfo 对 Office 文档常只返回 octet-stream，此时按扩展名放行并记录日志
            if ($mime !== self::MIME_UNKNOWN) {
                throw new FileUploadException(sprintf('文件内容与扩展名不匹配（识别为 %s）', $mime));
            }
            $this->logger->warning('文件 MIME 无法识别，按扩展名放行', ['extension' => $extension]);
            $mime = $allowed[0];
        }

        $storedName = bin2hex(random_bytes(self::NAME_BYTES)) . '.' . $extension;
        $relativePath = date('Y/m') . '/' . $storedName;

        try {
            $this->storage->store($content, $relativePath);
        } catch (InvalidArgumentException | RuntimeException $e) {
            $this->logger->error('文件写入失败', ['path' => $relativePath, 'error' => $e->getMessage()]);
            throw new FileUploadException('文件保存失败，请稍后重试', previous: $e);
        }

        $file = new File();
        $file->setOriginalName($originalName);
        $file->setPath($relativePath);
        $file->setMime($mime);
        $file->setSize($size);

        try {
            return $this->repository->create($file);
        } catch (\Throwable $e) {
            // 回滚已落盘文件，避免出现磁盘有文件、库里无记录的孤儿数据
            $this->storage->delete($relativePath);
            $this->logger->error('上传记录写入失败，已回滚文件', ['path' => $relativePath, 'error' => $e->getMessage()]);
            throw new FileUploadException('上传记录保存失败', previous: $e);
        }
    }

    /**
     * 删除文件（磁盘文件 + 数据库记录）
     *
     * @param int $id 文件记录 ID
     *
     * @return bool 是否成功举办，记录不存在返回 false
     */
    public function deleteFile(int $id): bool
    {
        $file = $this->repository->findById($id);
        if ($file === null) {
            return false;
        }

        if (!$this->storage->exists($file->getPath())) {
            $this->logger->warning('磁盘文件已不存在，仅删除记录', ['id' => $id, 'path' => $file->getPath()]);
        } elseif (!$this->storage->delete($file->getPath())) {
            $this->logger->error('磁盘文件删除失败', ['id' => $id, 'path' => $file->getPath()]);
        }

        return $this->repository->delete($id);
    }

    // ─── 查询 ────────────────────────────────────────────

    /**
     * 获取最近上传的文件列表
     *
     * @param int $limit 返回条数上限
     *
     * @return File[] 文件对象数组，按 ID 降序
     */
    public function getAllFiles(int $limit = self::DEFAULT_LIMIT): array
    {
        return $this->repository->findAllFiles($limit);
    }

    /**
     * 按 ID 获取单个文件记录
     *
     * @param int $id 文件记录 ID
     *
     * @return File|null 找到返回 File 实例，不存在返回 null
     */
    public function getFile(int $id): ?File
    {
        return $this->repository->findById($id);
    }

    /**
     * 获取文件在磁盘上的绝对路径（供下载流式读取）
     *
     * @param File $file 文件记录
     *
     * @return string|null 绝对路径；路径非法或文件已丢失时返回 null
     */
    public function storagePath(File $file): ?string
    {
        try {
            $absolute = $this->storage->resolve($file->getPath());
        } catch (InvalidArgumentException) {
            return null;
        }

        return is_file($absolute) ? $absolute : null;
    }

    // ─── 上传策略 ────────────────────────────────────────

    /**
     * 当前实际生效的单文件上限（字节）
     *
     * 取「应用配置」与「PHP 的 upload_max_filesize / post_max_size」两者的较小值，
     * 避免用户在页面上按应用上限提交却被 PHP 直接拒绝。
     */
    public function maxUploadBytes(): int
    {
        return min(self::MAX_SIZE_BYTES, $this->iniUploadLimit());
    }

    /**
     * 人类可读的上限提示，例如 "2 MB"
     */
    public function maxUploadLabel(): string
    {
        return $this->humanSize($this->maxUploadBytes());
    }

    /**
     * 允许上传的扩展名列表（升序，不含点）
     *
     * @return list<string>
     */
    public function allowedExtensions(): array
    {
        $extensions = array_keys(self::ALLOWED);
        sort($extensions);
        return $extensions;
    }

    /**
     * 页面上展示的扩展名说明，例如 ".7z、.csv、.doc …"
     */
    public function allowedExtensionsText(): string
    {
        return implode('、', array_map(
            static fn(string $extension): string => '.' . $extension,
            $this->allowedExtensions(),
        ));
    }

    /**
     * 将字节数转换为人类可读的体积字符串
     *
     * @param int $bytes 字节数
     *
     * @return string 例如 "1.4 MB"
     */
    public function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $value = (float) $bytes;
        $unit = 'B';

        foreach ($units as $unit) {
            if ($value < 1024 || $unit === 'TB') {
                break;
            }
            $value /= 1024;
        }

        return rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.') . ' ' . $unit;
    }

    // ─── 私有辅助 ────────────────────────────────────────

    /**
     * 用 finfo 从文件内容识别 MIME 类型
     *
     * @param string $content 文件内容
     *
     * @return string MIME 类型，识别失败时返回 application/octet-stream
     */
    private function detectMime(string $content): string
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($content);

        return is_string($mime) && $mime !== '' ? $mime : self::MIME_UNKNOWN;
    }

    /**
     * 清洗客户端提交的文件名：去掉 NUL 与控制字符、只保留最后一段路径
     *
     * @param string|null $name 客户端原始文件名（可能为 null 或含路径）
     *
     * @return string 清洗后的文件名，非法时返回空字符串
     */
    private function sanitizeName(?string $name): string
    {
        $cleaned = preg_replace('/[\x00-\x1F\x7F]/u', '', (string) $name);
        $cleaned = is_string($cleaned) ? basename($cleaned) : '';
        $cleaned = ltrim($cleaned, '.');

        return mb_substr($cleaned, 0, self::MAX_NAME_LENGTH, 'UTF-8');
    }

    /**
     * 解析 PHP ini 中 upload_max_filesize / post_max_size 的较小值
     *
     * @return int 字节数；0 或无法解析时视为不限制
     */
    private function iniUploadLimit(): int
    {
        return min(
            self::parseSizeIni(ini_get('upload_max_filesize')),
            self::parseSizeIni(ini_get('post_max_size')),
        );
    }

    /**
     * 解析 PHP 的大小配置字符串（支持 K/k、M/m、G/g 后缀）
     *
     * @param mixed $value ini 读取到的原始值
     *
     * @return int 字节数
     */
    private static function parseSizeIni(mixed $value): int
    {
        if (!is_string($value) || trim($value) === '') {
            return PHP_INT_MAX;
        }

        $raw = trim($value);
        if ($raw === '0') {
            return PHP_INT_MAX;
        }

        $match = [];
        if (preg_match('/^(\d+)\s*([kmgKMG])?$/', $raw, $match) !== 1) {
            return PHP_INT_MAX;
        }

        $bytes = (int) $match[1];
        $unit = isset($match[2]) ? strtoupper($match[2]) : '';

        return match ($unit) {
            'K' => $bytes * 1024,
            'M' => $bytes * 1024 * 1024,
            'G' => $bytes * 1024 * 1024 * 1024,
            default => $bytes,
        };
    }

    /**
     * 将 PHP 上传错误码翻译为可读提示
     *
     * @param int $code UPLOAD_ERR_* 常量
     *
     * @return string 面向用户的错误提示
     */
    private function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => '文件超过服务器允许的最大上传体积',
            UPLOAD_ERR_PARTIAL => '文件只上传了一部分，请重试',
            UPLOAD_ERR_NO_FILE => '没有选择要上传的文件',
            UPLOAD_ERR_NO_TMP_DIR => '服务器缺少临时上传目录',
            UPLOAD_ERR_CANT_WRITE => '临时文件写入磁盘失败',
            UPLOAD_ERR_EXTENSION => '上传被 PHP 扩展拦截',
            default => '文件上传过程中出现错误',
        };
    }
}