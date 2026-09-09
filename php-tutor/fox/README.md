# Fox

基于 Slim 4 的 PHP REST API 框架学习项目，集成 Doctrine ORM、Redis 缓存和 Swagger 文档。

## 技术栈

| 组件 | 版本 | 用途 |
|------|------|------|
| PHP | ≥ 8.1 | 运行环境 |
| Slim | 4.x | HTTP 框架 |
| Doctrine ORM | 3.x | 数据库 ORM |
| Doctrine DBAL | 4.x | 数据库抽象层 |
| Doctrine Migrations | 3.x | 数据库迁移 |
| Redis (Predis) | 3.x | 缓存 / Session |
| Monolog | 3.x | 日志 |
| Swagger PHP | 6.x | API 文档生成 |

## 快速开始

### 环境要求

- PHP ≥ 8.1（启用 pdo_mysql、mbstring 扩展）
- MySQL 8.x
- Redis 6.x+

### 安装

```bash
git clone <repo-url>
cd fox
composer install
```

### 配置

数据库和 Redis 连接配置在 `public/index.php` 中：

```php
// MySQL
'host' => '127.0.0.1',
'port' => 3306,
'user' => 'root',
'password' => '123456',
'dbname' => 'fox',

// Redis
'host' => '127.0.0.1',
'port' => 6379,
'password' => '123456',
```

### 数据库初始化

```powershell
# 创建数据库
mysql -u root -p123456 -e "CREATE DATABASE fox DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 执行迁移
./runmig.ps1 migrate
```

### 启动

```powershell
# Windows PowerShell
./runserve.ps1

# 或手动启动
php -S localhost:2977 -t public
```

访问 http://localhost:2977

## 项目结构

```
fox/
├── public/                    # Web 入口
│   └── index.php              # 应用启动文件
├── src/
│   ├── Controllers/           # 控制器
│   │   ├── HomeController.php
│   │   ├── UserController.php
│   │   ├── LogController.php
│   │   ├── RainController.php
│   │   └── SwaggerController.php
│   ├── Entity/                # Doctrine 实体
│   │   └── User.php
│   ├── Repository/            # Doctrine 仓储
│   │   └── UserRepository.php
│   ├── Middleware/             # 中间件
│   │   └── CacheMiddleware.php
│   ├── Routes/                # 路由注册
│   │   └── Web.php
│   ├── Services/              # 业务服务
│   │   ├── Logger.php
│   │   ├── LogReader.php
│   │   └── Store.php
│   ├── Support/               # 响应封装
│   │   └── BaseResponse.php
│   ├── Swagger/               # API 文档
│   │   └── ApiInfo.php
│   ├── Util/                  # 工具类
│   │   ├── Cache.php          # #[Cache] 属性定义
│   │   ├── FileCache.php      # 文件缓存（轻量场景）
│   │   ├── RedisCache.php     # Redis 缓存（HTTP 响应）
│   │   ├── RedisCachePool.php # PSR-6 缓存（Doctrine 元数据）
│   │   ├── CacheItem.php      # PSR-6 CacheItem 实现
│   │   ├── RedisConfig.php    # Redis 连接工厂
│   │   ├── DoctrineConfig.php # Doctrine EntityManager 工厂
│   │   ├── FileUtil.php
│   │   └── FileConst.php
│   └── Vo/                    # 值对象
├── migrations/                # Doctrine 迁移文件
├── migrations.php             # 迁移配置
├── migrations-db.php          # 迁移数据库连接
├── runserve.ps1               # 启动脚本
├── runmig.ps1                 # 数据库迁移脚本
├── cache/                     # 运行时缓存
└── logs/                      # 日志目录
```

## 脚本

| 脚本 | 用途 |
|------|------|
| `./runserve.ps1` | 启动开发服务器 (localhost:2977) |
| `./runmig.ps1 migrate` | 执行所有待执行的迁移 |
| `./runmig.ps1 status` | 查看迁移状态 |
| `./runmig.ps1 diff` | 根据 Entity 变更生成新迁移文件 |
| `./runmig.ps1 rollback` | 回滚到上一个版本 |
| `./runmig.ps1 fresh` | 删除所有表并重新迁移（需二次确认） |

