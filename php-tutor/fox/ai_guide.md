# AI 工作区与 Skill 生成规范

## 1. 目标

为项目建立一套面向 AI Agent 的项目知识与开发辅助体系。

生成的内容主要用于帮助 AI Agent 理解项目的技术背景、架构设计、代码组织方式、开发习惯以及不同开发场景下应该参考的资料。

整体结构包括：

```text
AGENTS.md
.ai/
├── commands/     # 面向具体任务的可复用指令
├── skills/       # 按开发场景组织的项目开发指南
│   └── INDEX.md  # Skill 索引
└── docs/         # 项目介绍、架构说明、技术资料与核心约定
```

这套体系的核心目标不是建立一套“AI 行为规章制度”，而是为 AI Agent 提供一个结构化的项目知识库。

AI 读完这些内容后，应当能够形成类似以下的理解：

> 这是一个什么项目，使用什么技术，代码通常如何组织，为什么采用这种设计，以及在进行某类开发时应该参考哪些资料。

而不是仅仅得到：

> 我需要执行哪些命令、哪些事情不能做。

---

# 2. AGENTS.md 的职责

`AGENTS.md` 是 AI Agent 进入项目后的总入口。

它负责提供项目级别的整体上下文，并告诉 AI：

* 项目是什么；
* 项目解决什么问题；
* 使用什么技术栈；
* 核心目录如何组织；
* `.ai/` 目录承担什么职责；
* 开发过程中有哪些重要的设计原则；
* 不同类型的问题应该参考哪些 Skill 或 Docs。

`AGENTS.md` 应保持相对简洁。

它更像项目的 README + AI 开发入口，而不是完整的开发规范手册。
为了让我知道AI是否遵守规则,必须在AGENTS.md有一条强制规则: 让ai输出"我已经读取了AGENTS.md,并且会遵守规则"
例如可以包含：

```text
# Project Overview

项目简介

# Technology Stack

技术栈

# Architecture

整体架构

# Directory Structure

目录结构

# Development Philosophy

项目开发思路

# AI Resources

.ai/ 目录说明

# Development Workflow

常见开发流程
```

详细的技术知识、框架 API、复杂业务规则等内容不建议全部堆积在 `AGENTS.md` 中，而是分散到 `.ai/docs/` 和 `.ai/skills/`。

---

# 3. `.ai/` 的职责

`.ai/` 是项目专门为 AI Agent 准备的知识与辅助资源目录。

可以将其中的内容理解为三个不同层次：

```text
commands
    ↓
任务入口

skills
    ↓
场景化开发指南

docs
    ↓
详细知识与技术资料
```

三者职责需要保持清晰，不要互相混淆。

---

# 4. commands 的定位

`.ai/commands/` 用于保存**具体任务的可复用指令**。

Command 更接近：

> “当我要让 AI 完成某一类事情时，应该怎么描述任务。”

例如：

```text
.ai/commands/
├── create-api.md
├── create-entity.md
├── create-module.md
├── refactor.md
└── review.md
```

Command 可以包含明确的操作步骤和任务要求。

例如创建 API 的 Command 可以描述：

```text
创建一个新的 API 模块。

根据项目现有架构确定 Controller、Service、DTO、Repository
等代码应该放在哪里。

先参考相关 Skill 和 Doc，再根据现有代码实现。

完成后检查项目已有的测试、格式化和静态检查方式。
```

Commands 本身可以具有较强的任务导向性。

但是，Command 不应该重新复制项目的大量技术知识。

如果需要解释某个框架的具体使用方式，应当引用对应的 Skill 或 Doc。

---

# 5. skills 的定位

`.ai/skills/` 是整个体系中最重要的部分。

Skill 应当被理解为：

> 针对某一个开发场景整理的“项目开发指南”。

Skill 不是规则列表，也不是命令集合。

一个好的 Skill 应该让 AI 理解某个领域在这个项目中是如何工作的。

例如：

```text
.ai/skills/
├── INDEX.md
├── backend/
│   └── SKILL.md
├── api/
│   └── SKILL.md
├── database/
│   └── SKILL.md
└── testing/
    └── SKILL.md
```

也可以根据项目规模采用更简单的结构。

Skill 的组织方式应该根据项目实际情况决定，不需要为了形式强行拆分。

---

# 6. SKILL.md 的写作风格

Skill 正文应该采用自然、专业的技术文档语气。

重点描述：

