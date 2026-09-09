<?php

declare(strict_types=1);

namespace App\Middleware;

/**
 * clouddrive 访问规则：集中保存旧 api/clouddrive.php switch 中每个 action 的
 * 方法要求（POST 才允许 → 405）与登录要求（401），供鉴权中间件与旧版
 * 兼容分发共用，保证与迁移前逐分支行为一致。
 */
final class CloudDriveAccessRules
{
    /** 与旧代码一致：这些 action 必须用 POST 访问，否则 apiError('方法不允许', 405)。
     * share_info 为新增公开接口，沿用 create_share 的 POST 语义；share_download/zip/stream 为 GET。 */
    public const POST_ONLY_ACTIONS = [
        'login', 'global_search', 'create_share', 'delete_share', 'mkdir', 'upload',
        'change_pwd', 'delete', 'batch_delete', 'rename', 'move', 'share_info',
    ];

    /** 免登录 action：旧有四个 + 分享访客四件套（token+提取密码即凭证） */
    public const PUBLIC_ACTIONS = ['login', 'logout', 'check', 'stream', 'share_info', 'share_download', 'share_zip', 'share_stream'];

    /**
     * 全部已知 action（旧 switch + 分享访客接口）；用于把未知 action 与"未登录"区分开，
     * 保证未知 action 无论登录与否都返回 400"未知操作"（等价旧 switch default 分支）。
     */
    public const ALL_ACTIONS = [
        'login', 'logout', 'check', 'global_search', 'create_share', 'get_share_list',
        'delete_share', 'mkdir', 'upload', 'change_pwd', 'list_dir', 'delete',
        'batch_delete', 'rename', 'move', 'download', 'zip', 'stream',
        'share_info', 'share_download', 'share_zip', 'share_stream',
    ];

    public static function isKnownAction(string $action): bool
    {
        return in_array($action, self::ALL_ACTIONS, true);
    }

    /**
     * 校验顺序与旧代码一致：先方法校验（405），再登录校验（401）。
     *
     * @return array{code: int, message: string}|null null 表示放行
     */
    public static function violation(string $action, string $method): ?array
    {
        if (in_array($action, self::POST_ONLY_ACTIONS, true) && $method !== 'POST') {
            return ['code' => 405, 'message' => '方法不允许'];
        }
        if (self::isKnownAction($action)
            && !in_array($action, self::PUBLIC_ACTIONS, true)
            && !(isset($_SESSION['login']) && $_SESSION['login'])) {
            return ['code' => 401, 'message' => '未登录'];
        }

        return null;
    }
}
