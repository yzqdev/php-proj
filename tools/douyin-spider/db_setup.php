<?php

declare(strict_types=1);

// 临时脚本：创建数据库并执行迁移文件中的建表 DDL（用完即删）

require __DIR__ . '/vendor/autoload.php';

if (file_exists(__DIR__ . '/.env')) {
    \Dotenv\Dotenv::createImmutable(__DIR__)->load();
}

$e = static fn (string $key, mixed $default = null): mixed => $_ENV[$key] ?? $_SERVER[$key] ?? $default;

$host = (string) $e('DB_HOST', '127.0.0.1');
$port = (int) $e('DB_PORT', 3306);
$dbname = (string) $e('DB_DATABASE', 'php_douyin_spider');
$user = (string) $e('DB_USERNAME', 'root');
$password = (string) $e('DB_PASSWORD', '');
$charset = (string) $e('DB_CHARSET', 'utf8mb4');

// 1. 不带 dbname 连接，创建数据库
$pdo = new \PDO(
    "mysql:host={$host};port={$port};charset={$charset}",
    $user,
    $password,
    [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION],
);
$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo '[OK] database ready: ' . $dbname . PHP_EOL;

// 2. 加载 ORM 配置（与 config/dependencies.php 保持一致的代理设置）
$config = \Doctrine\ORM\ORMSetup::createAttributeMetadataConfiguration(
    paths: [__DIR__ . '/src/Model'],
    isDevMode: true,
);
$config->enableNativeLazyObjects(true);
$config->setProxyDir('');
$config->setProxyNamespace('');

$conn = \Doctrine\DBAL\DriverManager::getConnection(
    [
        'driver' => 'pdo_mysql',
        'host' => $host,
        'port' => $port,
        'dbname' => $dbname,
        'user' => $user,
        'password' => $password,
        'charset' => $charset,
    ],
    $config,
);

// 3. 若表已存在则跳过；否则执行迁移文件中的 DDL
$tables = $conn->executeQuery('SHOW TABLES')->fetchFirstColumn();
if (in_array('users', $tables, true) && in_array('articles', $tables, true)) {
    echo '[SKIP] tables already exist: ' . json_encode($tables) . PHP_EOL;
} else {
    require __DIR__ . '/database/migrations/Version20260908000001.php';

    $migration = new \DoctrineMigrations\Version20260908000001($conn, new \Psr\Log\NullLogger());
    $migration->up(new \Doctrine\DBAL\Schema\Schema());

    foreach ($migration->getSql() as $query) {
        $conn->executeStatement($query->getQuery(), $query->getParams(), $query->getTypes());
        echo '[OK] ' . trim((string) preg_replace('/\s+/', ' ', $query->getQuery())) . PHP_EOL;
    }

    $tables = $conn->executeQuery('SHOW TABLES')->fetchFirstColumn();
    echo '[OK] tables: ' . json_encode($tables) . PHP_EOL;
}