* 项目的技术背景；
* 架构；
* 设计思路；
* 技术选型；
* 代码组织；
* 常见实现方式；
* 组件之间的关系；
* 为什么采用这种方式；
* 开发某类功能时通常参考什么；
* 哪些地方容易出现问题。

例如推荐这样的表达：

> 项目采用 Service 层承载业务逻辑，Controller 主要负责请求参数转换、调用业务服务以及返回响应。

> 数据访问层基于 Repository 组织。业务代码通常通过 Repository 获取数据，而不是直接操作数据库连接。

> DTO 用于描述 API 输入输出的数据结构，Entity 则负责持久化模型，两者在职责上保持分离。

> 新增接口时，可以参考现有模块的 Controller → Service → Repository 结构，以保持项目内部的一致性。

这种表达是在**解释项目是怎么工作的**。

---

# 7. 避免将 Skill 写成规则清单

不要把 Skill 写成下面这种形式：

```text
- AI 必须使用 Service。
- AI 禁止直接操作数据库。
- AI 必须创建 DTO。
- AI 不得修改 Controller。
- AI 必须执行测试。
- AI 必须执行 lint。
- AI 必须检查 XXX。
```

除非某一项确实属于项目的硬性技术约束。

更推荐将其表达为项目上下文：

```text
项目通常通过 Service 层承载业务逻辑，因此 Controller 保持轻量，
主要处理 HTTP 层相关职责。

数据库访问集中在 Repository 层。业务逻辑通常不会直接操作数据库连接，
这样可以保持持久化细节与业务代码之间的边界。

API 的请求和响应通常使用 DTO 表达，以避免直接暴露持久化 Entity。
```

这样 AI 不只是知道“应该怎么做”，还能够理解“为什么这样做”。

---

# 8. 硬性约束的表达

项目确实存在硬性约束时，可以明确表达。

例如：

> 项目的数据访问统一使用 Doctrine ORM。业务代码禁止直接创建 PDO 连接。

或者：

> API 路由统一使用 `/api/v1` 作为版本前缀。

或者：

> 生产环境禁止使用开发服务器作为正式 HTTP Server。

这类内容属于真正影响项目正确性的约束，可以使用：

* 必须
* 禁止
* 不允许
* 只能

但应该控制数量。

不要为了让 AI“听话”而把普通开发习惯全部包装成硬性规则。

---

# 9. references 的定位

复杂 Skill 不应该把所有知识都塞进 `SKILL.md`。

对于比较详细的内容，可以建立：

```text
skills/
└── api/
    ├── SKILL.md
    └── references/
        ├── authentication.md
        ├── error-handling.md
        ├── pagination.md
        └── examples.md
```

`SKILL.md` 负责建立整体认知。

`references/` 负责提供深入资料。

例如：

```text
SKILL.md
    ↓
介绍 API 在项目中的整体设计

references/authentication.md
    ↓
详细介绍 JWT、Refresh Token、认证流程

references/error-handling.md
    ↓
详细介绍异常类型、错误响应格式

references/pagination.md
    ↓
详细介绍分页参数与响应结构
```

这样可以避免 Skill 变成一个几千行的大型 Markdown 文件。

---

# 10. references 的内容

`references/` 适合保存：

* 框架 API 使用说明；
* 项目内部 API 设计；
* 数据库设计；
* 认证机制；
* 第三方库使用方式；
* 复杂业务规则；
* 代码示例；
* 架构细节；
* 常见问题；
* Migration / Deployment 说明；
* 与项目强相关的官方文档摘要。

这些内容应该具有较强的事实性和参考价值。

Skill 可以在适当的位置说明：

```text
认证流程的详细说明见 references/authentication.md。
```

而不是把整个认证系统重新解释一遍。

---

# 11. scripts 的定位

如果 Skill 需要实际执行工具，可以放在：

```text
skills/
└── xxx/
    ├── SKILL.md
    ├── references/
    └── scripts/
        ├── check.ps1
        ├── generate.ps1
        └── validate.ps1
```

`scripts/` 用于真正可以执行的辅助程序，例如：

* 项目检查；
* 自动生成代码；
* 格式化；
* 测试；
* 构建；
* 环境检查；
* 文件生成；
* 数据转换；
* 项目诊断。

Scripts 是“工具”，不是“说明文字”。

不要把大量 Shell 命令直接塞进 Skill 正文来模拟脚本。

---

# 12. docs 的定位

`.ai/docs/` 是项目的基础知识库。

它与 Skill 的区别在于：

```text
docs
    = 项目知识

skills
    = 如何在某个开发场景下理解和使用这些知识

commands
    = 针对具体任务的操作入口
```

