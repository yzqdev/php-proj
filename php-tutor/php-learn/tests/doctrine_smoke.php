<?php

declare(strict_types=1);

/**
 * Doctrine 集成冒烟测试（CLI 运行，不依赖 Web 服务器）
 *
 * 用法：php tests/doctrine_smoke.php
 *
 * 验证内容：
 *   1. EntityManager 组装成功（配置正确）
 *   2. 元数据解析成功（Entity 注解有效）
 *   3. DQL 查询成功（DB 连接 + 映射一致）
 *   4. 事务 + Unit of Work 插入/回滚成功
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Entities\User;
use App\Services\DoctrineServiceProvider;

$em = DoctrineServiceProvider::createEntityManager();

// 1) 元数据：User / RefreshToken 都能解析
$metaUser = $em->getClassMetadata(User::class);
echo "[1] metadata OK: users table = {$metaUser->getTableName()}\n";
$metaToken = $em->getClassMetadata(App\Entities\RefreshToken::class);
echo "[2] metadata OK: refresh_tokens table = {$metaToken->getTableName()}\n";

// 2) DQL 查询（验证映射与 DB 列名一致）
$repo = $em->getRepository(User::class);
$users = $repo->createQueryBuilder('u')
    ->setMaxResults(3)
    ->getQuery()
    ->getArrayResult();
echo '[3] DQL query OK: got ' . count($persistedUsers = $users) . " user(s)\n";

// 3) Unit of Work：事务里插入后回滚（不污染数据）
$em->beginTransaction();
try {
    $u = new App\Entities\User();
    $u->setUsername('doctrine_smoke_' . bin2hex(random_bytes(4)))
        ->setEmail('smoke_' . bin2hex(random_bytes(4)) . '@test.local')
        ->setPassword(password_hash('dummy', PASSWORD_BCRYPT));
    $em->persist($u);
    $em->flush();
    echo "[4] persist+flush OK: new id = {$u->getId()}\n";
} finally {
    $em->rollback();
    $em->clear();
    echo "[5] rollback OK (no data polluted)\n";
}

echo "\nAll smoke tests passed.\n";
