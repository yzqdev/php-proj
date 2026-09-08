<?php

declare(strict_types=1);

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Yzqde\DouyinSpider\Model\Article;
use Yzqde\DouyinSpider\Model\User;

require __DIR__ . '/../vendor/autoload.php';

// 加载环境变量
if (file_exists(__DIR__ . '/../.env')) {
    \Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->load();
}

$dbParams = [
    'driver' => getenv('DB_DRIVER') ?: 'pdo_mysql',
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => (int) (getenv('DB_PORT') ?: 3306),
    'dbname' => getenv('DB_DATABASE') ?: 'douyin_spider',
    'user' => getenv('DB_USERNAME') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: '',
    'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
];

$config = \Doctrine\ORM\ORMSetup::createAttributeMetadataConfiguration(
    paths: [__DIR__ . '/../src/Model'],
    isDevMode: filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN),
);

$entityManager = new \Doctrine\ORM\EntityManager(
    DriverManager::getConnection($dbParams, $config),
    $config,
    DriverManager::getConnection($dbParams, $config)->getEventManager()
);

$input = new ArgvInput();
$output = new ConsoleOutput();

$helpers = [
    'em' => $entityManager,
    'db' => $entityManager->getConnection(),
];

ConsoleRunner::run($output, $helpers, $input);
