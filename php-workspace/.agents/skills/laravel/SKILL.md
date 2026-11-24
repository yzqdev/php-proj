---
name: laravel
description: Laravel 项目结构、请求生命周期、服务容器、Eloquent、队列、认证与测试的开发指南
---

# Laravel 开发指南

## Overview

Laravel 是全栈 Web 框架，约定优于配置。理解请求生命周期与容器，比记住 Facade 列表更重要。

适用：业务后台、SaaS、CRUD 密集型系统。若只需要一个极简 API，先评估 Slim（见 `.agents/docs/framework-comparison.md`）。

## 项目结构与版本差异

Laravel 11 起骨架明显精简：

```text
app/
├── Http/Controllers/
├── Models/
├── Providers/
└── ...
bootstrap/app.php        # 中间件、异常、路由集中配置
routes/
├── web.php
├── api.php
└── console.php
config/                  # 按需发布
database/
├── migrations/
├── factories/
└── seeders/
public/index.php
tests/
```

- Laravel 10 及更早：`app/Http/Kernel.php`、`app/Exceptions/Handler.php`、`app/Console/Kernel.php` 存在。
- Laravel 11+：上述职责收敛到 `bootstrap/app.php`，通过 `->withMiddleware()`、`->withExceptions()` 配置。
- 查阅文档时确认版本段，不要混用两套结构。

## 请求生命周期

```text
public/index.php
  → bootstrap/app.php
  → HTTP Kernel
  → 全局中间件
  → 路由匹配
  → 路由中间件（认证、限流、绑定）
  → Controller
  → Response
  → 中间件回程
  → 发送响应
```

关键点：

- 服务容器在早期创建，贯穿请求。
- 路由模型绑定（Route Model Binding）在进入 Controller 前解析参数。
- 中间件是洋葱模型，后置逻辑在响应返回时执行。
- Octane 下应用常驻，注意静态状态与单例污染。

## 服务容器与服务提供者

```php
// app/Providers/AppServiceProvider.php
public function register(): void
{
    $this->app->bind(PaymentGateway::class, StripeGateway::class);
    $this->app->singleton(ReportBuilder::class);
}

public function boot(): void
{
    Model::preventLazyLoading(! app()->isProduction());
}
```

- `bind` 每次解析新实例，`singleton` 进程内共享。
- 接口绑定实现是解耦的主要手段。
- 服务提供者分 `register`（绑定）与 `boot`（启动逻辑）。
- 不要滥用 `app()` 全局解析，优先构造器注入。

## Facade

```php
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

Cache::put('key', $value, now()->addMinutes(10));
Log::channel('api')->info('request', ['path' => $request->path()]);
```

- Facade 是对容器服务的静态代理，便于写法简洁。
- 业务代码大量使用 Facade 会降低可测试性；核心逻辑优先注入 `LoggerInterface`、`CacheInterface` 等。
- 自定义 Facade 需要 `getFacadeAccessor()` 返回容器键。

## 路由与中间件

```php
// routes/api.php
Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::apiResource('users', UserController::class);
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);
});
```

- `api.php` 默认无 Session/CSRF，适合 Token 认证。
- 路由模型绑定：`{user}` 自动注入 `User`，不存在返回 404。
- 中间件顺序影响认证与限流结果；跨域、认证、限流要明确排序。
- 路由缓存：`php artisan route:cache`，闭包路由无法缓存。

中间件示例：

```php
final class EnsureApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->header('X-Api-Key') !== config('services.api.key')) {
            throw new AuthenticationException('Invalid API key');
        }
        return $next($request);
    }
}
```

## Controller 与校验

```php
final class UserController extends Controller
{
    public function __construct(private readonly UserService $users) {}

    public function store(StoreUserRequest $request): UserResource
    {
        $user = $this->users->create($request->validated());
        return new UserResource($user);
    }
}
```

- Controller 只做参数转换、调用 Service、返回 Resource。
- 校验用 FormRequest，把规则与授权集中在 `rules()` / `authorize()`。
- 不在 Controller 内写复杂查询与业务分支。

## Eloquent

```php
final class Order extends Model
{
    protected $fillable = ['user_id', 'status', 'total'];

    protected $casts = [
        'status' => OrderStatus::class,
        'total' => 'integer',
        'paid_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
```

常见问题：

- **N+1**：列表查询用 `with(['items'])` 预加载；开发环境开启 `preventLazyLoading()`。
- **Mass Assignment**：只允许 `$fillable` 中的字段，敏感字段用 `$guarded` 或显式赋值。
- **大结果集**：用 `chunkById()` / `cursor()`，不要一次 `get()` 全表。
- **事务**：`DB::transaction(fn () => ...)`，事务内不要做 HTTP 调用。
- **软删除**：`SoftDeletes` 后查询默认过滤，统计时注意 `withTrashed()`。

