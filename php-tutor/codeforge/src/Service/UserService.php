<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;

/**
 * 用户业务逻辑层
 *
 * 协调 UserRepository 完成用户增删改查，
 * 不直接操作数据库，仅做参数传递和领域对象构造。
 *
 * 依赖注入：UserRepository 由 PHP-DI autowiring 自动解析。
 */
final class UserService
{
    public function __construct(
        private UserRepository $repository,
    ) {
    }

    /**
     * 获取全部用户
     *
     * @return User[] 用户对象数组
     */
    public function getAllUsers(): array
    {
        return $this->repository->findAllUsers();
    }

    /**
     * 按 ID 获取单个用户
     *
     * @param int $id 用户 ID
     *
     * @return User|null 找到返回 User 实例，不存在返回 null
     */
    public function getUser(int $id): ?User
    {
        return $this->repository->findById($id);
    }

    /**
     * 创建新用户
     *
     * @param string $name  用户姓名
     * @param string $email 用户邮箱
     *
     * @return User 创建成功后的用户实体（含分配的 ID）
     */
    public function createUser(string $name, string $email): User
    {
        $user = new User();
        $user->setName($name);
        $user->setEmail($email);

        return $this->repository->create($user);
    }

    /**
     * 更新已有用户
     *
     * @param int    $id    用户 ID
     * @param string $name  新姓名
     * @param string $email 新邮箱
     *
     * @return User|null 更新后的用户实体，ID 不存在时返回 null
     */
    public function updateUser(int $id, string $name, string $email): ?User
    {
        $user = $this->repository->findById($id);
        if ($user === null) {
            return null;
        }

        if ($name !== '') {
            $user->setName($name);
        }
        if ($email !== '') {
            $user->setEmail($email);
        }

        return $this->repository->update($user);
    }

    /**
     * 删除用户
     *
     * @param int $id 用户 ID
     *
     * @return bool 是否成功删除
     */
    public function deleteUser(int $id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * 分页查询用户
     *
     * @param int $page     页码（从 1 开始）
     * @param int $pageSize 每页条数
     *
     * @return array{ data: User[], total: int, page: int, pageSize: int, lastPage: int }
     */
    public function paginateUsers(int $page = 1, int $pageSize = 20): array
    {
        $result = $this->repository->paginate($page, $pageSize);
        $result['data'] = array_map(fn(User $u): array => $u->toArray(), $result['data']);
        return $result;
    }
}