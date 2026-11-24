AI Workspace Version: 1.1.4

# PHP Development AI Workspace

## Project Overview

本工作区是一套面向现代 PHP 开发的 **AI 知识工作区**，不是一个已经完成的业务项目。它的用途是让 AI Agent 在进入 PHP 相关任务时，能够快速判断技术栈、选对框架与库、遵守版本兼容性，并按场景检索到可执行的开发指南。

工作区覆盖的范围：

- PHP 8.x 语言特性、类型系统、运行时机制。
- Composer 2.x 依赖管理与 PSR 标准。
- Laravel、Slim 4、Symfony 等框架的开发方式与选型。
- 日志、依赖注入、HTTP 客户端、数据库、缓存、队列、校验、序列化等常用组件。
- 开发工具链：静态分析、代码风格、测试、调试、性能分析、API 文档。
- 常见架构场景：REST API、CLI、API 网关、异步服务、定时任务。
- Web 安全与生产部署。

本工作区根目录只保存知识库与任务入口，不存放业务源码。具体的 PHP 项目应在独立目录中创建，本工作区为该目录提供 AI 上下文与规范。

## Technology Stack

| 类别 | 技术与版本 |
|---|---|
| 运行时 | PHP 8.5.10（NTS，本机 CLI）；目标兼容范围以项目 `composer.json` 的 `require.php` 与 `config.platform.php` 为准 |
| 包管理 | Composer 2.11.2 |
| 框架 | Laravel、Slim 4、Symfony、Mezzio、Flight、CodeIgniter 4（按项目实际选择，不默认 Laravel） |
| 数据库 | MySQL / PostgreSQL / SQLite，PDO 为统一底层；ORM 视项目而定（Eloquent / Doctrine / Cycle / Medoo） |
| 缓存与队列 | Redis（Predis / PhpRedis）、Symfony Cache、Laravel Queue、Symfony Messenger、RabbitMQ |
| 质量工具 | PHPStan、Psalm、PHP-CS-Fixer、PHP_CodeSniffer、Rector、PHPUnit、Pest、Mockery、Infection |
| 调试与性能 | Xdebug 3.5.3、OPcache、Blackfire / XHProf / Tideways（按需） |
| 本机缺失 | 无 Nginx、MySQL 服务端、Redis 服务端、Docker；涉及这些组件的示例需标注为参考 |

完整环境事实见 `.agents/docs/environment.md`，其中未列出的工具在生成命令前应先实际探测。

## Version Compatibility Policy

PHP 生态的版本差异会直接影响语法可用性与 API 行为，处理任何代码前先确认目标版本：

1. 以项目的 `composer.json`（`require.php`、`config.platform.php`）和部署环境为唯一依据，不以本机 8.5.10 推断目标版本。
2. PHP 8.0、8.1、8.2、8.3、8.4、8.5 的特性差异见 `.agents/docs/php-version-matrix.md`；低版本项目不得使用高版本语法。
3. 框架与库的主版本差异（如 Laravel 10/11/12、Symfony 6/7、Doctrine 3、PHPUnit 10/11/12）以各自官方文档为准，不混用不同大版本的 API。
4. 编写示例时优先使用目标版本稳定可用的语法；使用新特性时明确标注引入版本。
5. 不硬编码已过时的最低版本，也不假设所有项目都使用最新版本。

## Architecture

工作区按四个层次组织，职责不重叠：

```text
AGENTS.md           项目总览、强制规则、任务路由
.agents/docs/       项目知识、技术事实、官方资料索引
.agents/skills/     按开发场景组织的开发指南（怎么工作）
.agents/commands/   具体任务的可复用指令（做什么）
.agents/memory/     工作日志与踩坑记录
```

- **docs** 回答"这个技术是什么、有哪些事实"：版本矩阵、PSR 标准、生态库地图、框架对比、场景方案、官方链接。
- **skills** 回答"这类功能在 PHP 项目中通常如何理解和实现"：语言用法、Laravel、Slim API、数据访问、缓存队列、可观测性、质量测试、安全、API 设计、运行时部署、异步服务。
- **commands** 只写任务步骤，引用 skills 与 docs，不复制技术知识。
- **memory** 只追加，不覆盖。

## Directory Structure

