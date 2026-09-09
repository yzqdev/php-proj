<?php

declare(strict_types=1);

namespace Yzqde\Fox\Util;

/**
 * 项目路径和文件相关常量。
 */
final class FileConst
{
    // ==================== 项目路径 ====================

    /** 项目根目录 */
      public const string ROOT_DIR = __DIR__ . '/../../';

    /** public 目录 */
    public const string PUBLIC_DIR = self::ROOT_DIR . '/public';

    /** src 目录 */
    public const string SRC_DIR = self::ROOT_DIR . '/src';

    /** 日志目录 */
    public const string LOG_DIR = self::ROOT_DIR . '/logs';

    /** 缓存目录 */
    public const string CACHE_DIR = self::ROOT_DIR . '/cache';

    /** doctrine proxy 目录 */
    public const string DOCTRINE_PROXY_DIR = self::CACHE_DIR . '/doctrine/proxies';

    /** 上传目录 */
    public const string UPLOAD_DIR = self::PUBLIC_DIR . '/uploads';

    /** 配置目录 */
    public const string CONFIG_DIR = self::ROOT_DIR . '/config';

    /** migration 目录 */
    public const string MIGRATION_DIR = self::ROOT_DIR . '/migrations';

    /** entity 目录 */
    public const string ENTITY_DIR = self::SRC_DIR . '/Entity';

    /** repository 目录 */
    public const string REPOSITORY_DIR = self::SRC_DIR . '/Repository';

    /** storage 目录 */
    public const string STORAGE_DIR = self::ROOT_DIR . '/storage';

    /** session 目录 */
    public const string SESSION_DIR = self::STORAGE_DIR . '/session';

    // ==================== 文件大小单位 ====================

    public const int KB = 1024;
    public const int MB = 1024 * 1024;
    public const int GB = 1024 * 1024 * 1024;
    public const int TB = 1024 * 1024 * 1024 * 1024;

    // ==================== MIME 类型映射 ====================

    /** @var array<string, string> 扩展名 => MIME 类型 */
    public const array MIME_TYPES = [
        // 图片
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
        'bmp' => 'image/bmp',
        'ico' => 'image/x-icon',
        'tiff' => 'image/tiff',
        'avif' => 'image/avif',

        // 文档
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'txt' => 'text/plain',
        'csv' => 'text/csv',

        // 音频
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'ogg' => 'audio/ogg',
        'flac' => 'audio/flac',
        'aac' => 'audio/aac',

        // 视频
        'mp4' => 'video/mp4',
        'avi' => 'video/x-msvideo',
        'mov' => 'video/quicktime',
        'mkv' => 'video/x-matroska',
        'webm' => 'video/webm',

        // 压缩包
        'zip' => 'application/zip',
        'rar' => 'application/vnd.rar',
        '7z' => 'application/x-7z-compressed',
        'tar' => 'application/x-tar',
        'gz' => 'application/gzip',

        // Web
        'html' => 'text/html',
        'htm' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'yaml' => 'application/x-yaml',
        'yml' => 'application/x-yaml',

        // 字体
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'otf' => 'font/otf',
        'eot' => 'application/vnd.ms-fontobject',
    ];

    // ==================== 允许上传的扩展名 ====================

    /** @var array<string> 允许上传的图片类型 */
    public const array ALLOWED_IMAGE_EXTS = [
        'jpg',
        'jpeg',
        'png',
        'gif',
        'webp',
        'svg',
        'bmp',
    ];

    /** @var array<string> 允许上传的文档类型 */
    public const array ALLOWED_DOC_EXTS = [
        'pdf',
        'doc',
        'docx',
        'xls',
        'xlsx',
        'ppt',
        'pptx',
        'txt',
        'csv',
    ];

    /** @var array<string> 允许上传的所有类型 */
    public const array ALLOWED_UPLOAD_EXTS = [
        ...self::ALLOWED_IMAGE_EXTS,
        ...self::ALLOWED_DOC_EXTS,
    ];
}