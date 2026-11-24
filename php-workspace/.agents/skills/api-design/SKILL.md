---
name: api-design
description: REST API 资源设计、错误格式、分页、输入校验、序列化与 OpenAPI 文档的开发指南
---

# API 设计开发指南

## Overview

API 是前后端、服务与服务之间的契约。设计目标不是"能跑"，而是**稳定、可预测、可演进**。

原则：

- 资源用名词，动作用 HTTP 方法表达。
- 状态码表达结果，响应体表达细节。
- 输入输出使用 DTO，不直接暴露数据库模型。
- 错误结构全局统一。
- 契约先于实现，接口文档与代码同步。

## 一、URL 与版本

```text
GET    /api/v1/users
GET    /api/v1/users/{id}
POST   /api/v1/users
PATCH  /api/v1/users/{id}
DELETE /api/v1/users/{id}
GET    /api/v1/users/{id}/orders
POST   /api/v1/orders/{id}/cancel   # 非 CRUD 动作用子资源 + 动词
```

- 版本放路径 `/api/v1`，简单直观；Header 版本适合内部服务。
- 资源名用复数、小写、连字符。
- 不在 URL 中放动词表达 CRUD。
- 嵌套不超过两层，深层关系用查询参数。

## 二、方法与状态码

| 方法 | 语义 | 幂等 |
|---|---|---|
| GET | 查询 | 是 |
| POST | 创建/动作 | 否 |
| PUT | 全量替换 | 是 |
| PATCH | 部分更新 | 否（通常） |
| DELETE | 删除 | 是 |

| 状态码 | 场景 |
|---|---|
| 200 | 查询/更新成功 |
| 201 | 创建成功，带 `Location` |
| 204 | 删除成功，无响应体 |
| 400 | 请求格式错误 |
| 401 | 未认证 |
| 403 | 已认证但无权限 |
| 404 | 资源不存在 |
| 409 | 状态冲突（重复创建、版本冲突） |
| 422 | 参数校验失败 |
| 429 | 限流 |
| 500 | 服务端错误 |

- 不要所有接口都返回 200 再在 body 里写 `code`。
- 401 与 403 不要混用。
- 创建成功返回资源或 ID。

## 三、请求与响应结构

```json
// 成功（单个）
{
  "data": {
    "id": 42,
    "name": "Alice",
    "email": "alice@example.com"
  }
}

// 成功（列表）
{
  "data": [
    { "id": 42, "name": "Alice" }
  ],
  "meta": {
    "page": 1,
    "per_page": 20,
    "total": 135,
    "total_pages": 7
  }
}

// 错误
{
  "error": {
    "code": "VALIDATION_FAILED",
    "message": "The given data was invalid.",
    "details": {
      "email": ["The email field is required."]
    },
    "request_id": "8f3c2a1b9e4d"
  }
}
```

约定：

- 统一外层 `data`，便于扩展 `meta`、`links`。
- 错误统一 `error`，包含机器可读 `code` 与人类可读 `message`。
- 返回 `request_id`，便于排查。
- 时间统一 ISO 8601 带时区。
- 金额用整数最小单位字符串或数值，明确币种。

## 四、分页、过滤与排序

```text
GET /api/v1/users?page=2&per_page=20
GET /api/v1/users?sort=-created_at&status=active
GET /api/v1/users?filter[role]=admin
```

- `per_page` 设上限（如 100）。
- 深分页用游标：`?after=eyJpZCI6MTAwfQ`。
- 排序字段白名单，`-` 表示降序。
- 过滤参数文档化，未知参数忽略或报错保持一致。

## 五、输入校验

### Symfony Validator

```php
use Symfony\Component\Validator\Constraints as Assert;

final class CreateUserRequest
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email = '';

    #[Assert\Length(min: 8, max: 72)]
    public string $password = '';
}

$errors = $validator->validate($dto);
if (count($errors) > 0) {
    // 转 422
}
```

