<?php
declare(strict_types=1);

/**
 * 日志接收端点
 * 前端测速完成后，POST 测试结果到此端点记录日志。
 */

require_once dirname(__DIR__) . '/lib/Logger.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');

if (empty($raw)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Empty request body']);
    exit;
}

$data = json_decode($raw, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON data']);
    exit;
}

// 记录测速结果
Logger::test($data);

// 记录一条摘要日志
Logger::info('测速日志已写入', [
    'client_ip' => $data['client_ip'] ?? '',
    'ping_ms' => $data['ping'] ?? 0,
    'jitter_ms' => $data['jitter'] ?? 0,
    'download_mbps' => $data['download_speed'] ?? 0,
    'upload_mbps' => $data['upload_speed'] ?? 0,
]);

// 定期清理过期日志
if (mt_rand(1, 100) === 1) {
    Logger::cleanup();
}

echo json_encode([
    'status' => 'ok',
    'server_time' => date('c'),
]);
