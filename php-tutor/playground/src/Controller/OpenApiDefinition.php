<?php

declare(strict_types=1);

namespace Yzqde\Playground\Controller;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 规范的 API 级定义:信息、服务地址、安全方案、标签,以及全局响应包络 schema
 *
 * 本类不参与任何运行时调用,仅由 swagger-php 扫描 src/ 时读取其属性注解。
 * 端点级注解写在各自控制器方法上(见 ImageController、OpenApiDocsController)。
 */
#[OA\Info(
    version: '1.0.0',
    description: <<<'MD'
纯 JSON API,不含任何 HTML 渲染页面。

**认证**:无登录态。非 GET 的 `/api/*` 请求必须携带 `X-Requested-With: XMLHttpRequest`
请求头,否则返回 403;GET 请求无此要求。该头由前端 axios 实例统一注入。

**跨域**:服务端对全部来源放行 CORS(`Access-Control-Allow-Origin: *`),前端可直接跨域调用。

**文件限制**:单文件上限由 `UPLOAD_MAX_SIZE` 决定(默认 10 MB),仅接受
jpg / jpeg / png / gif / webp / bmp,且经 `finfo` 与 `getimagesize()` 双重内容校验。
不支持 svg(内联渲染可执行 JS,存在存储型 XSS 风险)。

**日志查看**:`/api/logs` 系列只允许本机来源(127.0.0.1 / ::1)访问,其他来源返回 403;
需要在内网或生产环境调试时,把 `.env` 的 `LOG_VIEW_ALLOW_ANY` 置为 1。
MD,
    title: '简单图床 API',
)]
#[OA\Server(
    url: 'http://localhost:8080',
    description: '本地开发(php -S localhost:8080)',
)]
#[OA\Tag(name: '系统', description: '健康检查等运行时信息')]
#[OA\Tag(name: '图片', description: '图片的上传、列表、访问与删除')]
#[OA\Tag(name: '文档', description: '本规范与文档页自身')]
#[OA\Tag(name: '日志', description: '按天的 Monolog 日志文件查看,仅本机来源可访问')]
#[OA\Tag(name: '人员', description: '人员信息的增删改查')]
#[OA\Components(
    securitySchemes: [
        new OA\SecurityScheme(
            securityScheme: 'XRequestedWith',
            type: 'apiKey',
            description: '非 GET 的 /api/* 必须携带此头,后端要求固定值 XMLHttpRequest',
            name: 'X-Requested-With',
            in: 'header',
        ),
    ],
)]
#[OA\Schema(
    schema: 'ApiEnvelope',
    title: '统一响应包络',
    description: '所有 JSON 接口的响应结构（由 ApiResponse 类生成）',
    properties: [
        new OA\Property(property: 'success', description: '请求是否成功', type: 'boolean'),
        new OA\Property(
            property: 'message',
            description: '提示信息,成功与失败都可能携带',
            type: 'string',
            nullable: true,
        ),
        new OA\Property(property: 'data', description: '业务数据,具体结构见各端点定义', nullable: true),
        new OA\Property(
            property: 'code',
            description: '业务错误码,仅失败响应携带（前端用于细粒度分支判断）',
            type: 'integer',
            format: 'int64',
            nullable: true,
        ),
    ],
    type: 'object',
)]
final class OpenApiDefinition
{
}
