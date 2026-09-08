<?php
declare(strict_types=1);

/**
 * 下载测试端点
 * 生成指定大小的随机二进制数据并流式输出。
 * 参数：size 下载数据大小（字节），默认 20MB，范围 1KB - 100MB
 */

$size = isset($_GET['size']) ? (int) $_GET['size'] : 20 * 1024 * 1024;
$size = max(1024, min($size, 100 * 1024 * 1024));

header('Content-Type: application/octet-stream');
header('Content-Length: ' . $size);
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Access-Control-Allow-Origin: *');
header('Vary: Accept-Encoding');
header('X-Accel-Buffering: no');

while (ob_get_level()) {
    ob_end_clean();
}
ob_implicit_flush(true);

// 生成 64KB 随机种子数据，重复填充
$seedSize = 65536;
$seed = random_bytes($seedSize);
$remaining = $size;

while ($remaining > 0) {
    $current = min($seedSize, $remaining);
    echo $current === $seedSize ? $seed : substr($seed, 0, $current);
    flush();
    $remaining -= $current;
}
