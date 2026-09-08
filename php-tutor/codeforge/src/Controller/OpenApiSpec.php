<?php

declare(strict_types=1);

namespace App\Controller;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 根声明（swagger-php 扫描入口）
 *
 * 必须放在 src/ 下：ReflectionAnalyser 只扫描能 autoload 的类，
 * 放在 config/ 里会被静默跳过。
 *
 * 注解书写顺序约定，全项目统一：
 *   根声明类：  Info → Server → Tag（Tag 按 config/routes.php 的注册顺序）
 *   控制器方法：Operation → Parameter（path → query → header）→ RequestBody → Response（状态码升序）
 *   Schema 类： Schema → Property（按响应 JSON 中字段出现的顺序）
 */
#[OA\Info(
    version: '1.0.0',
    description: <<<TEXT
    CodeForge 是代码托管与文件管理平台，提供用户管理、文件管理、日志查看等能力。

    统一响应结构：`{ "code": 0, "message": "...", "data": ... }`
    - `code = 0` 表示成功
    - `code > 0` 表示业务错误，取值与 HTTP 状态码一致

    开发环境 CORS 默认放行全部来源。
    TEXT,
    title: 'CodeForge API',
)]
#[OA\Server(url: 'http://localhost:6966', description: '本地开发环境')]
#[OA\Tag(name: '用户', description: '用户增删改查')]
#[OA\Tag(name: '文件', description: '文件上传、列表、下载与删除')]
#[OA\Tag(name: '日志', description: '应用日志查询')]
#[OA\Tag(name: '文档', description: 'API 文档与 OpenAPI 规范')]
#[OA\Tag(name: '其它', description: '非业务接口')]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    description: '输入你的 xAI API Key (格式：Bearer xai-xxx)',
    name: 'Authorization',
    in: 'header',
    bearerFormat: 'JWT',
    scheme: 'bearer'
)]
#[OA\OpenApi(
    security: [['bearerAuth' => []]]
)]
final class OpenApiSpec
{
}
