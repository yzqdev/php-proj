请分析当前项目，并根据项目实际情况生成一套完整的 AI Agent 工作区。

## 一、生成目标

为当前项目建立：

```text
AGENTS.md
.ai/
├── commands/
├── skills/
│   └── INDEX.md
└── docs/
```

其中：

* `AGENTS.md`：项目级 AI Agent 总入口
* `ai_guide.md`：AI 工作区与 Skill 的生成及使用规范
* `.ai/commands/`：具体任务的可复用指令
* `.ai/skills/`：按开发场景组织的项目开发指南
* `.ai/skills/INDEX.md`：Skill 索引
* `.ai/docs/`：项目知识、架构说明和技术资料

---

# 二、最高优先级：ai_guide.md

我会提供一份 `ai_guide.md` 内容。

这份 `ai_guide.md` 是本次生成工作的核心规范。

生成 `AGENTS.md`、`.ai/commands/`、`.ai/skills/`、`.ai/docs/` 时，都需要遵守其中的要求。

不要自行改变 `ai_guide.md` 所规定的：

* AGENTS.md 职责
* commands 职责
* skills 职责
* docs 职责
* Skill 写作风格
* references 的定位
* scripts 的定位
* 文件组织方式
* 内容分层原则

如果项目实际情况与规范存在差异，以项目真实情况为基础进行合理调整，但不要为了满足目录形式而虚构不存在的内容。

---

# 三、生成前先分析项目

在创建任何文件之前，先分析当前项目。

重点分析：

### 项目基本信息

* 项目类型
* 项目用途
* 主要功能
* 技术栈
* 使用的框架
* 使用的语言及版本
* 包管理器
* 构建工具

### 项目结构

分析：

* 根目录
* 核心源码目录
* 配置目录
* 数据访问层
* API 层
* Service / Business 层
* 前端目录
* 测试目录
* 脚本目录
* 部署相关文件

### 代码架构

从现有代码中总结：

* 分层方式
* 模块划分
* 依赖关系
* 数据访问方式
* API 设计方式
* 错误处理方式
* 日志方式
* 配置方式
* 测试方式
* 认证授权方式

### 项目实际开发习惯

优先观察现有代码，而不是自行设计新的规范。

例如：

* 现有 Controller 是如何组织的
* Service 是如何组织的
* Entity / Model 如何定义
* DTO 是否存在
* Repository 是否存在
* 异常如何处理
* 日志如何记录
* 测试如何编写
* 文件如何命名
* 类如何命名

---

# 四、不要凭空设计项目

这是非常重要的要求。

生成的 AI 工作区应该描述**当前项目真实存在的情况**。

不要因为某个技术“通常应该这样做”，就把它写成当前项目已经采用的方案。

例如：

如果项目当前使用 EF Core，就可以描述 EF Core。

如果项目没有 Repository，就不要因为所谓的“标准三层架构”而写：

> 项目通过 Repository 访问数据库。

如果项目没有 Redis，就不要生成 Redis 开发 Skill。

如果项目没有 JWT，就不要凭空创建 JWT 文档。

如果某个设计目前无法从代码中确定：

* 可以省略；
* 或明确标记为建议；
* 不要伪装成现有项目事实。

---

# 五、AGENTS.md

生成一个简洁的 `AGENTS.md`。

它应该是 AI Agent 进入项目后的第一入口。

建议包含：

```text
# Project Overview

# Technology Stack

# Architecture

# Directory Structure

# Development Philosophy

# AI Resources

# Development Workflow
```

具体章节根据项目实际情况调整。

不要把所有技术细节都复制到 `AGENTS.md`。

详细内容应该放入：

```text
.ai/docs/
.ai/skills/
```

---

# 六、AGENTS.md 的规则确认机制

为了让我能够确认 AI 是否真正读取了 `AGENTS.md`：

在 `AGENTS.md` 中加入一条明确的强制规则：

> AI Agent 开始处理项目任务时，需要先读取 `AGENTS.md`。读取完成后，在首次回复中输出：“我已经读取了AGENTS.md,并且会遵守规则”。

请保持这句话的原文，不要自行改写。

这条规则应该放在 `AGENTS.md` 中比较明显的位置。

---

# 七、Skill 的生成原则

Skill 是：

> 针对某个开发场景整理的项目开发指南。

不是：

> AI 行为命令集合。

根据项目实际情况决定需要哪些 Skill。

例如项目存在后端 API，可以生成：

```text
.ai/skills/api/SKILL.md
```

如果存在数据库开发：

```text
.ai/skills/database/SKILL.md
```

如果存在测试：

```text
.ai/skills/testing/SKILL.md
```

但不要机械生成这些目录。

只有项目确实存在对应开发场景时才创建。

---

# 八、Skill 的写作风格

Skill 使用自然、专业的技术文档语气。

重点描述：

