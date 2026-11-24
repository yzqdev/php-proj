# 升级 AI 工作区

把当前项目的 AI 工作区（`AGENTS.md` 与 `.agents/`）升级到 gen-skill 的最新规则。

## 执行要求

严格按 gen-skill 的 `references/gen_skill.md` 第十七节的升级流程执行，要点如下：

1. **读取当前版本**：读取本项目 `AGENTS.md` 首行 `AI Workspace Version:`。版本行不存在或格式错误时，如实记录，视为"未对齐任何 gen-skill 版本"，不要猜测历史版本。
2. **读取变更历史**：读取 gen-skill 的 `docs/changelog.md`，了解各次 gen-skill 变更及其"对已生成项目工作区的影响"，对照当前版本号确定需要同步的规则。
3. **盘点现状**：检查本项目的 `AGENTS.md`、`.agents/skills/`、`.agents/docs/`、`.agents/commands/`、`.agents/memory/worklog.md`、`.agents/memory/known-issues.md`，确认已有能力与用户定制内容。
4. **增量升级**：只补充缺失规则、修正过时内容；**禁止覆盖或重建用户已积累的 skills / docs / commands / worklog / known-issues**；本次需求与现有规则冲突时先整合再写入。
5. **更新版本号**：把 `AGENTS.md` 首行直接写成 gen-skill 当前版本（gen-skill `SKILL.md` frontmatter `version` 字段），**不做独立语义判断**——项目工作区版本号始终等于最近一次同步到的 gen-skill 版本；项目特有定制不递增版本号，记入 worklog 即可。
6. **验证**：按 gen_skill.md 第十五节检查清单自检；重点核对文件引用、规则冲突、重复内容、版本行正确性。
7. **记录**：把本次升级摘要（前后版本号、同步了哪些变更、涉及文件、验证结果、遗留问题）追加到 `.agents/memory/worklog.md`。

## 环境与安全约束

升级过程同样受 `AGENTS.md` 全部强制规则约束：先读 `.agents/docs/environment.md` 与 `.agents/docs/git_guide.md`；涉及文件操作时遵守危险操作禁令，不得覆盖用户数据。

## 输出

完成后简要说明：升级前后版本号、同步的变更清单、涉及文件、验证结果、遗留问题。
