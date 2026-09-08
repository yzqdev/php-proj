<?php

declare(strict_types=1);

// php -S 内置服务器专用路由脚本。
//
// 内置服务器遇到不存在的 URI 时会直接 404，而不是转发给应用；
// 只有不带扩展名的路径才会回退到 index.php，所以 /docs、/docs/openapi.json 会挂。
// 这里显式分流：命中 public/ 下真实存在的静态文件就直接返回（return false），
// 其余请求一律交给 Slim 处理。

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

$publicDir = realpath(__DIR__);
$requested = __DIR__ . '/' . ltrim($uri, '/');
$realFile = is_file($requested) ? realpath($requested) : false;

// 只放行真正落在 public/ 目录内的文件，避免 ../ 越界读到源码
if ($publicDir !== false && $realFile !== false && str_starts_with($realFile, $publicDir)) {
    return false;
}

require __DIR__ . '/index.php';
