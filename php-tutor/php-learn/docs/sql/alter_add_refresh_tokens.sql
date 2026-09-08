-- ============================================================
-- 增量脚本：新增 refresh_tokens 表
-- ============================================================
--
-- 使用场景：
--   Bearer Token 鉴权下的 Refresh Token 存储。
--   Access Token 用 JWT 无状态签名（15 分钟过期），
--   Refresh Token 是随机 32 字节 hex（64 字符），存表以便：
--     - 服务端能主动撤销（登出、密码修改、账号封禁）
--     - 支持"记住我"跨设备管理
--
-- 使用步骤：
--   1) 首次运行项目：先执行 docs/sql/schema.sql 建库建表
--   2) 本次改造后：执行本脚本追加 refresh_tokens 表
--      mysql -uroot -p < docs\sql\alter_add_refresh_tokens.sql
--   3) 幂等设计：IF NOT EXISTS，可反复执行
--
-- 对应 Laravel Sanctum 的做法：
--   Sanctum 在 personal_access_tokens 表存 tokenable_id + token + expires_at，
--   我们的 refresh_tokens 是它的极简版。
--
-- 对应 Java Spring Security：
--   OAuth2 场景的 RefreshToken 存 redis 或 DB 都是标准做法。
-- ============================================================

SET NAMES utf8mb4;

USE `php_learn`;

CREATE TABLE IF NOT EXISTS `refresh_tokens` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
    `user_id`     BIGINT UNSIGNED NOT NULL                COMMENT '用户 id（逻辑外键到 users.id）',
    `token`       CHAR(64)        NOT NULL                COMMENT '随机 32 字节 hex（64 字符）',
    `expires_at`  DATETIME        NOT NULL                COMMENT '过期时间（access_ttl + refresh_ttl 之外的独立值）',
    `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_refresh_tokens_token` (`token`),
    KEY          `idx_refresh_tokens_user_id` (`user_id`),
    KEY          `idx_refresh_tokens_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Refresh Token 存储表';

-- 定期清理过期 Token（可以在 cron job 里跑，也可手动）
-- DELETE FROM `refresh_tokens` WHERE `expires_at` < NOW() - INTERVAL 7 DAY;

-- 验证：建表成功 + 结构符合预期
DESC `refresh_tokens`;
