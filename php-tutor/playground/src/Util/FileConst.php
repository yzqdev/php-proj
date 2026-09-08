<?php

namespace Yzqde\Playground\Util;

class FileConst
{
    public const string ROOT_DIR = __DIR__ . '/../../';

    // 基于 ROOT_DIR 定义其他所有子路径
    public const CONTROLLER_DIR = self::ROOT_DIR . 'src/Controller';
    public const SCHEMA_DIR = self::ROOT_DIR . 'src/Dto';
}