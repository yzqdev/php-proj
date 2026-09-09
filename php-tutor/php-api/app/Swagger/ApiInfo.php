<?php

declare(strict_types=1);

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'PHP Tutor API',
    description: "多用户博客系统的 REST API，支持文章、评论和 JWT 认证。\n\n"
        . "## 认证方式\n\n"
        . "使用 Bearer Token 认证，在请求头中携带 `Authorization: Bearer <token>`。\n\n"
        . "## 响应格式\n\n"
        . "所有 API 响应遵循统一的 JSON 格式：\n"
        . "```json\n"
        . "{\n"
        . "  \"data\": { ... },\n"
        . "  \"request_id\": \"xxx\"\n"
        . "}\n"
        . "```"
)]
#[OA\Server(
    url: 'http://localhost:8080',
    description: '本地开发服务器'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    description: '输入 JWT Token（格式：Bearer <token>）',
    name: 'Authorization',
    in: 'header',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
#[OA\Schema(
    schema: 'User',
    description: '用户信息',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'username', type: 'string', example: 'johndoe'),
        new OA\Property(property: 'email', type: 'string', example: 'john@example.com'),
        new OA\Property(property: 'created_at', type: 'string', example: '2024-01-01 00:00:00'),
        new OA\Property(property: 'updated_at', type: 'string', example: '2024-01-01 00:00:00'),
    ]
)]
#[OA\Schema(
    schema: 'TokenResponse',
    description: '登录/注册/刷新返回的 Token 响应',
    properties: [
        new OA\Property(property: 'user', ref: '#/components/schemas/User'),
        new OA\Property(property: 'access_token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGc...'),
        new OA\Property(property: 'refresh_token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGc...'),
    ]
)]
#[OA\Schema(
    schema: 'Article',
    description: '文章信息',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: '文章标题'),
        new OA\Property(property: 'body', type: 'string', example: '文章内容'),
        new OA\Property(property: 'created_at', type: 'string', example: '2024-01-01 00:00:00'),
        new OA\Property(property: 'updated_at', type: 'string', example: '2024-01-01 00:00:00'),
        new OA\Property(
            property: 'author',
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'username', type: 'string', example: 'johndoe'),
            ]
        ),
    ]
)]
#[OA\Schema(
    schema: 'ArticleList',
    description: '文章列表响应',
    properties: [
        new OA\Property(
            property: 'items',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Article')
        ),
        new OA\Property(
            property: 'pagination',
            type: 'object',
            properties: [
                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                new OA\Property(property: 'total', type: 'integer', example: 100),
                new OA\Property(property: 'last_page', type: 'integer', example: 7),
            ]
        ),
    ]
)]
#[OA\Schema(
    schema: 'Comment',
    description: '评论信息',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'article_id', type: 'integer', example: 1),
        new OA\Property(property: 'content', type: 'string', example: '这是一条评论'),
        new OA\Property(property: 'created_at', type: 'string', example: '2024-01-01 00:00:00'),
        new OA\Property(
            property: 'author',
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'username', type: 'string', example: 'johndoe'),
            ]
        ),
    ]
)]
#[OA\Schema(
    schema: 'CommentList',
    description: '评论列表响应',
    properties: [
        new OA\Property(
            property: 'items',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Comment')
        ),
        new OA\Property(
            property: 'pagination',
            type: 'object',
            properties: [
                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                new OA\Property(property: 'total', type: 'integer', example: 50),
                new OA\Property(property: 'last_page', type: 'integer', example: 4),
            ]
        ),
    ]
)]
class ApiInfo
{
}
