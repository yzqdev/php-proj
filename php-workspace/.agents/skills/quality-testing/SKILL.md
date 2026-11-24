---
name: quality-testing
description: PHP 静态分析、代码风格、单元测试、Mock 与 CI 质量门禁的开发指南
---

# 代码质量与测试指南

## Overview

质量工具分三层：

```text
格式与风格   PHP-CS-Fixer / PHP_CodeSniffer
静态分析     PHPStan / Psalm / Rector
测试         PHPUnit / Pest / Mockery / Infection / ParaTest
```

原则：工具配置进仓库，命令进 `composer.json` scripts 或 CI，团队统一执行；不要只靠人工 review。

## 一、静态分析

### PHPStan

```neon
# phpstan.neon
parameters:
    level: 8
    paths:
        - src
        - tests
    excludePaths:
        - vendor/*
    phpVersion: 80100   # 按项目实际最低版本
    treatPhpDocTypesAsCertain: false
```

```powershell
composer require --dev phpstan/phpstan
vendor/bin/phpstan analyse --memory-limit=1G
```

- 级别 0~10（新版支持更高），新项目从 6 起步，逐步提升。
- 用 baseline 记录存量问题，避免一次性修复阻塞开发：

```powershell
vendor/bin/phpstan analyse --generate-baseline
```

- `phpVersion` 让分析器按目标版本报告不可用 API。
- 不忽略 `mixed`、`array` 的泛型问题，逐步补充 `array<int, User>`。
- 第三方库缺少类型时安装对应 stub 或用 `@phpstan-*` 注解。

### Psalm

```xml
<!-- psalm.xml -->
<psalm errorLevel="4" resolveFromConfigFile="true">
    <projectFiles>
        <directory name="src"/>
        <ignoreFiles><directory name="vendor"/></ignoreFiles>
    </projectFiles>
</psalm>
```

- Psalm 类型推断强，适合遗留代码逐步收紧。
- `--alter` 可自动补类型，但要 review 结果。

### 选择建议

- 新项目：PHPStan（生态广、规则清晰）。
- 复杂泛型/历史代码：Psalm。
- 两者选其一即可，不必同时跑。

## 二、代码风格

### PHP-CS-Fixer

```php
<?php
// .php-cs-fixer.php
$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        'declare_strict_types' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'strict_comparison' => true,
    ])
    ->setFinder($finder);
```

```powershell
vendor/bin/php-cs-fixer fix --dry-run --diff   # 检查
vendor/bin/php-cs-fixer fix                   # 修复
```

### PHP_CodeSniffer

```powershell
vendor/bin/phpcs --standard=PSR12 src
vendor/bin/phpcbf --standard=PSR12 src
```

- 二者择一，避免规则冲突。
- 格式化提交前自动执行（Husky / pre-commit / IDE on save）。

### Rector

```powershell
vendor/bin/rector process src --dry-run
vendor/bin/rector process src
```

- 用于批量升级 PHP 版本、替换弃用 API、统一代码模式。
- 先 `--dry-run` 看 diff，再分批应用。
- 配合版本升级使用（如 8.1 → 8.3）。

## 三、测试

### 测试分层

```text
Unit         领域逻辑、纯函数、值对象
Integration  数据库、队列、外部服务的真实交互
Feature      路由、认证、校验、响应结构
E2E          关键用户流程（按需）
```

- 单元测试快、稳定，覆盖核心规则。
- 集成测试验证 SQL、事务、序列化。
- Feature 测试验证 HTTP 契约。

### PHPUnit

```php
<?php
declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function test_add_same_currency(): void
    {
        $a = new Money(100, 'CNY');
        $b = new Money(250, 'CNY');

        $this->assertSame(350, $a->add($b)->amount);
    }

    public function test_add_different_currency_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new Money(100, 'CNY'))->add(new Money(100, 'USD'));
    }
}
```

- 一个测试只验证一个行为。
- 测试名描述行为，不写 `test1`。
- 用数据提供器覆盖边界：

```php
#[DataProvider('invalidEmails')]
public function test_rejects_invalid_email(string $email): void
{
    $this->expectException(ValidationException::class);
    (new Email($email))->assertValid();
}

public static function invalidEmails(): array
{
    return [[''], ['a@'], ['@b.com'], ['a b@c.com']];
}
```

### Pest

```php
test('order can be cancelled when pending', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Pending]);

    $order->cancel();

    expect($order->status)->toBe(OrderStatus::Cancelled);
});
```

- Pest 语法更简洁，底层仍是 PHPUnit。
- 团队统一风格，不要同一项目混用两套。

### Mockery

```php
$gateway = Mockery::mock(PaymentGateway::class);
$gateway->shouldReceive('charge')
    ->once()
    ->with(1000)
    ->andReturn(new PaymentResult('ok'));

$service = new PaymentService($gateway);
$service->pay(new Money(1000, 'CNY'));
```

- mock 外部依赖（HTTP、支付、邮件），不要 mock 自己的业务类。
- 验证交互次数与参数，但不过度断言实现细节。

### 覆盖率与变异测试

```powershell
vendor/bin/phpunit --coverage-text
vendor/bin/infection --min-msi=60
```

- 覆盖率是参考，不是目标；关键分支必须覆盖。
- Infection 通过变异代码检验测试是否真的有效。
- 覆盖率报告需要 Xdebug 或 PCOV。

### 并行测试

```powershell
vendor/bin/paratest --processes=4
```

- 数据库测试需隔离（每进程独立库或事务）。
- 并行下共享文件、端口、缓存会冲突。

## 四、CI 质量门禁

```json
{
  "scripts": {
    "cs": "php-cs-fixer fix --dry-run --diff",
    "analyse": "phpstan analyse --memory-limit=1G",
    "test": "phpunit --colors=always",
    "check": ["@cs", "@analyse", "@test"]
  }
}
```

```powershell
composer check
```

CI 顺序建议：格式 → 静态分析 → 单测 → 集成测试 → 安全审计（`composer audit`）。

- 失败即阻断合并。
- 缓存 `vendor/` 与分析结果加速。
- 固定 PHP 版本或跑 matrix。
- 报告上传为 artifact，便于定位。

## 五、常见坑

- 测试依赖执行顺序或共享状态，单独跑失败。
- 测试里连生产/开发数据库。
- 只断言 HTTP 200，不断言响应结构。
- mock 了被测类自身，测不到真实逻辑。
- PHPStan 级别虚高但满屏 ignore。
- 格式化工具与 IDE 配置冲突，diff 反复横跳。
- 覆盖率 100% 但关键异常分支未测。

## References

- PHPUnit：<https://docs.phpunit.de/>
- Pest：<https://pestphp.com/docs/>
- PHPStan：<https://phpstan.org/user-guide/getting-started>
- Psalm：<https://psalm.dev/docs/>
- PHP-CS-Fixer：<https://cs.symfony.com/>
- Rector：<https://getrector.com/documentation>
- Infection：<https://infection.github.io/>
