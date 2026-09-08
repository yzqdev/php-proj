<?php
declare(strict_types=1);
namespace App\Config;



class FileConst
{
    // 以 FileConst.php 所在位置为基准，计算项目根目录
    // 假设本文件在 src/Config/，需要往上退两级到达根目录
    public const ROOT_DIR = __DIR__ . '/../../';

    // 基于 ROOT_DIR 定义其他所有子路径
    public const LOG_DIR = self::ROOT_DIR . 'var/logs';
    public const CACHE_DIR = self::ROOT_DIR . 'var/cache';
    public const UPLOAD_DIR = self::ROOT_DIR . 'public/uploads';
    public const CONFIG_DIR = self::ROOT_DIR . 'config';
}
