# AGENTS.md — PHP 项目通用开发规范

> 本文件是 AI 编码代理（Agent）在本仓库中工作的**唯一行为准则**。
> 代理在开始任何任务前必须先完整阅读本文件，并严格遵守。
> 项目特有的、与本文件冲突的约定，一律以本文件下方的「项目自定义补充」为准。

---

## 1. 基本原则（最高优先级）

1. **先读后改**：修改任何文件前，必须先读取并理解现有代码，禁止凭空重写整个文件。
2. **最小改动**：只完成被明确要求的任务，不顺手重构、不顺手"优化"、不删除看似无用的代码。
3. **不臆造 API**：禁止凭记忆编造函数签名、类方法、配置项、Composer 包名。不确定的必须先在仓库内检索，或明确告知用户"无法确认"。
4. **保持一致性**：新增代码必须贴合周边代码的风格（命名、分层、错误处理方式），哪怕与通用最佳实践略有出入。一致性 > 个人偏好。
5. **不确定就问**：需求存在歧义、有多种合理实现、或改动会影响线上行为时，先停下来询问，不要猜测。
6. **禁止提交**：不要执行 `git commit`、`git push`，除非用户明确要求。
7. **禁止破坏性命令**：不执行 `rm -rf`、`git reset --hard`、`git clean -fd`、数据库 DROP/TRUNCATE 等不可逆操作。

---

## 2. 环境与工具链

### 2.1 版本要求
- **PHP >= 8.2**，推荐 **8.4+**。
- 启用严格类型：所有 PHP 文件（模板文件除外）第一行必须是 `<?php`，第二行必须是 `declare(strict_types=1);`。
- Composer 2.x。

### 2.2 常用命令（执行前先确认仓库里实际存在哪些工具）

```bash
composer install                 # 安装依赖（禁止随意 composer update）
composer dump-autoload -o        # 新增类后刷新自动加载

vendor/bin/php-cs-fixer fix .    # 自动修复代码风格
vendor/bin/phpcs                 # 代码风格检查
vendor/bin/phpstan analyse       # 静态分析
vendor/bin/phpunit               # 运行测试（无 phpunit 时用 vendor/bin/pest）
```

**硬性要求**：提交前必须保证 静态分析 + 代码风格 + 测试 三项全部通过。若某项工具未在仓库中配置，说明情况，不要自行安装依赖。

---

## 3. 编码规范

### 3.1 遵循标准
- 遵循 **PSR-1**（基础编码规范）、**PSR-4**（自动加载）、**PSR-12**（编码风格扩展）。
- 类文件命名与类名完全一致（含大小写），一个文件只定义一个类/接口/枚举/trait。

### 3.2 命名约定

| 对象 | 规则 | 示例 |
|---|---|---|
| 类 / 接口 / 枚举 / trait | `UpperCamelCase`，名词 | `UserRepository`、`OrderStatus` |
| 方法 / 函数 | `lowerCamelCase`，动词开头 | `getById()`、`validateOrder()` |
| 变量 / 属性 / 参数 | `lowerCamelCase`，名词 | `$orderList`、`$userId` |
| 常量 | `UPPER_SNAKE_CASE` | `MAX_RETRY_COUNT` |
| 枚举 case | `UpperCamelCase` | `OrderStatus::PendingPayment` |
| 数据库表 / 字段 | `snake_case`，表名复数 | `users`、`created_at` |
| 布尔值方法 | `is` / `has` / `can` / `should` 前缀 | `isValid()`、`hasPermission()` |
| 私有成员 | 不加下划线前缀 | `$this->logger` |

### 3.3 类型声明
- 所有函数/方法的参数与返回值**必须**声明类型（含 `void`、`never`、`?Type` 可空类型）。
- 优先使用原生类型声明，而非仅写在 PHPDoc 中。
- 能用 `enum` 就不要用字符串/整型常量集合。
- 属性必须声明类型（PHP 7.4+ 支持），推荐配合构造函数属性提升（constructor property promotion）。

### 3.4 现代 PHP 特性优先
- 优先使用：`enum`、`readonly` 类与属性、构造函数属性提升、`match` 表达式、命名参数、空安全运算符 `?->`、空合并赋值 `??=`、First-class callable 语法 `strlen(...)`、数组解包（含字符串键）。
- 避免过度使用：反射、`eval()`、可变变量 `$$var`、动态属性（PHP 8.2 已废弃）、`@` 错误抑制符。