例如：

```text
.ai/docs/
├── project-overview.md
├── architecture.md
├── development.md
├── database.md
├── api.md
└── conventions.md
```

`docs` 更适合描述项目本身，而不是告诉 AI “下一步必须做什么”。

---

# 13. Skill 与 Doc 的关系

可以使用下面的方式判断内容应该放在哪里。

### 如果内容回答：

> “这个项目是什么？”

放到 `docs/`。

### 如果内容回答：

> “这个技术在项目中是怎么使用的？”

可以放到 `docs/` 或 Skill 的 `references/`。

### 如果内容回答：

> “开发这个类型的功能时，应该如何理解和组织代码？”

放到 `skills/`。

### 如果内容回答：

> “我要执行一个具体任务，应该让 AI 做什么？”

放到 `commands/`。

可以形成这样的关系：

```text
AGENTS.md
    │
    └── 项目总览
          │
          ├── docs
          │     └── 项目知识
          │
          └── skills
                ├── 开发场景指南
                │
                └── references
                      └── 深入技术资料

commands
    │
    └── 具体任务入口
```

---

# 14. Skill 的大小控制

`SKILL.md` 应该保持足够精炼。

如果某个章节开始包含大量：

* API 参数；
* 完整代码；
* 第三方库文档；
* 大量示例；
* 复杂业务说明；

应该考虑将其拆到 `references/`。

理想情况下：

```text
SKILL.md
    ↓
告诉 AI “如何理解这个领域”

references/
    ↓
告诉 AI “具体细节是什么”
```

而不是：

```text
SKILL.md
    ↓
把所有东西全部塞进来
```

---

# 15. Skill 的基本结构

一个 Skill 可以采用类似结构：

```markdown
---
name: example
description: 用于 XXX 场景的项目开发指南
---

# XXX 开发指南

## Overview

介绍这个领域在项目中的定位。

## Architecture

介绍整体架构和主要组件。

## Project Structure

介绍相关目录和文件组织方式。

## Design

介绍核心设计思路以及组件之间的关系。

## Development

介绍通常的开发方式。

## Conventions

介绍项目内部常见的编码习惯。

## References

列出需要进一步阅读的详细资料。
```

不需要机械套用全部章节。

根据具体 Skill 的内容进行调整。

---

# 16. INDEX.md 的职责

`.ai/skills/INDEX.md` 用于帮助 AI 快速了解当前有哪些 Skill。

例如：

```markdown
# Skills

| Skill | 用途 |
|---|---|
| backend | 后端整体开发 |
| api | API 开发 |
| database | 数据库与 ORM |
| testing | 测试 |
```

INDEX 主要解决“有哪些 Skill 可以参考”的问题。

具体内容仍然放在对应的 `SKILL.md` 中。

---

# 17. 生成这些文件时的总体原则

生成项目 AI 工作区时，应优先从项目现有代码、配置、依赖和目录结构中提取真实信息。

不要为了填充文档而虚构项目不存在的技术、目录或开发流程。

如果项目已经存在成熟的代码模式，应优先总结现有实现，而不是凭空设计一套新的规范。

对于尚未确定的架构或开发方式，可以描述为建议或推荐，而不要伪装成项目已经存在的硬性规则。

整个体系应该尽可能做到：

```text
真实
    ↓
来自项目现状

清晰
    ↓
AI 可以快速建立上下文

分层
    ↓
AGENTS / docs / skills / commands 各司其职

可扩展
    ↓
详细内容可以继续放入 references

低重复
    ↓
同一份知识尽量只维护一个来源
```

---

# 18. 最终目标

最终生成的 AI 工作区应该让一个新的 AI Agent 在进入项目后逐渐建立这样的认知：

```text
我知道这个项目是什么
        ↓
我知道项目使用什么技术
        ↓
我知道代码大致如何组织
        ↓
我理解主要模块之间的关系
        ↓
我知道项目为什么采用这种设计
        ↓
我知道某类任务应该参考哪个 Skill
        ↓
我知道复杂技术细节应该去哪个 references / docs 查找
        ↓
我可以按照项目现有方式继续开发
```

而不是让 AI 面对一份充满：

```text
必须……
禁止……
不得……
务必……
必须先……
必须再……
禁止直接……
不得修改……
```

的巨大规则文件。

**优秀的 Skill 更接近一个经验丰富的项目成员写给另一个开发者的技术指南，而不是一份 AI 行为条例。**
