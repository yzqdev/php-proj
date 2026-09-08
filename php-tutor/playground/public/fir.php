<?php

declare(strict_types=1);
echo getcwd();
echo "\n";
echo __DIR__;
// 1. 获取并格式化输出系统 PATH 环境变量
$path = getenv('TMP');
echo "==== 系统 PATH 环境变量 ====" . PHP_EOL;
echo ($path !== false ? $path : '未获取到 PATH 变量') . PHP_EOL . PHP_EOL;

// 2. 安全地创建目录（先判断是否存在，并指定默认权限与递归创建）
$dirToCreate = 'first';

if (!is_dir($dirToCreate)) {
    if (mkdir($dirToCreate, 0755, true)) {
        echo "目录 [{$dirToCreate}] 创建成功！" . PHP_EOL;
    } else {
        echo "目录 [{$dirToCreate}] 创建失败！" . PHP_EOL;
    }
} else {
    echo "目录 [{$dirToCreate}] 已存在，无需重复创建。" . PHP_EOL;
}

// 3. 检查目录是否存在，并以直观的文字/布尔描述输出结果
$targetDir = 'public';
$isDir = is_dir($targetDir);

echo "检查 [{$targetDir}] 是否为目录: " . ($isDir ? '是 (true)' : '否 (false)') . PHP_EOL;