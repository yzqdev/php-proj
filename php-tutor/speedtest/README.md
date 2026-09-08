# 纯 PHP 网络测速工具

基于纯 PHP 的轻量级网络测速工具，使用 **Monolog** 日志库记录测速结果。

## 功能

- 📡 **网络延迟** — 多轮 Ping 测试，取平均和抖动值
- 📊 **延迟抖动** — 相邻延迟差值分析
- ↓ **下载速度** — 服务器生成随机数据，客户端测量传输速率
- ↑ **上传速度** — 客户端生成随机数据，服务器测量接收时间
- 🌍 **IP 信息** — 自动获取客户端 IP 和 ISP 信息
- 🌙 **深色/浅色主题** — 一键切换，自动记忆偏好
- 📋 **日志系统** — 基于 Monolog，JSON Lines 格式，按日分割
- 📱 **响应式布局** — 适配桌面端和移动端

## 文件结构

```
speedtest/
├── index.php           # 主界面（PHP 配置 + 内联 CSS/JS 测速逻辑）
├── lib/
│   └── Logger.php      # Monolog 日志封装（单例 + 静态接口）
├── test/
│   ├── ping.php       # 延迟测试端点
│   ├── info.php       # IP 信息端点
│   ├── download.php   # 下载测试数据生成
│   ├── upload.php     # 上传测试数据接收
│   ├── log.php        # 日志接收端点（POST）
│   └── logs.php       # 日志查看页面
├── logs/              # 日志文件目录（按日期分割）
├── vendor/            # Composer 依赖（Monolog）
├── favicon.ico
├── LICENSE
├── README.md
├── composer.json
├── composer.lock
└── .gitignore
```

## 环境要求

- **PHP 8.0+**（推荐 PHP 8.2+）
- **扩展**: `json`
- **Composer**
- 现代浏览器（Chrome, Firefox, Edge, Safari）

## 快速开始

```bash
# 安装依赖
composer install

# 启动开发服务器
php -S localhost:8080

# 或通过 Composer
composer serve
```

然后访问 http://localhost:8080

## 配置

编辑 `index.php` 顶部的 `$config` 数组：

```php
$config = [
    'download_size' => 20 * 1024 * 1024,  // 下载测试大小（默认 20MB）
    'upload_size'   => 10 * 1024 * 1024,  // 上传测试大小（默认 10MB）
    'ping_count'    => 10,                // Ping 请求次数
    'get_isp'       => false,             // 是否获取 ISP 信息
    'title'         => '测速网站',         // 页面标题
    'footer'        => '服务器信息',       // 页脚文字
];
```

上传大小会根据 `post_max_size` 自动调整（留 1MB 余量）。

## 日志系统

基于 **Monolog 2.x** 实现，日志存储在 `logs/` 目录：

```
logs/speedtest-2026-06-09.log   # 按日期分割
```

日志格式为 **JSON Lines**，每行一条记录：

```json
{"message":"测速结果","context":{"client_ip":"127.0.0.1","ping":10.5,"jitter":2.3,"download_speed":50.2,"upload_speed":20.1},"level_name":"INFO","channel":"speedtest","datetime":"2026-06-09T10:00:00+08:00"}
```

### 查看日志

访问 http://localhost:8080/test/logs.php

### 日志保留

默认保留 30 天，可在 `lib/Logger.php` 中修改 `RETENTION_DAYS` 常量。

### 使用方法

```php
require_once 'lib/Logger.php';

// 记录测速结果
Logger::test([
    'client_ip' => '127.0.0.1',
    'ping' => 10.5,
    'jitter' => 2.3,
    'download_speed' => 50.2,
    'upload_speed' => 20.1,
]);

// 记录信息日志
Logger::info('服务器启动', ['version' => '2.0']);

// 记录错误日志
Logger::error('数据库连接失败', ['error' => $e->getMessage()]);

// 获取最近的日志
$recent = Logger::getRecent(50);

// 获取统计信息
$stats = Logger::getStats();

// 清理过期日志
Logger::cleanup(30); // 保留 30 天
```

## ISP 信息

默认不获取 ISP 信息。如需启用：

1. 将 `index.php` 中 `'get_isp' => false` 改为 `true`
2. （可选）将 API Key 写入 `test/ipinfo_apikey.txt`

## 测速原理

```
客户端 ──→ test/download.php?size=20MB  ──→ 下载速度 = 字节数 / 耗时
客户端 ──→ test/upload.php (POST 10MB)  ──→ 上传速度 = 字节数 / 耗时
客户端 ──→ test/ping.php (×10)          ──→ 延迟 = 往返时间平均值
                                    抖动 = 相邻延迟差值平均
客户端 ──→ test/log.php (POST JSON)     ──→ 结果记录到日志文件
```

## 许可证

GPL-3.0-or-later
