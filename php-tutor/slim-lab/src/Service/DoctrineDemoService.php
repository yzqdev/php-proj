<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ApiException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Entity\Product;
use Entity\User;
use Faker\Factory as FakerFactory;
use Predis\ClientInterface;
use Predis\PredisException;
use Throwable;

/**
 * 原 api/doctrine.php 的全部 action 业务逻辑，原样搬迁（ORM 调用不改写）。
 * 方法名 = 旧接口 ?action= 参数值；每个请求创建一次 EntityManager，与旧代码一致。
 */
final class DoctrineDemoService
{
    /** stats 查询结果的 Redis 缓存键（写操作后失效，TTL 300s 兜底） */
    private const STATS_CACHE_KEY = 'doctrine:stats';

    private const STATS_CACHE_TTL = 300;

    public function __construct(
        private readonly EntityManager $em,
        private readonly ClientInterface $redis,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function init(): array
    {
        $em = $this->em;
        $schemaTool = new SchemaTool($em);
        $metadata = $em->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
        // 表已重建，stats 缓存必然失真，立即失效
        $this->redis->del(self::STATS_CACHE_KEY);

        return ['message' => '数据库表已初始化（重建完成）'];
    }

    /**
     * @return array<string, mixed>
     */
    public function createUsers(): array
    {
        $em = $this->em;

        // Faker 随机生成中文姓名 + 唯一登录名(username) + 唯一邮箱：演示用户可直接登录。
        // unique() 保证单次请求内互不相同；同时设置统一演示密码 demo123。
        $faker = FakerFactory::create('zh_CN');
        $user1 = new User($faker->name(), $faker->unique()->safeEmail(), $faker->unique()->userName());
        $user2 = new User($faker->name(), $faker->unique()->safeEmail(), $faker->unique()->userName());
        $user1->setPassword(password_hash('demo123', PASSWORD_BCRYPT));
        $user2->setPassword(password_hash('demo123', PASSWORD_BCRYPT));

        $em->persist($user1);
        $em->persist($user2);

        // 兜底：极小概率跨请求撞邮箱，仍转业务异常（400 友好提示）
        try {
            $em->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw new ApiException('用户邮箱随机冲突，请重试一次或先执行 init 重置数据', 400);
        }
        $this->redis->del(self::STATS_CACHE_KEY);

        return ['message' => "创建成功\n用户1: {$user1->getName()} <{$user1->getEmail()}> ID: {$user1->getId()}  登录名: {$user1->getUsername()}\n用户2: {$user2->getName()} <{$user2->getEmail()}> ID: {$user2->getId()}  登录名: {$user2->getUsername()}\n（演示账号均可用 登录名/邮箱 + 密码 demo123 登录）"];
    }

    /**
     * @return array<string, mixed>
     */
    public function createProducts(): array
    {
        $em = $this->em;
        $users = $em->getRepository(User::class)->findAll();
        if (count($users) < 2) {
            throw new ApiException('请先创建用户');
        }
        $prod1 = new Product('iPhone 16', '7999.00', $users[0]);
        $prod2 = new Product('MacBook Pro', '19999.00', $users[0]);
        $prod3 = new Product('iPad Air', '4799.00', $users[1]);
        $em->persist($prod1);
        $em->persist($prod2);
        $em->persist($prod3);
        $em->flush();
        $this->redis->del(self::STATS_CACHE_KEY);

        return ['message' => "创建商品完成\niPhone 16 → {$users[0]->getName()}\nMacBook Pro → {$users[0]->getName()}\niPad Air → {$users[1]->getName()}"];
    }

    /**
     * @return array<string, mixed>
     */
    public function findUser(): array
    {
        $em = $this->em;
        $users = $em->getRepository(User::class)->findAll();
        if (empty($users)) {
            throw new ApiException('没有用户数据');
        }
        $found = $em->find(User::class, $users[0]->getId());

        return ['message' => "查询结果: {$found}"];
    }

    /**
     * @return array<string, mixed>
     */
    public function dqlQuery(): array
    {
        $em = $this->em;
        $users = $em->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.name LIKE :name')
            ->setParameter('name', '%张%')
            ->getQuery()
            ->getResult();
        $names = implode(', ', array_map(fn(User $u) => $u->getName(), $users));

        return ['message' => "DQL 查询 'LIKE %张%'\n匹配结果: {$names}"];
    }

    /**
     * @return array<string, mixed>
     */
    public function joinQuery(): array
    {
        $em = $this->em;
        $usersWithProducts = $em->createQueryBuilder()
            ->select('u', 'p')
            ->from(User::class, 'u')
            ->leftJoin('u.products', 'p')
            ->getQuery()
            ->getResult();
        $output = [];
        foreach ($usersWithProducts as $u) {
            $productList = [];
            foreach ($u->getProducts() as $p) {
                $productList[] = "{$p->getName()} ¥{$p->getPrice()}";
            }
            $output[] = "{$u->getName()}: " . implode(' | ', $productList);
        }

        return ['message' => "LEFT JOIN 关联查询\n" . implode("\n", $output)];
    }

    /**
     * @return array<string, mixed>
     */
    public function updateUser(): array
    {
        $em = $this->em;
        $users = $em->getRepository(User::class)->findAll();
        if (empty($users)) {
            throw new ApiException('没有用户数据');
        }
        $oldName = $users[0]->getName();
        $users[0]->setName('张三丰');
        $em->flush();
        $updated = $em->find(User::class, $users[0]->getId());
        $this->redis->del(self::STATS_CACHE_KEY);

        return ['message' => "更新操作\n{$oldName} → {$updated->getName()}"];
    }

    /**
     * @return array<string, mixed>
     */
    public function deleteProduct(): array
    {
        $em = $this->em;
        $products = $em->getRepository(Product::class)->findAll();
        if (empty($products)) {
            throw new ApiException('没有商品数据');
        }
        $deletedName = $products[0]->getName();
        $em->remove($products[0]);
        $em->flush();
        $this->redis->del(self::STATS_CACHE_KEY);
        $count = $em->createQueryBuilder()
            ->select('COUNT(p.id)')
            ->from(Product::class, 'p')
            ->getQuery()
            ->getSingleScalarResult();

        return ['message' => "删除商品: {$deletedName}\n剩余商品数: {$count}"];
    }

    /**
     * @return array<string, mixed>
     */
    public function repository(): array
    {
        $em = $this->em;
        $allUsers = $em->getRepository(User::class)->findAll();
        $userList = implode(', ', array_map(fn(User $u) => $u->getName(), $allUsers));

        return ['message' => "Repository::findAll()\n所有用户: {$userList}"];
    }

    /**
     * @return array<string, mixed>
     */
    public function count(): array
    {
        $em = $this->em;
        $count = $em->createQueryBuilder()
            ->select('COUNT(p.id)')
            ->from(Product::class, 'p')
            ->getQuery()
            ->getSingleScalarResult();

        return ['message' => "COUNT 查询\n商品总数: {$count}"];
    }

    /**
     * @return array<string, mixed>
     */
    public function transaction(): array
    {
        $em = $this->em;
        $em->beginTransaction();
        try {
            $tempUser = new User('临时用户', 'temp@example.com');
            $em->persist($tempUser);
            $em->flush();
            $rollbackId = $tempUser->getId();
            $em->rollback();
            $stillExists = $em->find(User::class, $rollbackId);
            $msg = $stillExists === null
                ? "创建 ID: {$rollbackId}\n回滚成功，用户不存在"
                : '异常：用户仍然存在';

            return ['message' => "事务回滚演示\n{$msg}"];
        } catch (Throwable $e) {
            $em->rollback();
            throw new ApiException("异常: {$e->getMessage()}");
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function stats(): array
    {
        // 读缓存（cache-aside）：写操作均已失效该键，TTL 300s 仅作兜底
        try {
            $cached = $this->redis->get(self::STATS_CACHE_KEY);
            if ($cached !== null) {
                $data = json_decode($cached, true);
                if (is_array($data)) {
                    return $data;
                }
            }
        } catch (PredisException) {
            // Redis 不可用时降级为直查数据库（stats 是只读接口，不能因缓存故障而不可用）
        }

        $em = $this->em;
        $userCount = $em->createQueryBuilder()->select('COUNT(u.id)')->from(User::class, 'u')->getQuery()->getSingleScalarResult();
        $productCount = $em->createQueryBuilder()->select('COUNT(p.id)')->from(Product::class, 'p')->getQuery()->getSingleScalarResult();

        try {
            $this->redis->setex(
                self::STATS_CACHE_KEY,
                self::STATS_CACHE_TTL,
                (string) json_encode(['userCount' => $userCount, 'productCount' => $productCount]),
            );
        } catch (PredisException) {
            // 写缓存失败不影响本次响应
        }

        return ['userCount' => $userCount, 'productCount' => $productCount];
    }
}
