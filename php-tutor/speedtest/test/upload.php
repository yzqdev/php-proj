<?php
declare(strict_types=1);

/**
 * 上传测试端点
 * 接收客户端上传的二进制数据，测量服务器端接收耗时。
 */

$startT = microtime(true);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Access-Control-Allow-Origin: *');

$raw = file_get_contents('php://input');
$endT = microtime(true);

$bytes = strlen($raw);
$elapsed = $endT - $startT;

echo json_encode([
    'status' => 'ok',
    'bytes_received' => $bytes,
    'server_receive_ms' => round($elapsed * 1000, 2),
    'server_receive_s' => round($elapsed, 4),
]);