```text
AGENTS.md
.agents/
├── commands/
│   ├── upgrade-ai-workspace.md
│   ├── new-php-project.md
│   ├── new-api-module.md
│   ├── review.md
│   └── diagnose.md
├── skills/
│   ├── INDEX.md
│   ├── php-language/SKILL.md
│   ├── composer/SKILL.md
│   ├── laravel/SKILL.md
│   ├── slim-api/SKILL.md
│   ├── data-access/SKILL.md
│   ├── cache-queue/SKILL.md
│   ├── observability/SKILL.md
│   ├── quality-testing/SKILL.md
│   ├── security/
│   │   ├── SKILL.md
│   │   └── references/owasp-php.md
│   ├── api-design/SKILL.md
│   ├── runtime-deployment/SKILL.md
│   └── async-services/SKILL.md
├── docs/
│   ├── environment.md
│   ├── git_guide.md
│   ├── artifact_guide.md
│   ├── php-version-matrix.md
│   ├── psr-standards.md
│   ├── ecosystem-map.md
│   ├── framework-comparison.md
│   ├── architecture-scenarios.md
│   └── official-sources.md
└── memory/
    ├── worklog.md
    └── known-issues.md
```

## Development Philosophy

- **先识别项目，再动手**：开始编码前先看 `composer.json`、目录结构、现有 Controller / Service / Repository 组织方式、测试与静态分析配置。
- **跟随现有习惯**：已有项目优先沿用其分层、命名、错误处理与日志方式，不强行套用另一套架构。
- **不默认 Laravel**：轻量 API、CLI、网关、常驻进程各有更合适的方案，先看 `.agents/docs/framework-comparison.md`。
- **按任务路由知识**：只读取与当前任务相关的 skill 与 doc，不一次性加载整个工作区。
- **官方文档优先**：API、配置项、版本行为以官方文档为准；社区文章仅作补充。
- **先验证再断言**：不虚构 API、配置项或版本号；不确定时查文档或实际运行。
- **质量与安全属于实现的一部分**：交付前运行项目已有的测试、静态分析和格式检查，并对照安全清单。

## 强制规则

### 规则一：读取确认

AI Agent 开始处理项目任务时，需要先读取 `AGENTS.md`。读取完成后，在首次回复中输出：“我已经读取了AGENTS.md(工作区版本:X.Y.Z),并且会遵守规则”。

`X.Y.Z` 取自本文件首行版本行；版本行缺失或格式错误时，输出原文“我已经读取了AGENTS.md,并且会遵守规则”（不带版本段），不得编造版本号。

### 规则二：坑记录

开发过程中遇到坑（环境问题、依赖冲突、隐蔽的配置要求、容易复发的 Bug、反直觉的工具或 API 行为等）并确认原因或解决方式后，把“现象、原因、解决方式”记录到 `.agents/memory/known-issues.md`；该文件不存在时创建。开始新任务前先查看该文件，避免重复踩坑。

### 规则三：环境约束

开始执行任务前，必须先阅读 .agents/docs/environment.md，并严格按照其中描述的本机环境执行。

不得忽略环境约束，不得自行假设不存在的工具或配置。

环境条款：

- 优先使用 environment.md 中已有的软件和工具。
- 不重复安装已经存在的依赖。
- 不随意修改系统级环境。
- 不改变用户指定的版本。
- 不使用环境中不存在的命令。
- 如果环境信息不足，需要明确说明，而不是自行猜测。

### 规则四：Git 与危险操作安全

开始执行任何 Git 或文件操作前，必须先阅读 .agents/docs/git_guide.md 并严格遵守。

严格禁止主动执行 `git reset --hard`、`git push --force`、`git clean -fd` 等可能丢失数据或改写历史的操作；任何不确定是否会造成数据丢失的操作，都按照危险操作处理，先向用户说明并获得明确授权后才能执行。

补充要点：

- 默认认为工作区中的所有修改都属于用户，除非能确认是当前任务产生的修改。
- 开始任务前先检查 `git status`、`git diff`、`git diff --cached`。
- 不得为了“恢复干净状态”回退、清理或覆盖用户修改。
- 需要撤销修改时优先使用可逆操作：`git stash push`、备份分支、临时补丁。
- 删除类命令（`rm -rf`、`Remove-Item -Recurse` 等）与修改系统配置的操作按危险操作处理。

### 规则五：工作日志

每次 AI 完成一轮实际工作任务、准备向用户交付结果时，必须将本次任务的工作摘要追加记录到当前项目 `.agents/memory/worklog.md`。不得仅在用户明确要求时才记录。

