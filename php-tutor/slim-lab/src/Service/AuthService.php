<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Exception\ApiException;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;

/**
 * 统一认证服务：JSON 注册用户（storage/users.json）与 ORM 演示用户（MySQL users 表）
 * 两条用户源都可以登录。subject 前缀（json:/orm:）隔离两套 ID 空间，避免 token 归属错乱。
 */
final class AuthService
{
    public function __construct(
        private readonly EntityManager $em,
        private readonly UserStore $jsonUsers,
        private LoggerInterface $logger
    ) {
    }

    /**
     * 校验账号密码（identifier 可为用户名或邮箱），成功返回 subject 与公开用户信息。
     *
     * @return array{subject: string, user: array<string, mixed>}
     */
    public function authenticate(string $identifier, string $password): array
    {
        // 1) ORM 演示用户：登录名(username)或 email 命中且已设密码（未设密码的演示数据不可登录）
        $orm = $this->em->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('LOWER(u.username) = :ident1 OR LOWER(u.email) = :ident2')
            ->setParameter('ident1', mb_strtolower($identifier))
            ->setParameter('ident2', mb_strtolower($identifier))
            ->getQuery()
            ->getOneOrNullResult();

        if ($orm instanceof User
            && $orm->getPassword() !== null
            && password_verify($password, $orm->getPassword())) {
            return ['subject' => 'orm:' . $orm->getId(), 'user' => $this->ormPublic($orm)];
        }

        // 2) JSON 注册用户
        $json = $this->jsonUsers->findByIdentifier($identifier);
        if ($json !== null && $this->jsonUsers->verifyPassword($json, $password)) {
            return ['subject' => 'json:' . $json['id'], 'user' => $this->jsonPublic($json)];
        }

        // 统一提示，不区分"用户不存在/密码错误"，避免账号枚举
        throw new ApiException('账号或密码错误', 400);
    }

    /**
     * 按 subject 取当前用户信息；用户已被删除或 subject 非法均视为登录态失效。
     *
     * @return array<string, mixed>
     */
    public function userBySubject(string $subject): array
    {
        if (str_starts_with($subject, 'orm:')) {
            $user = $this->em->find(User::class, (int) substr($subject, 4));
            if ($user === null) {
                throw new ApiException('登录状态已失效，请重新登录', 401);
            }

            return $this->ormPublic($user);
        }

        if (str_starts_with($subject, 'json:')) {
            $user = $this->jsonUsers->findById((int) substr($subject, 5));
            if ($user === null) {
                throw new ApiException('登录状态已失效，请重新登录', 401);
            }

            return $this->jsonPublic($user);
        }

        throw new ApiException('登录状态已失效，请重新登录', 401);
    }

    /**
     * @return array<string, mixed>
     */
    private function ormPublic(User $user): array
    {
        return [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'created_at' => $user->getCreatedAt()->getTimestamp(),
        ];
    }

    /**
     * @param array<string, mixed> $user
     * @return array<string, mixed>
     */
    private function jsonPublic(array $user): array
    {
        return [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => (string) ($user['email'] ?? ''),
            'created_at' => (int) $user['created_at'],
        ];
    }
}
