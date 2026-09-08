<?php

declare(strict_types=1);

/**
 * ============================================================
 * Doctrine CLI 入口（schema + migrations 全家桶）
 * ============================================================
 *
 * 用法：
 *   php app/Console/DoctrineConsole.php list                # 列出所有命令
 *   php app/Console/DoctrineConsole.php schema:create       # 按 Entity 注解直接建表（学习/原型用）
 *   php app/Console/DoctrineConsole.php migrations:diff     # 对比 Entity 与库差异，生成迁移类
 *   php app/Console/DoctrineConsole.php migrations:migrate  # 执行迁移
 *   php app/Console/DoctrineConsole.php migrations:status   # 查看迁移状态
 *
 * 对比 Java / Spring Boot：
 *   - Flyway / Liquibase 的 migrate 命令 → migrations:migrate
 *   - hibernate ddl-auto=update          → schema:create（但生产要用迁移）
 *
 * 关键点：
 *   - 官方推荐用 ConsoleRunner + DependencyFactory 组装，不要手写迁移执行逻辑；
 *   - DependencyFactory::fromEntityManager() 把项目已有的 EntityManager 交给迁移系统，
 *     保证 CLI 与 Web 用同一套连接配置；
 *   - 迁移类目录：app/Migrations（命名空间 App\Migrations）。
 */

namespace App\Console;
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use App\Services\DoctrineServiceProvider;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use Doctrine\Migrations\Tools\Console\Command\CurrentCommand;
use Doctrine\Migrations\Tools\Console\Command\DiffCommand;
use Doctrine\Migrations\Tools\Console\Command\DumpSchemaCommand;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\Migrations\Tools\Console\Command\LatestCommand;
use Doctrine\Migrations\Tools\Console\Command\ListCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\RollupCommand;
use Doctrine\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\Migrations\Tools\Console\Command\SyncMetadataCommand;
use Doctrine\Migrations\Tools\Console\Command\UpToDateCommand;
use Doctrine\Migrations\Tools\Console\Command\VersionCommand;
use Doctrine\Migrations\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

// ============================================================
// 1) 组装 EntityManager（与 Web 端共用 DoctrineServiceProvider）
// ============================================================
$em = DoctrineServiceProvider::createEntityManager();

// ============================================================
// 2) 迁移配置：迁移类目录 + 元数据存储表
// ============================================================
$migrationsConfig = new Configuration();
$migrationsConfig->addMigrationsDirectory('App\Migrations', dirname(__DIR__, 2) . '/app/Migrations');

// 元数据表（记录已执行的迁移版本，默认表名 doctrine_migration_versions）
$storageConfig = new TableMetadataStorageConfiguration();
$migrationsConfig->setMetadataStorageConfiguration($storageConfig);

// ============================================================
// 3) DependencyFactory：把 EM + 迁移配置交给迁移系统
// ============================================================
$dependencyFactory = DependencyFactory::fromEntityManager(
    new ExistingConfiguration($migrationsConfig),
    new ExistingEntityManager($em),
);

// ============================================================
// 4) 自定义命令：schema:create（按注解建表，学习期最直观）
// ============================================================
#[AsCommand('schema:create', '按 Entity 注解直接创建数据表（不走迁移，学习/原型用）')]
final class SchemaCreateCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = DoctrineServiceProvider::createEntityManager();
        (new \Doctrine\ORM\Tools\SchemaTool($em))->createSchema($em->getMetadataFactory()->getAllMetadata());
        $output->writeln('<info>[OK] 数据表已按 Entity 注解创建</info>');
        return Command::SUCCESS;
    }
}

#[AsCommand('db:drop', '删除当前配置的数据库（危险操作）')]
final class DbDropCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = DoctrineServiceProvider::createEntityManager();
        $conn = $em->getConnection();
        $dbname = $conn->getParams()['dbname'] ?? null;
        if ($dbname === null) {
            $output->writeln('<error>连接配置里没有 dbname</error>');
            return Command::FAILURE;
        }
        // 先切到系统库再删（不能删除当前连接正在使用的库）
        $sm = $conn->createSchemaManager();
        if (in_array($dbname, $sm->listDatabases(), true)) {
            $conn->executeStatement("DROP DATABASE `{$dbname}`");
            $output->writeln("<info>[OK] 数据库 {$dbname} 已删除</info>");
        } else {
            $output->writeln("<comment>数据库 {$dbname} 不存在，跳过</comment>");
        }
        return Command::SUCCESS;
    }
}

// ============================================================
// 5) 组装 Console Application
// ============================================================
$commands = [
    // 自定义命令
    new SchemaCreateCommand(),
    new DbDropCommand(),

    // doctrine/migrations 官方命令（自动带上 DependencyFactory）
    new CurrentCommand($dependencyFactory),
    new DiffCommand($dependencyFactory),
    new DumpSchemaCommand($dependencyFactory),
    new ExecuteCommand($dependencyFactory),
    new GenerateCommand($dependencyFactory),
    new LatestCommand($dependencyFactory),
    new ListCommand($dependencyFactory),
    new MigrateCommand($dependencyFactory),
    new RollupCommand($dependencyFactory),
    new StatusCommand($dependencyFactory),
    new SyncMetadataCommand($dependencyFactory),
    new UpToDateCommand($dependencyFactory),
    new VersionCommand($dependencyFactory),
];

// 补充 doctrine/orm 自带的 Entity 校验命令
$application = ConsoleRunner::createApplication($commands);
$application->addCommands([
    new ValidateSchemaCommand(new SingleManagerProvider($em)),
]);

$application->run();
