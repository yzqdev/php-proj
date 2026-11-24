# 本机执行环境（AI Agent 环境参考）

快照日期：2026-10-25（经 environment-audit 扫描复核并更新 pwsh/Rust/pnpm 版本）；2026-11-07 实测更新 Node/pnpm/npm（用户升级 pnpm 12.18.0）；2026-11-24 实测更新 PHP 8.5.10 / Composer 2.11.2 及 PHP 扩展与缺失工具清单

本文档描述 AI Agent 执行任务时所处的本机环境。开始任务前必须先阅读本文档，并严格按照其中描述的环境执行；不得忽略环境约束，不得自行假设不存在的工具或配置。

## 生成/改写环境相关内容的使用规则

生成"安装/运行/构建/环境"相关内容时，直接引用本文档的真实版本与路径，不要虚构版本号或假设默认安装位置：

1. Java 项目文档中的 JAVA_HOME / JDK 路径必须指向 `~\.jdks\` 下的具体目录，不要写"系统默认 JDK"；需要正式发行版时选 `jdk-25` 或 `zulu-*`，不要用 JBR 编译服务端程序。
2. 本文档未覆盖的内容（数据库、IDE 插件、WSL、网络代理等）按项目实际情况探测后再写，不要凭空补充。
3. PowerShell 脚本一律面向 PowerShell 7（pwsh）编写与执行：文档/脚本中的执行入口写 `pwsh` 而不是 `powershell`，可放心使用 7.x 语法（如 `??`、管道链 `&&`）；不要生成仅兼容 Windows PowerShell 5.1 的写法。
4. Gradle 相关文档与命令一律通过项目 wrapper（`gradlew` / `gradlew.bat`）调用，缓存位于 `F:\deps\.gradle`（`GRADLE_USER_HOME`），不要假设 `~/.gradle`。
5. 本文档是快照，可能过期：若实际探测结果与本文档冲突，以探测结果为准，并顺手更新本文档；环境信息不足时明确说明，不要猜测。

## 环境速查表

| 类别 | 实际环境 |
|---|---|
| OS / 硬件 | Windows 10 专业版 22H2 (10.0.19045.6456) / i5-12600KF / 32 GB RAM |
| .NET | SDK 10.0.401、9.0.205、8.0.424（`C:\Program Files\dotnet\sdk`）；Visual Studio Professional 2026（18.10.1）；NuGet 包缓存重定向到 `F:\deps\Nuget\.nuget\packages` |
| Flutter / Dart | Flutter 3.47.4 stable，位于 `F:\flutter`；Dart 3.13.3、DevTools 2.60.0（随 Flutter） |
| Android | SDK 位于 `C:\Users\yanni\AppData\Local\Android\Sdk`（ANDROID_HOME）：platforms android-19 ～ android-36 + android-37.0，build-tools 28.0.3 ～ 36.0.0 + 36.1.0-rc1；生成 Android 相关内容前先实际运行 `flutter doctor` 确认 |
| Java | 多版本 JDK 集中在 `%USERPROFILE%\.jdks\`（即 `C:\Users\yanni\.jdks\`：jdk-11/17/21/25、zulu-17/21、graalvm-17、jbr-17/21/25 等）；`JAVA_HOME` 当前指向 `~\.jdks\jbr-25.0.4.1`（JetBrains Runtime，非正式 JDK 发行版）；Maven 3.9.16（scoop）、Kotlin 2.4.20 可用；Gradle 不在 PATH（项目走 wrapper），缓存目录由 `GRADLE_USER_HOME=F:\deps\.gradle` 指定 |
| Node 生态 | Node 26.13.0 / npm 11.20.0 / pnpm 12.18.0 / yarn 4.18.1；另有 Bun 1.4.2、Deno 2.8.3 |
| Python | 3.13.15；uv 0.12.13 |
| Rust | 1.99.1 stable（x86_64-pc-windows-msvc），cargo 1.99.1 |
| Go | 1.27.1 (windows/amd64) |
| PHP | 8.5.10 (NTS, Visual C++ 2022 x64，`F:\programs\php-8.5.10-nts-Win32-vs17-x64\php.exe`) + Composer 2.11.2（scoop shim）；含 Xdebug 3.5.3、OPcache；本机无 nginx / MySQL 客户端 / Redis / Docker |
| C/C++ | MinGW GCC/G++ 16.2.0（MinGW-Builds） |
| Shell / 版本控制 | PowerShell 7.6.7（pwsh）、Git 2.55.0.windows.2 |

## 分类明细

### 操作系统与硬件

* Windows 10 专业版 22H2，内核版本 10.0.19045.6456，x64。
* CPU i5-12600KF，内存 32 GB。
* 文件系统为 Windows NTFS，路径分隔符 `\`；Git Bash 环境下可用 `/`。

### 开发工具与 IDE

* Visual Studio Professional 2026（18.10.1）。
* PowerShell 7.6.7（pwsh）为默认脚本环境。
* Git 2.55.0.windows.2。
* Docker 未安装：不要生成需要实际执行 Docker 的命令；Docker 相关内容只能作为参考资料并明确标注本机不可执行。
* Nginx、MySQL 客户端（`mysql`）、Redis（`redis-server`）、`docker` 均不在 PATH；需要时先用项目自带或自行安装的方案，不要假设已存在。
* 本机可直接使用 PHP 内置服务器 `php -S`、Composer、Xdebug、PHPUnit 等 CLI 工具。

### 编程语言与 SDK

| 语言 | 版本 | 备注 |
|---|---|---|
| .NET SDK | 10.0.401 / 9.0.205 / 8.0.424 | 位于 `C:\Program Files\dotnet\sdk` |
| Java JDK | jdk-11/17/21/25、zulu-17/21、graalvm-17、jbr-17/21/25 | 集中在 `C:\Users\yanni\.jdks\`；`JAVA_HOME` 指向 `~\.jdks\jbr-25.0.4.1`（JBR，非正式发行版） |
| Kotlin | 2.4.20 | |
| Flutter / Dart | Flutter 3.47.4 stable（`F:\flutter`）/ Dart 3.13.3 | DevTools 2.60.0 |
| Android SDK | android-19 ～ 36 + 37.0；build-tools 28.0.3 ～ 36.0.0 + 36.1.0-rc1 | `ANDROID_HOME=C:\Users\yanni\AppData\Local\Android\Sdk` |
| Python | 3.13.15 | 配 uv 0.12.13 |
| Rust | 1.99.1 stable（x86_64-pc-windows-msvc） | cargo 1.99.1 |
| Go | 1.27.1 (windows/amd64) | |
| PHP | 8.5.10 (NTS, VC++ 2022 x64) | 配 Composer 2.11.2；php.ini 位于 `F:\programs\php-8.5.10-nts-Win32-vs17-x64\php.ini`；`memory_limit=128M`；`opcache.enable_cli=0`（CLI 未启用 OPcache）；已加载 Xdebug 3.5.3 |
| C/C++ | MinGW GCC/G++ 16.2.0 | MinGW-Builds |

### 包管理器与构建工具

| 工具 | 版本/位置 | 备注 |
|---|---|---|
| npm / pnpm / yarn | 11.20.0 / 12.18.0 / 4.18.1 | Node 26.13.0；Bun 1.4.2、Deno 2.8.3 亦可用 |
| NuGet | 包缓存重定向到 `F:\deps\Nuget\.nuget\packages` | 不要假设默认 `%USERPROFILE%\.nuget\packages` |
| Maven | 3.9.16（scoop 安装） | |
| Gradle | 不在 PATH | 项目走 wrapper（`gradlew` / `gradlew.bat`）；缓存 `GRADLE_USER_HOME=F:\deps\.gradle` |
| uv | 0.12.13 | Python 包管理 |
| Composer | 2.11.2 | PHP 包管理（`D:\scoop\shims\composer.ps1`） |

### 常用目录

| 目录 | 用途 |
|---|---|
| `C:\Users\yanni\.jdks\` | 全部 JDK |
| `F:\flutter` | Flutter SDK |
| `C:\Users\yanni\AppData\Local\Android\Sdk` | Android SDK（ANDROID_HOME） |
| `F:\deps\.gradle` | Gradle 缓存（GRADLE_USER_HOME） |
| `F:\deps\Nuget\.nuget\packages` | NuGet 全局包缓存 |

### PHP 运行环境（2026-11-24 实测）

* PHP CLI：`F:\programs\php-8.5.10-nts-Win32-vs17-x64\php.exe`（8.5.10，NTS，VC++ 2022 x64）。
* php.ini：`F:\programs\php-8.5.10-nts-Win32-vs17-x64\php.ini`；无额外 `.ini` 扫描目录。
* `memory_limit=128M`；CLI 下 `opcache.enable_cli=0`；已加载 `Zend OPcache` 与 `Xdebug 3.5.3`。
* 已加载扩展：bcmath、ctype、curl、dom、fileinfo、filter、iconv、intl、json、libxml、mbstring、mysqli、mysqlnd、openssl、pcre、PDO、pdo_mysql、pdo_pgsql、pdo_sqlite、pgsql、Phar、random、readline、Reflection、session、SimpleXML、sodium、SPL、tokenizer、uri、xml、xmlreader、xmlwriter、zip、zlib。
* 未加载的常见扩展：`gd`、`imagick`、`redis`、`memcached`、`pcntl`、`sockets`、`amqp`。
  * 因此 Intervention Image（需要 gd/imagick）在本机不可直接运行；`PhpRedis` 需要额外安装扩展，应优先使用纯 PHP 的 Predis。
  * Windows 上无 `pcntl`/`sockets`，`Symfony Console`、`ReactPHP`（stream_select 可用但部分功能受限）与队列 Worker 需要以兼容方式运行。
* 本机没有 Nginx、MySQL 服务端与客户端、Redis 服务端、Docker。涉及这些组件的示例必须标注为"参考/需外部环境"，不能作为本机可直接执行的命令。
* 本地快速验证 Web 应用可使用内置服务器：`php -S 127.0.0.1:8000 -t public`。

### 网络环境与代理

快照未记录网络与代理配置。涉及网络访问、镜像源、代理设置的内容，先实际探测（如 `git config --get http.proxy`、`npm config get proxy`、环境变量 `HTTP_PROXY`/`HTTPS_PROXY`），以探测结果为准，不要凭空写入。

## 环境硬性约束（执行任务时必须遵守）

* 优先使用本文档中已存在的软件和工具，不重复安装已存在的依赖。
* 不随意修改系统级环境（PATH、环境变量、全局配置）。
* 不改变用户指定的版本。
* 不使用本文档中不存在的命令；不确定某工具是否可用时先实际验证。
* 环境信息不足时明确说明，不要自行猜测。
