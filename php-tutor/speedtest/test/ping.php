<?php
declare(strict_types=1);

/**
 * 延迟测试端点
 * 客户端发送请求后，服务器立即返回响应，
 * 客户端测量往返时间（RTT）即为延迟。
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

echo json_encode([
    'status' => 'pong',
    'server_time' => microtime(true),
    'server_time_iso' => date('c'),
]);
