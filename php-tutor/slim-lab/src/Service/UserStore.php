<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ApiException;

/**
 * 用户存储：JSON 文件存储（storage/users.json），与网盘模块同级的文件型存储风格�?
 * 密码只用 password_hash / password_verify（bcrypt），token 使用 random_bytes(32)�?
 * 文件不存在时自动初始化；读写�?LOCK_EX 防并发覆盖�?
 */
final class UserStore
{
    private const ALGO = PASSWORD_BCRYPT;

    public function __construct(
        private readonly string $file,
    ) {
    }

    /**
     * 从项目根推导默认存储路径（storage/users.json）�?
     * __DIR__ = src/Service/Auth，向�?3 级到项目根�?
     */
    public static function defaultPath(): string
    {
        return dirname(__DIR__, 3) . '/storage/users.json';
    }

    /**
     * 注册新用户；用户名重复即�?400（邮箱可选，填写时唯一性校验）�?
     *
     * @return array{id: int, username: string, email: string, created_at: int}
     */
    public function create(string $username, string $password, string $email): array
    {
        $users = $this->all();
        foreach ($users as $u) {
            if (strcasecmp($u['username'], $username) === 0) {
                throw new ApiException('用户名已存在', 400);
            }
            if ($email !== '' && strcasecmp((string) ($u['email'] ?? ''), $email) === 0) {
                throw new ApiException('邮箱已被注册', 400);
            }
        }

        $maxId = 0;
        foreach ($users as $u) {
            $maxId = max($maxId, $u['id']);
        }

        $user = [
            'id' => $maxId + 1,
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash($password, self::ALGO),
            'created_at' => time(),
        ];
        $users[] = $user;
        $this->save($users);

        return [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'created_at' => $user['created_at'],
        ];
    }

    /**
     * 按用户名或邮箱查找（登录入口允许两者任一）�?
     *
     * @return array<string, mixed>|null
     */
    public function findByIdentifier(string $identifier): ?array
    {
        foreach ($this->all() as $u) {
            if (strcasecmp($u['username'], $identifier) === 0
                || strcasecmp($u['email'], $identifier) === 0) {
                return $u;
            }
        }

        return null;
    }

    public function verifyPassword(array $user, string $password): bool
    {
        return password_verify($password, (string) $user['password_hash']);
    }

    public function findById(int $id): ?array
    {
        foreach ($this->all() as $u) {
            if ($u['id'] === $id) {
                return $u;
            }
        }

        return null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function all(): array
    {
        if (!file_exists($this->file)) {
            return [];
        }

        $raw = (string) file_get_contents($this->file);
        $data = json_decode($raw, true);

        return is_array($data) ? $data : [];
    }

    /**
     * @param list<array<string, mixed>> $users
     */
    private function save(array $users): void
    {
        $dir = dirname($this->file);
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            throw new ApiException('用户存储目录创建失败', 500);
        }

        $json = json_encode($users, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        if ($json === false
            || file_put_contents($this->file, $json, LOCK_EX) === false) {
            throw new ApiException('用户存储写入失败', 500);
        }
    }
}
