<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Generator;

/**
 * ============================================================
 * OpenAPI Spec 生成器（基于 zircote/swagger-php）
 * ============================================================
 *
 * 工作原理（类 playground 的 OpenApiSpecService）：
 *   1. Generator 扫描整个 app/ 目录，自动发现所有 #[OA\...] 属性注解
 *   2. #[OA\OpenApi] 全局定义在 Spec.php（info / servers / tags / securitySchemes）
 *   3. 组件 schema 直接定义在 Entity 类上（#[OA\Schema] + #[OA\Property]）
 *      不再需要单独的 Schemas.php 手写数组
 *   4. Controller 方法上的 #[OA\Get/Post/Put/Delete] 提供端点级注解
 *
 * 对比旧实现（手写反射扫描）：
 *   - 旧：自己解析 PHPDoc → 组装数组（400+ 行代码）
 *   - 新：使用 swagger-php 官方库 → 几行代码调用，spec 格式由库保证
 *   - 更新：schema 已迁移到 Entity 上，不再手动合并 Schemas::all()
 *
 * 对应 Java / Spring Boot：
 *   - 类似 springdoc-openapi 自动扫描 @RestController + @GetMapping + @Schema
 *   - 区别：Java 用注解（compile-time），PHP 用 PHP 8 属性注解（runtime）
 */
final class SpecGenerator
{
    /**
     * 生成 OpenAPI 3.0 规范数组
     *
     * @return array<string, mixed>
     */
    public static function generate(): array
    {
        $generator = new Generator();

        // 扫描整个 app/ 目录：Controller 的 #[OA\Get/...] + Entity 的 #[OA\Schema]
        // + Spec.php 的 #[OA\OpenApi] 全局定义，一次性全部发现
        $openapi = $generator->generate(
            [dirname(__DIR__)],
            validate: false,
        );

        if ($openapi === null) {
            return [];
        }

        // 将 OpenApi 对象转为 PHP 数组
        return json_decode(
            json_encode($openapi, JSON_UNESCAPED_SLASHES),
            true,
        ) ?? [];
    }
}
