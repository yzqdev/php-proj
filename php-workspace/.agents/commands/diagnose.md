# 排查 PHP 问题

定位运行时错误、性能问题、依赖冲突或部署异常。

## 开始前

1. 读取 `AGENTS.md`、`.agents/docs/environment.md`，先看 `.agents/memory/known-issues.md` 是否已有同类问题。
2. 收集现象：报错原文、复现步骤、PHP 版本、框架版本、最近改动、日志片段。
3. 明确问题类型：启动失败、运行时异常、性能慢、内存高、依赖冲突、部署后不生效。

## 按类型排查

### 启动/加载失败

- 读完整错误，确认第一个失败点。
- `composer dump-autoload`、`composer validate`。
- 检查 PHP 版本与扩展是否满足 `composer.json`。
- 检查文件权限与路径大小写（Windows 不敏感、Linux 敏感）。

### 运行时异常

- 看异常类型、消息、堆栈、`previous`。
- 开启开发环境详细错误，生产只记日志。
- 用 Xdebug 断点复现；参考 `.agents/skills/observability/SKILL.md`。

### 性能慢

1. 定位层级：Web Server → PHP → 数据库 → 外部服务。
2. 看 FPM slowlog、应用阶段耗时日志、数据库慢查询与 `EXPLAIN`。
3. 用 profiler 找函数级热点。
4. 修复后压测对比。

### 内存高

- `memory_get_peak_usage(true)` 打点。
- 检查大数组、`file_get_contents()` 大文件、ORM 全量加载。
- 长驻进程检查静态缓存与闭包引用。

### 依赖冲突

- 读第一个冲突点。
- `composer why-not`、`composer show --tree`。
- 检查 `config.platform.php`。
- 参考 `.agents/skills/composer/SKILL.md`。

### 部署后不生效

- OPcache 是否重启。
- 配置缓存是否刷新。
- 队列 Worker 是否重启。
- 代码目录与 `root` 是否一致。

## 输出

```text
现象：
根因：
证据：（日志/命令输出/代码位置）
修复：
验证：
预防：
```

## 完成后

- 更新 `.agents/memory/worklog.md`。
- 把确认的坑写入 `.agents/memory/known-issues.md`。