### 3.5 格式细节
- 缩进 4 个空格，禁止 Tab。
- 行宽建议 **120 字符**以内，硬性上限 120。
- 文件末尾保留一个空行，且**省略**闭合标签 `?>`。
- 使用 `use` 导入类，禁止在代码中写全限定名（FQCN 字符串形式的配置除外）；`use` 语句按字母序排列，不使用 `use function` / `use const` 除非必要。
- 字符串：无变量插值一律用单引号；需要插值用双引号；不要用 heredoc 拼接 SQL 或 HTML 大段模板。

### 3.6 注释与文档
- 注释解释 **为什么**（why），而非 **做什么**（what）。显而易见的代码不写注释。
- 公共 API、非自明的方法、有副作用的方法必须写 PHPDoc，包含 `@param`、`@return`、`@throws`。
- 注释语言：**中文**（与团队沟通语言保持一致）；代码标识符一律英文。
- 禁止保留被注释掉的代码块，需要历史请用 Git。
- 待办事项统一写 `// TODO(负责人): 说明`，禁止无主 TODO。

---

## 4. 架构与分层

### 4.1 目录约定（Laravel / Symfony / 通用框架按各自约定，无框架时按此）

```
app/           # 业务代码
  Controller/  # 只做参数校验、调用 Service、返回响应，禁止写业务逻辑
  Service/     # 业务编排，事务边界通常在此
  Repository/  # 数据访问，禁止在此写业务逻辑
  Model/       # 数据模型 / 实体
  DTO/         # 数据传输对象（readonly 类）
  Enum/        # 枚举
  Exception/   # 业务异常
config/        # 配置
database/      # 迁移与种子
public/        # Web 入口，唯一对外暴露目录
routes/        # 路由定义
tests/         # 测试
```

### 4.2 分层规则
- **单向依赖**：Controller → Service → Repository → Model。禁止反向依赖，禁止跨层直连（Controller 不得直接调用 Repository）。
- **Controller 保持精简**：单个方法不超过 40 行，超过即拆分到 Service。
- **Service 单一职责**：一个类只负责一个业务领域，方法超过 80 行须考虑拆分。
- **禁止在任意层写原生 SQL 拼接**（见第 6 节）。
- 跨模块复用逻辑放入 Service 或独立的工具类，禁止互相调用 Controller。

---

## 5. 错误处理与异常

- 使用**异常**传递错误，禁止返回 `false` / `null` / 错误码数组表示失败（查询无结果返回 `null` 除外）。
- 禁止用异常做正常流程控制。
- 捕获异常必须**具体化**：`catch (SpecificException $e)`，禁止裸 `catch (Throwable)` 后静默吞掉（框架顶层兜底除外）。
- 自定义业务异常继承统一的基类，并携带明确的错误信息与上下文。
- 面向用户的错误消息必须友好且**不泄露**堆栈、SQL、路径、配置等内部信息。
- 全局异常处理统一记录日志并返回结构化 JSON 响应。
- 禁止使用 `@` 抑制错误，禁止 `die()` / `exit()`（CLI 脚本除外）。

---

## 6. 安全规范（不可协商）

1. **SQL 注入**：一律使用预处理语句 / 查询构造器 / ORM 的参数绑定。字符串拼接 SQL 一律禁止。
2. **XSS**：输出到 HTML 前必须转义；富文本须经白名单过滤（如 HTMLPurifier）。
3. **CSRF**：所有非 GET 的状态变更请求必须校验 CSRF Token。
4. **密码**：只用 `password_hash()` / `password_verify()`（默认 `PASSWORD_BCRYPT` 或 `PASSWORD_ARGON2ID`）。禁止 MD5/SHA1、禁止自建加密、禁止可逆加密存储密码。
5. **文件上传**：校验 MIME 与扩展名白名单、限制大小、重命名存储、存储目录禁止执行 PHP。
6. **反序列化**：禁止 `unserialize()` 处理用户输入，使用 `json_decode()`。
7. **敏感信息**：禁止硬编码密钥、密码、Token，一律走环境变量（`getenv()` / `.env`）。禁止把密钥写进日志或提交进 Git。
8. **越权访问**：任何按 ID 查询/修改资源的操作，必须校验当前用户对该资源的归属权限。
9. **直接输入函数黑名单**：`eval`、`assert`（字符串参数）、`system`、`exec`、`passthru`、`shell_exec`、`proc_open`、`popen`、`create_function`——除非明确获批，否则禁止使用；如必须使用 `escapeshellarg()`。
10. **随机数**：安全场景（Token、验证码、重置码）使用 `random_bytes()` / `random_int()`，禁止 `rand()` / `mt_rand()` / `uniqid()`。