- 记录位置固定为当前项目的 `.agents/memory/worklog.md`（不是 gen-skill 的 `docs/changelog.md`，也不是项目的版本 CHANGELOG）；`.agents/memory/` 目录不存在时创建。
- 每条记录包含：日期与任务标题、任务目标、完成内容、文件变更清单、重要方法与符号变更、验证结果、遗留问题。
- 格式沿用文件中已有格式；只追加，不删除或覆盖已有记录；写入前先查看现有内容，避免同一任务重复记录。
- 只记录本次对话中实际完成的工作；未执行、未验证、推测性的内容不得写成已完成事实。
- 文件变更清单按实际变更区分新增、修改、删除、重命名或移动四类，某类不存在时写“无”；仅阅读未修改的文件不纳入清单；重要代码变更记录到符号级别。
- 验证结果只写实际执行过的构建、测试、静态检查及其真实结果；未验证时明确标注“未验证”及原因。
- 纯分析、排查、设计或配置讨论类任务也记录有实际价值的结论与决策；纯寒暄不写入。
- 同一任务跨多轮交流时形成连续记录，不重复写入相同内容。
- 工作目录不是 Git 仓库或本次没有代码修改时，仍按上述规则记录。
- 因权限、环境等原因无法写入时，明确告知用户失败原因，不得假装已记录成功。
- `worklog.md` 超过 1000 行时，将较早的完整记录迁移到 `.agents/memory/worklog/` 下按时间分卷的文件，并在 `worklog.md` 维护归档索引；迁移保留原始内容，每条只迁移一次。
- 向用户汇报任务结果时，简要说明已更新 `.agents/memory/worklog.md`。

### 规则六：中间产物管理

开发过程中产生的中间产物（解压源码、辅助脚本、临时文件、分析报告、缓存等）必须按 .agents/docs/artifact_guide.md 的规范集中管理，禁止散落在项目根目录、源码目录等无关位置；一次性临时文件在任务结束后必须清理，可复用资源不得随意删除。

- 中间产物统一放入项目根目录 `.ai-workspace/`（extracted / scripts / temp / reports / cache），按类型归位。
- 一次性临时脚本用完必须清理；可复用脚本、第三方参考源码长期保留，不得误删。
- `.ai-workspace/` 必须被 `.gitignore` 忽略，并用 `git check-ignore` 验证。
- 首次应用时按 artifact_guide.md 第四节做一次全面整理；日常开发按第五节持续遵守。
- 对无法确认用途的文件保留原位并记录原因，不做危险清理。

## AI Resources

按任务类型选择入口，不要全量加载：

| 任务 | 先读 |
|---|---|
| 判断 PHP 版本可用特性 | `.agents/docs/php-version-matrix.md` |
| 写现代 PHP 代码、类型、枚举、异常 | `.agents/skills/php-language/SKILL.md` |
| 安装依赖、自动加载、冲突排查 | `.agents/skills/composer/SKILL.md` |
| Laravel 项目开发 | `.agents/skills/laravel/SKILL.md` |
| Slim 轻量 REST API | `.agents/skills/slim-api/SKILL.md` |
| 数据库、ORM、事务、迁移、慢查询 | `.agents/skills/data-access/SKILL.md` |
| Redis、缓存、锁、队列、Worker | `.agents/skills/cache-queue/SKILL.md` |
| 日志、异常处理、调试、性能分析 | `.agents/skills/observability/SKILL.md` |
| 静态分析、代码风格、单元测试 | `.agents/skills/quality-testing/SKILL.md` |
| 安全开发与漏洞排查 | `.agents/skills/security/SKILL.md` |
| REST 设计、校验、序列化、OpenAPI | `.agents/skills/api-design/SKILL.md` |
| PHP-FPM、Nginx、OPcache、部署 | `.agents/skills/runtime-deployment/SKILL.md` |
| ReactPHP / Amp、常驻进程、网关 | `.agents/skills/async-services/SKILL.md` |
| 选框架、选库、方案对比 | `.agents/docs/framework-comparison.md`、`.agents/docs/ecosystem-map.md` |
| 找官方文档 | `.agents/docs/official-sources.md` |
| 了解 PSR 规范 | `.agents/docs/psr-standards.md` |
| 典型项目方案 | `.agents/docs/architecture-scenarios.md` |

## Development Workflow

1. **读取上下文**：`AGENTS.md` → `.agents/docs/environment.md` → 目标项目 `composer.json` 与目录结构。
2. **判定技术栈与版本**：确认 PHP 版本、框架与主要库，避免跨版本误用。
3. **路由知识**：按上表加载对应 skill；需要细节时再读其 `references/` 或 `docs/`。
4. **实现**：沿用项目现有分层与命名；新增文件放在项目既有位置。
5. **验证**：运行项目已有的测试、静态分析、代码风格检查；没有配置时说明未验证原因。
6. **安全复核**：按 `.agents/skills/security/SKILL.md` 检查输入、输出、认证、依赖与密钥。
7. **记录**：追加 `.agents/memory/worklog.md`；遇到坑写入 `.agents/memory/known-issues.md`。
