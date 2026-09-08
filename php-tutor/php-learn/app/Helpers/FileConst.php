<?php

namespace App\Helpers;

class FileConst
{
    public const ROOT_DIR = __DIR__ . '/../../';

    // 基于 ROOT_DIR 定义其他所有子路径
    public const CONTROLLER_DIR = self::ROOT_DIR . 'app/Controllers/Api';
    public const SCHEMA_DIR = self::ROOT_DIR . 'app/Entities';
}