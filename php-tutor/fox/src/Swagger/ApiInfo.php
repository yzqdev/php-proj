<?php

declare(strict_types=1);

namespace Yzqde\Fox\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: "### xAI (Grok) API 服务规范\n支持文本生成、Chat Completion 以及标准 OpenAI 兼容结构。",
    title: 'xAI Grok API Integration',
    contact: new OA\Contact(
        name: 'API Support',
        url: 'https://x.ai',
        email: 'support@x.ai'
    )
)]
#[OA\Server(
    url: 'https://api.x.ai/v1',
    description: 'xAI Official Production Server'
)]
#[OA\Server(
    url: 'http://localhost:8080/v1',
    description: 'Local Mock / Development Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    description: '输入你的 xAI API Key (格式：Bearer xai-xxx)',
    name: 'Authorization',
    in: 'header',
    bearerFormat: 'JWT',
    scheme: 'bearer'
)]
class ApiInfo
{
}