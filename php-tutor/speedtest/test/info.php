<?php
declare(strict_types=1);

/**
 * 客户端 IP 信息端点
 * 获取客户端真实 IP，可选从 ipinfo.io 获取 ISP / 国家信息。
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

function getClientIp(): string
{
    $headers = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = trim(explode(',', $_SERVER[$header])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return '0.0.0.0';
}

$ip = getClientIp();
$ip = preg_replace('/^::ffff:/', '', $ip);

$result = ['ip' => $ip, 'isp' => null, 'country' => null];

$getIsp = isset($_GET['isp']) && $_GET['isp'] === '1';
if ($getIsp) {
    $apiKey = '';
    if (file_exists(__DIR__ . '/ipinfo_apikey.txt')) {
        $apiKey = trim((string) file_get_contents(__DIR__ . '/ipinfo_apikey.txt'));
    }
    $apiKeyParam = $apiKey ? '?token=' . $apiKey : '';
    $context = stream_context_create(['http' => ['timeout' => 3]]);
    $json = @file_get_contents('https://ipinfo.io/' . $ip . '/json' . $apiKeyParam, false, $context);
    if ($json && is_string($json)) {
        $data = json_decode($json, true);
        if (is_array($data)) {
            $result['isp'] = isset($data['org']) ? preg_replace('/AS\d+\s/', '', (string) $data['org']) : null;
            $result['country'] = $data['country'] ?? null;
        }
    }
}

echo json_encode($result);
