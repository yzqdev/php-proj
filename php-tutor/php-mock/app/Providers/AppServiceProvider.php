<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\DataGeneratorService;
use App\Services\FieldResolver;
use Faker\Factory;
use Faker\Generator;

use Illuminate\Foundation\Console\DevCommand;
use Illuminate\Foundation\DevCommands;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Faker 以 zh_CN 本地化单例绑定（生成中文姓名/地址等），
        // 类比 Spring 中 @Bean 单例注册：容器内共享同一实例
        $this->app->singleton(Generator::class, fn() => Factory::create('zh_CN'));

        // FieldResolver / DataGeneratorService 以单例注册，容器自动解析构造器依赖（类似 @Autowired 构造注入）
        $this->app->singleton(FieldResolver::class);
        $this->app->singleton(DataGeneratorService::class);
    }

    public function boot(): void
    {   DevCommands::artisan('serve --host=127.0.0.1 --port=8562', 'server');
        DevCommands::except("vite");
    }
}