## 迁移、填充与工厂

```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('status', 32)->index();
    $table->unsignedBigInteger('total');
    $table->timestamps();
});
```

```powershell
php artisan make:migration create_orders_table
php artisan migrate
php artisan migrate:rollback --step=1
php artisan db:seed
```

- 迁移一旦上线不要修改，新增变更用新迁移。
- 生产环境禁止 `migrate:fresh`。
- 工厂与 Seeder 只用于开发/测试，不写入生产数据。

## 认证与授权

- **Sanctum**：SPA Cookie 认证与 API Token，轻量场景首选。
- **Passport**：完整 OAuth2，适合第三方授权。
- **Policy**：资源级授权，`$this->authorize('update', $order)`。
- **Gate**：闭包式权限判断，适合简单规则。

```php
final class OrderPolicy
{
    public function update(User $user, Order $order): bool
    {
        return $order->user_id === $user->id;
    }
}
```

## 缓存、队列、事件与调度

```php
Cache::remember("user:{$id}:profile", 600, fn () => $this->loadProfile($id));

ProcessOrder::dispatch($order)->onQueue('orders');

Event::dispatch(new OrderPaid($order));
```

- 缓存键带业务前缀与版本，便于失效。
- 队列任务实现 `ShouldQueue`，注意序列化与幂等。
- 事件监听器可同步或队列化（`ShouldQueue`）。
- 调度在 `routes/console.php`（11+）或 `app/Console/Kernel.php`（10-）定义。

```php
Schedule::command('orders:close-expired')->hourly()->withoutOverlapping();
```

- `withoutOverlapping()` 防止任务重叠。
- 队列 Worker 用 Supervisor 或 Windows 服务托管，`queue:work` 不要用 `queue:listen` 上生产。

## 存储、邮件与通知

- `Storage::disk('s3')` 统一文件抽象；本地默认 `storage/app`。
- 上传文件用 `$request->file('avatar')->store('avatars', 'public')`，并校验类型与大小。
- 邮件用 Mailable，通知用 Notification（邮件、短信、数据库多渠道）。
- 生产环境配置真实 SMTP，不用 `log` 驱动。

## Blade 与 API Resource

- Blade 用于服务端渲染；API 项目通常不需要。
- API 输出用 `JsonResource` / `ResourceCollection`，统一字段与嵌套。
- 不直接返回 Eloquent 模型，避免泄漏内部字段。

```php
final class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
```

## 异常处理与日志

- 业务异常继承 `RuntimeException`，在 `bootstrap/app.php` 或 `Handler` 中映射为 HTTP 状态码。
- `ValidationException` 自动转 422；认证失败 401；授权失败 403。
- 生产环境 `APP_DEBUG=false`，错误写日志，不返回堆栈。
- 日志渠道在 `config/logging.php` 配置；API 项目建议单独 `api` 渠道 + JSON 格式。

```php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (DomainException $e, Request $request) {
        return response()->json(['error' => $e->getMessage()], 400);
    });
})
```

## 配置与环境

- `.env` 不提交；提供 `.env.example`。
- `config()` 读取配置，`env()` 只在配置文件内使用。
- 配置缓存：`php artisan config:cache`，部署后执行并重启 FPM。
- 密钥用 `APP_KEY`、第三方 Token，不写入代码。

## 测试

```php
final class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_cancel_pending_order(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Pending]);

        $this->actingAs($order->user)
            ->postJson("/api/v1/orders/{$order->id}/cancel")
            ->assertOk();

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
    }
}
```

- Feature 测试覆盖路由、认证、校验、响应结构。
- Unit 测试覆盖领域逻辑。
- 用 `RefreshDatabase` 隔离数据库；SQLite 内存库可加速。
- 不 mock 自己写的业务类，优先 mock 外部服务。

## 扩展组件

| 组件 | 用途 |
|---|---|
| Laravel Octane | 常驻进程（Swoole/RoadRunner），提升吞吐 |
| Laravel Sanctum | SPA/Token 认证 |
| Laravel Horizon | Redis 队列监控 |
| Laravel Telescope | 本地调试与请求追踪 |
| Laravel Pennant | 特性开关 |

Octane 注意：应用常驻，单例与静态属性会跨请求保留，避免在容器中缓存请求相关数据。

## 常见坑

- 在 `config/` 外用 `env()`，配置缓存后返回 null。
- 列表接口未预加载关联，N+1 导致慢查询。
- 事务内调用外部 HTTP，长事务锁表。
- `migrate:fresh` 误用于生产。
- 队列任务未实现幂等，重试造成重复扣款。
- Octane 下静态缓存污染请求数据。

## References

- Laravel 文档：<https://laravel.com/docs>
- Eloquent：<https://laravel.com/docs/eloquent>
- 队列：<https://laravel.com/docs/queues>
- 测试：<https://laravel.com/docs/testing>