### Valinor（数组映射 + 校验）

```php
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;

try {
    $dto = (new MapperBuilder())
        ->mapper()
        ->map(CreateUserRequest::class, $payload);
} catch (MappingError $e) {
    // 转 422，含字段级错误
}
```

### Respect Validation

```php
use Respect\Validation\Validator as v;

v::email()->assert($payload['email'] ?? '');
```

选择建议：

- Laravel/Symfony 项目用框架内置验证。
- 需要把数组直接映射为强类型 DTO 时用 Valinor。
- 独立小项目用 Respect 或手写校验。

## 六、序列化

### Symfony Serializer

```php
$json = $serializer->serialize($dto, 'json', [
    AbstractNormalizer::GROUPS => ['api'],
]);
```

### JMS Serializer

适合注解/属性驱动的复杂序列化，但配置较重。

### Fractal

```php
$resource = new Item($user, new UserTransformer());
$data = $manager->createData($resource)->toArray();
```

### 手写 Resource

```php
final class UserResource
{
    public static function make(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }
}
```

- 输出结构显式声明，避免模型字段泄漏。
- 日期、金额、枚举统一转换。
- 不要把 ORM 懒加载对象直接 `json_encode()`。

## 七、OpenAPI 文档

### swagger-php

```php
use OpenApi\Attributes as OA;

#[OA\Info(version: '1.0.0', title: 'Acme API')]
#[OA\Get(
    path: '/api/v1/users/{id}',
    summary: 'Get user',
    tags: ['Users'],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [
        new OA\Response(response: 200, description: 'OK'),
        new OA\Response(response: 404, description: 'Not found'),
    ],
)]
final class GetUserAction {}
```

```powershell
vendor/bin/openapi src -o public/openapi.json
```

- 文档从代码生成，避免手写过期。
- 用 Swagger UI / Redoc 渲染。
- CI 校验 OpenAPI 语法。

### 调试工具

| 工具 | 用途 |
|---|---|
| Postman | 集合、环境、自动化 |
| Bruno | 本地优先、Git 友好 |
| Insomnia | 轻量调试 |
| Swagger UI | 文档即调试 |

把接口集合纳入版本库，作为可执行契约。

## 八、认证与限流

- Bearer Token（JWT/Sanctum）放 `Authorization` 头。
- 公开接口与私有接口分组，鉴权中间件按组挂载。
- 限流返回 429，带 `Retry-After`。
- 敏感操作（修改密码、支付）二次校验。

## 九、幂等与并发

- 创建类接口支持 `Idempotency-Key`，服务端记录结果。
- 更新用 `If-Match` / `version` 做乐观锁，冲突返回 409。
- 删除幂等：重复删除返回 204 或 404 保持一致。

## 十、版本演进

- 破坏性变更升版本（`/v2`）。
- 新增字段向后兼容，不删字段、不改类型、不改变语义。
- 弃用字段先在文档标记 `deprecated`，保留过渡期。
- 版本共存期间维护两套 DTO 或适配层。

## 十一、常见坑

- 直接返回 ORM 模型，泄漏密码哈希、内部字段。
- 错误格式各接口不一致，前端难以统一处理。
- 分页 `per_page` 无上限，被拉全表。
- 校验只在 Controller 做，Service 被其他入口调用时绕过。
- 时区不统一，前端显示错乱。
- 枚举值用魔术字符串，变更时无兼容策略。
- 文档手写且长期不更新。

## References

- OpenAPI：<https://spec.openapis.org/oas/latest.html>
- swagger-php：<https://zircote.github.io/swagger-php/>
- Symfony Validator：<https://symfony.com/doc/current/validation.html>
- Valinor：<https://valinor.cuyz.io/>
- Respect Validation：<https://respect-validation.readthedocs.io/>
- Symfony Serializer：<https://symfony.com/doc/current/serializer.html>
