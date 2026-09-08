# Thunder

Thunder 是一个基于 [Symfony Console](https://symfony.com/doc/current/console.html) 的 PHP CLI 文件操作工具，提供常用文件系统命令的跨平台封装。

## 系统要求

- PHP >= 8.2
- [Composer](https://getcomposer.org/)

## 安装

### 本地使用

```bash
git clone <仓库地址>
cd tools/thunder
composer install
php bin/thunder <command>
```

### 全局安装（推荐）

**Windows:**

```bash
# 1. 配置路径仓库
composer global config repositories.thunder path "F:/PhpStormProjects/php-proj/tools/thunder"

# 2. 全局安装
composer global require yzqde/thunder
```

全局 bin 目录：`%APPDATA%\Composer\vendor\bin`，确保此路径已加入系统 PATH。

**Linux / macOS:**

```bash
# 1. 配置路径仓库
composer global config repositories.thunder path "/path/to/php-proj/tools/thunder"

# 2. 全局安装
composer global require yzqde/thunder
```

全局 bin 目录：`~/.composer/vendor/bin`（Composer 2.x 为 `~/.config/composer/vendor/bin`），确保此路径已加入 PATH：

```bash
echo 'export PATH="$HOME/.composer/vendor/bin:$PATH"' >> ~/.bashrc
source ~/.bashrc
```

### 全局卸载

```bash
composer global remove yzqde/thunder

# 可选：移除路径仓库配置
composer global config --unset repositories.thunder
```

卸载后 `thunder` 命令将不再可用。

## 命令一览

| 命令 | 说明 | 示例 |
|------|------|------|
| `ls` | 列出目录内容 | `thunder ls` |
| `tree` | 树形展示目录结构 | `thunder tree src` |
| `cat` | 查看文件内容 | `thunder cat composer.json` |
| `mkdir` | 创建目录 | `thunder mkdir -p a/b/c` |
| `touch` | 创建空文件 / 更新修改时间 | `thunder touch index.php` |
| `write` | 写入内容到文件 | `thunder write hello.txt "Hello World"` |
| `cp` | 复制文件/目录 | `thunder cp src/ dist/ -f` |
| `mv` | 移动/重命名 | `thunder mv old.txt new.txt` |
| `rm` | 删除文件/目录 | `thunder rm -r build/ -f` |

## 详细用法

### ls — 列出目录内容

```bash
thunder ls [path] [-a] [-l]
```

| 选项 | 说明 |
|------|------|
| `path` | 目标目录（默认当前目录） |
| `-a, --all` | 显示隐藏文件（`.` 开头） |
| `-l, --long` | 显示详细信息（类型/大小/修改时间） |

### tree — 树形展示目录结构

```bash
thunder tree [path] [-d N] [-a]
```

| 选项 | 说明 |
|------|------|
| `path` | 目标目录（默认当前目录） |
| `-d, --depth` | 最大递归深度（默认 3） |
| `-a, --all` | 显示隐藏文件 |

### cat — 查看文件内容

```bash
thunder cat <file> [--lines N]
```

| 选项 | 说明 |
|------|------|
| `file` | 文件路径（必填） |
| `--lines` | 只显示前 N 行（默认 2000，防止刷屏） |

自动检测二进制文件并跳过输出。

### mkdir — 创建目录

```bash
thunder mkdir <dir>... [-p]
```

| 选项 | 说明 |
|------|------|
| `dirs` | 目录路径（支持多个） |
| `-p, --parents` | 递归创建父目录 |

### touch — 创建空文件 / 更新修改时间

```bash
thunder touch <file>...
```

| 选项 | 说明 |
|------|------|
| `files` | 文件路径（支持多个） |

文件已存在时仅更新修改时间。

### write — 写入文件内容

```bash
thunder write <file> [content] [-a]
```

| 选项 | 说明 |
|------|------|
| `file` | 目标文件路径（必填） |
| `content` | 要写入的内容（默认空串） |
| `-a, --append` | 追加模式，不覆盖原内容 |

内容含空格时请加引号：`thunder write readme.md "## Hello"`。

### cp — 复制文件/目录

```bash
thunder cp <src>... <dst> [-f]
```

| 选项 | 说明 |
|------|------|
| `sources` | 源路径（支持多个，最后一个为目标） |
| `-f, --force` | 目标已存在时覆盖 |

目录会递归复制。

### mv — 移动/重命名

```bash
thunder mv <src>... <dst> [-f]
```

| 选项 | 说明 |
|------|------|
| `sources` | 源路径（支持多个，最后一个为目标） |
| `-f, --force` | 目标已存在时覆盖 |

跨盘移动时自动回退为"复制 + 删除"。

### rm — 删除文件/目录

```bash
thunder rm <path>... [-r] [-f]
```

| 选项 | 说明 |
|------|------|
| `paths` | 要删除的路径（支持多个） |
| `-r, --recursive` | 递归删除目录及其内容 |
| `-f, --force` | 跳过确认直接删除 |

内置危险路径保护：拒绝删除根目录、盘符根、当前工作目录及其祖先。

## 路径支持

所有路径参数均支持：

- `~` 展开为用户主目录
- 相对路径（基于当前工作目录解析）
- `..` 归一化
- Windows 与 Unix 风格分隔符

## 许可证

MIT
