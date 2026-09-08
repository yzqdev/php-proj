<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use OpenApi\Attributes as OA;

/**
 * ============================================================
 * OpenAPI 3.0 全局规范（#[OA\OpenApi] 属性）
 * ============================================================
 *
 * swagger-php 通过反射扫描所有 Controller 类上的 #[OA\Get] / #[OA\Post] /
 * #[OA\Put] / #[OA\Delete] 属性注解，自动组装成 OpenAPI 3.0 spec。
 *
 * 本类只放"全局"信息（不随具体接口变化）：
 *   - info（标题 / 描述 / 版本 / 联系方式 / license）
 *   - servers（同域 + 本地开发两个地址）
 *   - tags（分组标签，顺序决定 Swagger UI 排列）
 *   - securitySchemes（bearerAuth，JWT Bearer Token）
 *   - components/parameters（可复用的 path 参数定义）
 *
 * 接口级 schema（Article / Activity / User 等）直接定义在 Entity 类上，
 * 通过 #[OA\Schema] + #[OA\Property] 属性注解声明，不再需要单独的 Schemas.php。
 *
 * 对应 Java / Spring Boot：
 *   - 类似 springdoc 在 application.yml 里配 springdoc.info.title / servers 等；
 *   - 区别：Java 用配置文件，PHP 用 #[OA\OpenApi] 属性注解。
 */

/**
 * 全局 OpenAPI 配置声明
 * 规则：所有顶级节点（OpenApi、Info、Server、Tag、Components）平铺在类顶部
 */
#[OA\OpenApi(openapi: '3.0.3')]
#[OA\Info(
    version: '1.0.0',
    description: 'PHP 8.5 无框架后端 + Vue 3 SPA 前后端分离项目的 REST API。' . "\n\n"
    . '鉴权方式：Bearer Token（JWT Access + Refresh Token）' . "\n"
    . '响应结构：统一 envelope {code, message, data, errors?}' . "\n"
    . '业务 code：0=成功, 40101=未登录, 40102=token 无效, 40301=无权限, 40401=资源不存在, 42201=校验失败, 50000=内部错误',
    title: 'php-learn API',
    contact: new OA\Contact(
        name: 'php-learn',
        url: 'https://github.com/example/php-learn'
    ),
    license: new OA\License(name: 'MIT')
)]
#[OA\Server(url: '/api/v1', description: 'API v1（同域部署）')]
#[OA\Server(url: 'http://127.0.0.1:8000/api/v1', description: '本地开发')]

#[OA\Tag(name: 'Health', description: 'Health 相关接口')]
#[OA\Tag(name: 'Auth', description: 'Auth 相关接口')]
#[OA\Tag(name: 'Articles', description: 'Articles 相关接口')]
#[OA\Tag(name: 'Activities', description: 'Activities 相关接口')]

#[OA\Components(
    parameters: [
        'Id' => new OA\Parameter(
            name: 'id',
            description: '资源 ID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer', minimum: 1),
            example: 1
        )
    ],
    securitySchemes: [
        'bearerAuth' => new OA\SecurityScheme(
            securityScheme: 'bearerAuth',
            type: 'http',
            description: 'Bearer Token（JWT Access Token），通过登录接口获取',
            bearerFormat: 'JWT',
            scheme: 'bearer'
        )
    ]
)]
class Spec
{
}