## API 路由

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | `/` | 首页 |
| GET | `/hello/{name}` | 问候 |
| GET | `/swagger` | Swagger UI |
| GET | `/swagger/json` | OpenAPI JSON |
| **用户** | | |
| GET | `/api/users` | 用户列表（带 Redis 缓存） |
| GET | `/api/users/{id}` | 用户详情 |
| POST | `/api/users` | 创建用户 |
| PUT | `/api/users/{id}` | 更新用户 |
| DELETE | `/api/users/{id}` | 删除用户 |
| **日志** | | |
| GET | `/api/logs` | 日志列表 |
| GET | `/api/logs/{date}` | 指定日期日志 |
| **文件操作（Rain）** | | |
| GET | `/api/rain/getIndex` | 获取目录索引 |
| GET | `/api/rain/fileExists` | 文件是否存在 |
| GET | `/api/rain/readFile` | 读取文件 |
| POST | `/api/rain/writeFile` | 写入文件 |
| POST | `/api/rain/appendFile` | 追加文件 |
| GET | `/api/rain/fileInfo` | 文件信息 |
| GET | `/api/rain/listDirectory` | 列出目录 |
| POST | `/api/rain/createDirectory` | 创建目录 |
| POST | `/api/rain/copyFile` | 复制文件 |
| DELETE | `/api/rain/deleteFile` | 删除文件 |

所有 `/api/*` 接口返回统一 JSON 信封：

```json
{ "code": 200, "message": "ok", "data": { ... } }
```

## 核心功能

### 响应缓存（#[Cache] 属性）

在控制器方法上加 `#[Cache]` 注解即可启用 Redis 响应缓存：

```php
use Yzqde\Fox\Util\Cache;

// 默认 60 秒过期
#[Cache]
public function index(Request $request, Response $response): Response { ... }

// 自定义过期时间（秒）
#[Cache(ttl: 300)]
public function show(Request $request, Response $response, array $args): Response { ... }
```

缓存命中时响应头会带 `X-Cache: HIT`，未命中带 `X-Cache: MISS`。

### 缓存分层

项目使用两套缓存，各司其职：

| 缓存 | 存储 | 用途 | 使用方 |
|------|------|------|--------|
| `RedisCache` | Redis | HTTP 响应缓存 | `CacheMiddleware`（`#[Cache]` 属性） |
| `RedisCachePool` | Redis | Doctrine 元数据/查询缓存 | Doctrine ORM 内部 |
| `FileCache` | 文件系统 | 轻量数据缓存 | `LogReader` 等业务服务 |

**FileCache 用法示例**（LogReader 日志统计缓存 5 分钟）：

```php
use Yzqde\Fox\Util\FileCache;

$cache = new FileCache();

// 读缓存
$cached = $cache->get('log:days:2026-09-09-18');
if ($cached !== null) {
    return $cached; // 直接返回，不读磁盘
}

// 不存在则计算后写入缓存
$data = $this->computeExpensiveData();
$cache->set('log:days:2026-09-09-18', $data, 300); // 300 秒过期
```

### Doctrine ORM

Entity 使用 PHP 8 Attribute 映射：

```php
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 100)]
    private string $name;
}
```

在控制器中使用 EntityManager：

```php
$em = $container->get(\Doctrine\ORM\EntityManager::class);
$userRepo = $em->getRepository(User::class);
$users = $userRepo->findAll();
```

### 数据库迁移

```powershell
# 查看当前状态
./runmig.ps1 status

# 生成迁移（基于 Entity 变更）
./runmig.ps1 diff

# 执行迁移
./runmig.ps1 migrate

# 回滚到上一个版本
./runmig.ps1 rollback
```

### Swagger 文档

访问 `/swagger` 查看交互式 API 文档，`/swagger/json` 获取 OpenAPI 规范文件。

## License

MIT
