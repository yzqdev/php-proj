<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\OpenApi\SwaggerGenerator;

// 命令行生成 OpenAPI 文档：php bin/generate-openapi.php
// 失败时直接抛出异常，由 PHP 默认处理器输出并以非零码退出。

$generator = new SwaggerGenerator();
$json = $generator->toJson(true);

fwrite(STDOUT, '已生成 var/openapi.json（' . strlen($json) . ' 字节）' . PHP_EOL);
