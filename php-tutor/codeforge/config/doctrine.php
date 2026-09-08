<?php

declare(strict_types=1);

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\ORMSetup;
use App\Entity\User;
use App\Entity\File;

// Doctrine 配置：SQLite + 属性注解
// 数据库文件路径：项目根目录/storage/database.sqlite

$databasePath = __DIR__ . '/../storage/database.sqlite';

// 确保 storage 目录存在
$storageDir = __DIR__ . '/../storage';
if (!is_dir($storageDir)) {
    mkdir($storageDir, 0755, true);
}

// 创建 Doctrine 配置（使用属性注解）
$config = ORMSetup::createAttributeMetadataConfiguration(
    paths: [__DIR__ . '/../src/Entity'],
    isDevMode: true,
);

// 配置 SQLite 连接
$connectionParams = [
    'driver' => 'pdo_sqlite',
    'path'   => $databasePath,
];

$connection = DriverManager::getConnection($connectionParams);

// 创建 Entity Manager
$entityManager = new EntityManager($connection, $config);

// 如果数据库文件不存在，自动创建表结构和初始数据
if (!file_exists($databasePath)) {
    $schemaTool = new SchemaTool($entityManager);
    $schemaTool->createSchema([
        $entityManager->getClassMetadata(User::class),
        $entityManager->getClassMetadata(File::class),
    ]);

    // 插入初始数据
    $user1 = new User();
    $user1->setName('Alice');
    $user1->setEmail('alice@example.com');
    $entityManager->persist($user1);

    $user2 = new User();
    $user2->setName('Bob');
    $user2->setEmail('bob@example.com');
    $entityManager->persist($user2);

    $user3 = new User();
    $user3->setName('Carol');
    $user3->setEmail('carol@example.com');
    $entityManager->persist($user3);

    $entityManager->flush();
}

return $entityManager;