<?php

declare(strict_types=1);

namespace Yzqde\Fox\Util;

/**
 * 项目路径和文件相关常量。
 */
class FileConst
{
    // ==================== 路径方法 ====================

    /** 项目根目录 */
    public static function rootDir(): string
    {
        return dirname(__DIR__, 2) . '/';
    }

    public static function publicDir(): string
    {
        return self::rootDir() . 'public';
    }

    public static function srcDir(): string
    {
        return self::rootDir() . 'src';
    }

    public static function logDir(): string
    {
        return self::rootDir() . 'logs';
    }

    public static function cacheDir(): string
    {
        return self::rootDir() . 'cache';
    }

    public static function doctrineProxyDir(): string
    {
        return self::cacheDir() . '/doctrine/proxies';
    }

    public static function uploadDir(): string
    {
        return self::publicDir() . '/uploads';
    }

    public static function configDir(): string
    {
        return self::rootDir() . 'config';
    }

    public static function migrationDir(): string
    {
        return self::rootDir() . 'migrations';
    }

    public static function entityDir(): string
    {
        return self::srcDir() . '/Entity';
    }

    public static function repositoryDir(): string
    {
        return self::srcDir() . '/Repository';
    }

    public static function storageDir(): string
    {
        return self::rootDir() . 'storage';
    }

    public static function sessionDir(): string
    {
        return self::storageDir() . '/session';
    }

    // ==================== 常用文件大小单位 ====================

    public const int KB = 1024;
    public const int MB = 1048576;          // 1024 * 1024
    public const int GB = 1073741824;       // 1024 * 1024 * 1024
    public const int TB = 1099511627776;    // 1024 * 1024 * 1024 * 1024

    // ==================== MIME 类型映射 ====================

    /** @var array<string, string> 扩展名 => MIME 类型 */
    public const array MIME_TYPES = [
        // 图片
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'svg'  => 'image/svg+xml',
        'bmp'  => 'image/bmp',
        'ico'  => 'image/x-icon',
        'tiff' => 'image/tiff',
        'avif' => 'image/avif',

        // 文档
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'  => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt'  => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'txt'  => 'text/plain',
        'csv'  => 'text/csv',

        // 音频
        'mp3'  => 'audio/mpeg',
        'wav'  => 'audio/wav',
        'ogg'  => 'audio/ogg',
        'flac' => 'audio/flac',
        'aac'  => 'audio/aac',

        // 视频
        'mp4'  => 'video/mp4',
        'avi'  => 'video/x-msvideo',
        'mov'  => 'video/quicktime',
        'mkv'  => 'video/x-matroska',
        'webm' => 'video/webm',

        // 压缩包
        'zip'  => 'application/zip',
        'rar'  => 'application/vnd.rar',
        '7z'   => 'application/x-7z-compressed',
        'tar'  => 'application/x-tar',
        'gz'   => 'application/gzip',

        // Web
        'html' => 'text/html',
        'htm'  => 'text/html',
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'json' => 'application/json',
        'xml'  => 'application/xml',
        'yaml' => 'application/x-yaml',
        'yml'  => 'application/x-yaml',

        // 字体
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'otf'   => 'font/otf',
        'eot'   => 'application/vnd.ms-fontobject',
    ];

    // ==================== 允许上传的扩展名 ====================

    /** @var array<string> 允许上传的图片类型 */
    public const array ALLOWED_IMAGE_EXTS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'];

    /** @var array<string> 允许上传的文档类型 */
    public const array ALLOWED_DOC_EXTS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv'];

    /** @var array<string> 允许上传的所有类型 */
    public const array ALLOWED_UPLOAD_EXTS = [
        ...self::ALLOWED_IMAGE_EXTS,
        ...self::ALLOWED_DOC_EXTS,
    ];
}
