<?php

declare(strict_types=1);

/**
 * 数据库迁移执行器
 *
 * 不引入 doctrine/migrations(会连带 symfony/console 等一整套依赖,与本项目的规模不匹配),
 * 这里用 DBAL 的 SchemaManager 实现最小可用的版本化迁移:
 *   - 迁移文件放 database/migrations/,命名 时间戳_描述.php,类内实现 up/down(AbstractSchemaManager)
 *   - 已应用的版本号记在 migrations 表,脚本可重复执行
 *   - DDL 按 auto-commit 执行:MySQL 的 DDL 会隐式提交事务,包进事务反而报错;
 *     迁移失败时不会写入版本记录,修正后重跑脚本即可重试
 *
 * 用法:
 *   php database/migrate.php             # 执行所有待应用迁移(库不存在时自动创建)
 *   php database/migrate.php --status    # 查看迁移状态
 *   php database/migrate.php --rollback  # 回滚最近一次已应用的迁移
 */

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Dotenv\Dotenv;
use Yzqde\Playground\Settings;

require dirname(__DIR__) . '/vendor/autoload.php';

const MIGRATIONS_TABLE = 'migrations';
const VERSION_PATTERN = '/^(\d{4}_\d{2}_\d{2}_\d{6})_(.+)$/';

$baseDir = dirname(__DIR__);
Dotenv::createUnsafeImmutable($baseDir)->safeLoad();
$settings = Settings::fromEnv($baseDir);
$action = $argv[1] ?? 'migrate';

/**
 * 库名不参与参数绑定(MySQL 语法限制),做白名单校验后再拼进 DDL
 */
function validateDbName(string $dbName): void
{
    if (!preg_match('/^[A-Za-z][A-Za-z0-9_]{0,63}$/', $dbName)) {
        throw new InvalidArgumentException('非法的数据库名:' . $dbName);
    }
}

// 先不带 dbname 连服务器,目标库不存在则先建;建库本身是幂等的
$serverParams = [
    'driver' => 'pdo_mysql',
    'host' => $settings->dbHost,
    'port' => $settings->dbPort,
    'user' => $settings->dbUser,
    'password' => $settings->dbPassword,
    'charset' => $settings->dbCharset,
];
validateDbName($settings->dbName);

$server = DriverManager::getConnection($serverParams);
$server->executeStatement(sprintf(
    'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
    $settings->dbName
));

/** @var Connection $conn */
$conn = DriverManager::getConnection($serverParams + ['dbname' => $settings->dbName]);
$schema = $conn->createSchemaManager();

// 版本表是迁移机制自身的基础设施,幂等创建
if (!$schema->tablesExist([MIGRATIONS_TABLE])) {
    $versionTable = new Table(MIGRATIONS_TABLE);
    $versionTable->addColumn('version', 'string', ['length' => 120])
        ->setNotnull(true)
        ->setComment('迁移文件名(不含扩展名)');
    $versionTable->addColumn('applied_at', 'datetime')
        ->setNotnull(true)
        ->setComment('应用时间');
    $versionTable->setPrimaryKey(['version']);
    $schema->createTable($versionTable);
}

/**
 * 扫描迁移文件,按文件名升序(即时间戳升序)返回 [版本号 => 迁移类全名]
 *
 * @return array<string, string>
 */
function loadMigrations(string $baseDir): array
{
    $files = glob($baseDir . '/database/migrations/*.php') ?: [];
    sort($files);

    $migrations = [];
    foreach ($files as $file) {
        $version = basename($file, '.php');
        // 2026_09_08_000001_create_persons_table => 描述段 create_persons_table => CreatePersonsTable
        if (!preg_match(VERSION_PATTERN, $version, $match)) {
            continue;
        }

        require_once $file;
        $suffix = $match[2];
        $class = 'Yzqde\\Playground\\Database\\Migrations\\'
            . str_replace(' ', '', ucwords(str_replace('_', ' ', $suffix)));

        if (!class_exists($class)) {
            throw new RuntimeException(sprintf('迁移文件 %s 中未找到类 %s', $file, $class));
        }

        $migrations[$version] = $class;
    }

    return $migrations;
}

$applied = $conn->fetchAllAssociative(
    'SELECT version, applied_at FROM ' . MIGRATIONS_TABLE . ' ORDER BY applied_at ASC'
);

if ($action === '--status') {
    $appliedVersions = array_column($applied, 'version');
    foreach (loadMigrations($baseDir) as $version => $class) {
        $state = in_array($version, $appliedVersions, true) ? '已应用' : '待应用';
        printf("  [%s] %s\n", $state, $version);
    }
    exit(0);
}

if ($action === '--rollback') {
    if ($applied === []) {
        echo "没有可回滚的迁移\n";
        exit(0);
    }

    $last = end($applied);
    $version = (string)$last['version'];
    $migrations = loadMigrations($baseDir);
    if (!isset($migrations[$version])) {
        throw new RuntimeException('待回滚的迁移文件已不存在:' . $version);
    }

    (new $migrations[$version]())->down($schema);
    $conn->delete(MIGRATIONS_TABLE, ['version' => $version]);
    echo '已回滚:' . $version . "\n";
    exit(0);
}

$migrations = loadMigrations($baseDir);
$appliedVersions = array_column($applied, 'version');
$ran = 0;

foreach ($migrations as $version => $class) {
    if (in_array($version, $appliedVersions, true)) {
        continue;
    }

    // MySQL 的 DDL 会隐式提交,包进事务反而在提交时报 NoActiveTransaction,
    // 因此按 auto-commit 执行:迁移失败时不会写入版本记录,修正后重跑即可重试
    (new $class())->up($schema);
    $conn->executeStatement(
        'INSERT INTO ' . MIGRATIONS_TABLE . ' (version, applied_at) VALUES (?, NOW())',
        [$version],
        [Types::STRING]
    );

    echo '已应用:' . $version . "\n";
    $ran++;
}

echo $ran > 0 ? sprintf("完成,共应用 %d 个迁移\n", $ran) : '所有迁移均已应用,无需变更' . "\n";