* 项目技术背景
* 架构
* 设计思路
* 技术选型
* 代码组织
* 常见实现方式
* 组件之间的关系
* 为什么采用这种方式
* 开发某类功能时通常参考什么
* 容易出现的问题

例如：

```text
项目采用 Service 层承载业务逻辑，Controller 主要负责 HTTP
请求参数转换、调用业务服务以及返回响应。

数据访问层基于 Repository 组织，业务代码通常通过 Repository
获取数据，从而保持业务逻辑与持久化细节之间的边界。
```

不要把 Skill 写成：

```text
AI 必须使用 Service。
AI 禁止直接访问数据库。
AI 必须创建 DTO。
AI 必须执行测试。
AI 不得修改 Controller。
```

只有真正的项目硬性约束才使用：

* 必须
* 禁止
* 不允许
* 只能

普通项目习惯使用：

* 项目采用
* 通常
* 推荐
* 建议
* 可以
* 适合

---

# 九、Skill 与 Doc 必须区分

严格区分：

```text
Skill
= 项目开发指南

Doc
= 项目知识 / 技术资料
```

Skill 主要回答：

> “在这个项目里，这类功能通常如何理解和开发？”

Doc 主要回答：

> “这个项目是什么，以及相关技术具体是什么、如何工作？”

例如：

```text
skills/api/SKILL.md
```

介绍 API 在当前项目中的整体设计。

而：

```text
docs/api.md
```

可以介绍项目 API 的具体结构、请求响应格式、错误码、认证方式等详细资料。

不要把同一份内容重复复制到多个地方。

---

# 十、Commands

`.ai/commands/` 用于保存具体任务的可复用指令。

例如项目实际适合的情况下，可以生成：

```text
create-api.md
create-module.md
create-entity.md
refactor.md
review.md
```

Command 可以具有较强的任务导向性。

例如：

```text
创建一个新的 API 模块。

先分析现有模块的实现方式，
参考对应 Skill 和 Doc，
按照当前项目的架构完成实现，
最后运行项目已有的检查和测试。
```

但是不要在 Command 中重复大量技术知识。

Command 应该引用：

```text
.ai/skills/
.ai/docs/
```

而不是复制它们。

---

# 十一、Docs

`.ai/docs/` 是项目基础知识库。

根据项目实际情况生成合适的文档，例如：

```text
project-overview.md
architecture.md
development.md
database.md
api.md
configuration.md
testing.md
deployment.md
```

不要机械生成全部文件。

只创建真正有价值的文档。

Docs 应该尽可能基于：

* 项目源码
* 配置文件
* package / dependency 文件
* README
* 构建文件
* 测试
* 实际代码

进行总结。

不要凭空创造项目不存在的内容。

---

# 十二、内容分层

生成过程中遵循：

```text
AGENTS.md
    ↓
项目总览 + AI 入口

.ai/docs/
    ↓
项目知识

.ai/skills/
    ↓
场景化开发指南

.ai/commands/
    ↓
具体任务入口
```

不要让四个层级互相重复。

---

# 十三、文件数量控制

不要为了看起来完整而生成大量 Markdown。

优先：

```text
少而有用
```

而不是：

```text
多而重复
```

如果一个 Doc 没有实际价值，就不要创建。

如果一个 Skill 可以覆盖多个相关场景，也不需要强行拆成多个 Skill。

目录结构应该根据项目规模自然形成。

---

# 十四、语言

所有 AI 工作区文档默认使用中文。

技术名称保持官方英文，例如：

```text
ASP.NET Core
Entity Framework Core
JWT
Redis
Controller
Service
Repository
DTO
Entity
OpenAPI
```

代码、类名、方法名、配置项保持原始形式。

不要为了中文化而翻译技术标识符。

---

# 十五、生成完成后检查

生成完成后，检查：

1. `AGENTS.md` 是否存在。
2. `ai_guide.md` 是否存在。
3. `.ai/skills/INDEX.md` 是否存在。
4. 所有 Skill 是否符合 Skill 的定位。
5. Docs 是否真正描述项目知识。
6. Commands 是否真正对应具体任务。
7. 是否存在大量重复内容。
8. 是否虚构了项目不存在的技术或架构。
9. Skill 是否被写成大量命令式规则。
10. `AGENTS.md` 是否包含指定的规则确认语句。
11. Skill 和 Doc 是否进行了合理分层。
12. 文件引用的路径是否真实存在。

如果发现重复内容，应优先保留一个权威来源，并让其他文件引用它。

---

# 十六、最终输出

完成后简要说明：

```text
已完成 AI 工作区生成。

AGENTS.md
.ai/
├── ai_guide.md
├── commands/
├── skills/
│   └── INDEX.md
└── docs/
```

然后列出实际生成的文件，并简要说明每个文件的用途。

不要输出大量生成过程。

---

最后，我会把 `ai_guide.md` 的具体内容提供给你,他就在当前项目根目录。

**在生成任何文件之前，先完整读取并理解 `ai_guide.md`，然后严格按照其中的规范执行。**