---

## 7. 数据库

- 表与字段使用 `snake_case`；主键统一 `id`（bigint 自增或 UUID）。
- 时间字段：`created_at`、`updated_at`，软删除用 `deleted_at`。
- **所有表结构变更必须通过迁移文件（migration）**，禁止手工改库、禁止改已上线的迁移文件。
- 迁移必须可回滚（`down()` 完整实现）。
- 为高频查询字段建索引；外键关系加索引；避免在大表上做无索引的 `LIKE '%x%'`。
- 警惕 N+1 查询，列表场景显式预加载关联（`with()` / `eagerLoad`）。
- 写操作（多步修改）必须包在数据库事务中。
- 禁止 `SELECT *`，按需取字段。

---

## 8. 测试

- 测试框架：**PHPUnit** 或 **Pest**（跟随仓库现有选择）。
- 目录结构：`tests/Unit/`、`tests/Feature/`，测试类以 `Test` 结尾，方法以 `test` 开头或加 `@test` 注解。
- 新增业务逻辑 / 修复 Bug **必须**附带测试用例。
- 测试三要素：**Arrange（准备）→ Act（执行）→ Assert（断言）**。
- 每个测试只验证一个行为，测试之间互不依赖、可独立运行、可重复执行。
- 禁止在测试中连接生产数据库、调用真实外部服务（用 Mock / 内存数据库 / 事务回滚）。
- 断言要具体：`assertSame` 优于 `assertEquals`，验证异常用 `expectException`。

---

## 9. 依赖管理

- 新增第三方包**必须事先征得用户同意**，并说明理由、许可证、维护活跃度。
- `composer.json` 与 `composer.lock` 同时提交；`composer.lock` 不可手工修改。
- 生产环境安装使用 `composer install --no-dev --optimize-autoloader`。
- 禁止把 `vendor/` 提交进 Git。
- 一个依赖只做一件事，禁止引入"大而全"的包解决小问题。

---

## 10. 日志与配置

- 使用 PSR-3 日志接口（如 Monolog），禁止 `echo` / `var_dump` / `print_r` / `error_log` 输出调试信息。
- 日志级别：DEBUG 调试、INFO 关键流程、WARNING 可恢复异常、ERROR 需要人工介入。
- 日志必须包含可追踪上下文（请求 ID、用户 ID、订单号），但**禁止记录**密码、身份证、银行卡、完整 Token。
- 配置与代码分离，环境差异通过环境变量注入；提交 `.env.example`，绝不提交 `.env`。

---

## 11. Git 提交规范

- 分支命名：`feature/xxx`、`bugfix/xxx`、`hotfix/xxx`、`refactor/xxx`。
- 提交信息遵循 **Conventional Commits**：`类型(范围): 简短描述`
    - 类型：`feat` / `fix` / `docs` / `style` / `refactor` / `perf` / `test` / `chore`
    - 示例：`fix(order): 修复优惠券叠加时金额计算错误`
- 一次提交只做一件事，禁止把无关改动混在一起。

---

## 12. 性能与规范红线

- 禁止在循环中执行数据库查询、HTTP 请求、文件 IO。
- 高频读、低频写的数据走缓存，并设置合理过期时间与缓存击穿/雪崩防护。
- 大批量数据处理使用游标 / 分块（`chunk`），避免一次性载入内存。
- 耗时长任务（发邮件、生成报表、调用外部 API）投递到队列异步执行。
- API 响应统一结构（如 `{ code, message, data }`），HTTP 状态码语义正确。

---

## 13. 交付前自检清单

完成任务后，代理必须逐项确认：

- [ ] 已阅读并理解了被修改文件的现有实现
- [ ] 改动范围最小，未夹带无关重构
- [ ] 新增代码有完整类型声明与 `declare(strict_types=1);`
- [ ] 无硬编码密钥、无调试残留（`var_dump`、`dd()`、`console.log`）
- [ ] 无 SQL 注入 / XSS / 越权风险
- [ ] 静态分析、代码风格检查、测试全部通过
- [ ] 新增功能配有测试
- [ ] 数据库变更已生成迁移文件
- [ ] 已向用户说明改动点、影响范围与仍需人工确认的事项

---

 