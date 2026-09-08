-- ============================================================
-- php-learn 数据库初始化脚本
-- ============================================================
--
-- 使用方法（任选一种）：
--   方式 A（命令行，推荐）：
--     mysql -uroot -p < docs\sql\schema.sql
--   方式 B（命令行交互）：
--     mysql -uroot -p
--     > source F:/PhpStormProjects/php-proj/php-tutor/php-learn/docs/sql/schema.sql
--   方式 C（Navicat / DBeaver / DataGrip）：
--     右键 schema.sql → Run SQL Script
--
-- 脚本设计要点：
--   1. 幂等：DROP + CREATE，可反复执行（重建环境友好）；
--   2. utf8mb4 + 0900_ai_ci：MySQL 8 默认字符集，支持 emoji；
--   3. 每张表都有 id / created_at / updated_at；
--   4. 种子用户密码是 bcrypt 哈希（明文分别是 admin123 / user123）；
--   5. 种子文章覆盖 4 个分类 + 分页测试（共 12 篇）。
--
-- 表结构对应 Java / JPA：
--   自增主键       → @Id + @GeneratedValue(strategy = IDENTITY)
--   NOT NULL      → @Column(nullable = false)
--   UNIQUE 索引   → @Column(unique = true) / @UniqueConstraint
--   created_at    → @Column(insertable=false, updatable=false) + 触发器 / EntityListener
-- ============================================================

-- 使用 utf8mb4 客户端字符集，避免中文乱码
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 删除已存在的库（幂等重建）
DROP DATABASE IF EXISTS `php_learn`;
CREATE DATABASE `php_learn`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_0900_ai_ci;
USE `php_learn`;

-- ============================================================
-- 1) 用户表 —— 登录认证
-- ============================================================
CREATE TABLE `users` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
    `username`   VARCHAR(50)     NOT NULL                COMMENT '用户名（登录名）',
    `email`      VARCHAR(100)    NOT NULL                COMMENT '邮箱（唯一）',
    `password`   VARCHAR(255)    NOT NULL                COMMENT 'bcrypt 哈希，password_hash() 生成',
    `role`       VARCHAR(20)     NOT NULL DEFAULT 'user' COMMENT '角色：user / admin',
    `created_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_users_email`    (`email`),
    UNIQUE KEY `uk_users_username` (`username`),
    KEY          `idx_users_role`  (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户表';

-- ============================================================
-- 2) 文章表 —— CRUD + 分页主战场
-- ============================================================
CREATE TABLE `articles` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
    `title`      VARCHAR(120)    NOT NULL                COMMENT '标题',
    `body`       TEXT            NOT NULL                COMMENT '正文',
    `category`   VARCHAR(20)     NOT NULL DEFAULT 'other' COMMENT '分类：php/java/db/other',
    `status`     VARCHAR(20)     NOT NULL DEFAULT 'published' COMMENT '状态：draft / published',
    `created_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_articles_category`  (`category`),
    KEY `idx_articles_status`    (`status`),
    KEY `idx_articles_created`   (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='文章表';

-- ============================================================
-- 3) 活动表 —— 保留原有 ActivityController 对应的业务
-- ============================================================
CREATE TABLE `activities` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
    `title`      VARCHAR(120)    NOT NULL                COMMENT '活动名称',
    `location`   VARCHAR(120)    NULL                    COMMENT '地点',
    `start_time` DATETIME        NOT NULL                COMMENT '开始时间',
    `end_time`   DATETIME        NULL                    COMMENT '结束时间',
    `description` TEXT           NULL                    COMMENT '活动描述',
    `created_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_activities_start` (`start_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='活动表';

-- ============================================================
-- 种子数据
-- ============================================================

-- 用户（密码是 bcrypt 哈希，明文分别是 admin123 和 user123）
INSERT INTO `users` (`username`, `email`, `password`, `role`, `created_at`) VALUES
('admin', 'admin@php-learn.local',
 '$2y$12$jfeho.uQQPWqlfVmvv16y.WrKLY2Xpvd67.DGYmez/fvx1RXsOcz2', 'admin',
 '2026-09-01 09:00:00'),
('user', 'user@php-learn.local',
 '$2y$12$wOocnqaEzpzYh.W4rnGMb.jaIz/rlQcA.9LngUJPZaCgswR6GLWZO', 'user',
 '2026-09-01 09:05:00');

-- 文章（12 篇，覆盖 4 个分类，测试分页）
INSERT INTO `articles` (`title`, `body`, `category`, `status`, `created_at`) VALUES
('PDO 与 JDBC 的异同',
 'PDO 是 PHP 的数据访问抽象，与 MySQL/pgsql/sqlite 都兼容；JDBC 是 Java 的同名抽象。两者都支持预处理语句，但 PDO 通过 DSN 描述连接目标，写法更紧凑。本项目 app/Database/Database.php 里可以逐行对照。',
 'php', 'published', '2026-09-02 10:00:00'),
('Composer 之于 Maven',
 'composer.json 相当于 pom.xml 的"项目清单"，autoload.psr-4 相当于 Maven 的源码目录约定。Composer 的自动加载是运行时按需 require，Maven 是全量编译进 classpath。',
 'php', 'published', '2026-09-02 10:15:00'),
('Session 与 Cookie 的关系',
 'Cookie 只保存一个 id（PHP_SESSION_ID），真正的会话数据在服务端文件里。这避免了把敏感信息暴露给客户端，也和 Java 的 JSESSIONID 完全一致。',
 'php', 'published', '2026-09-02 10:30:00'),
('表单校验的正确姿势',
 '链式 Validator 比一堆 if 更清晰。本项目 Validator::required()->min()->max()->in() 覆盖常用规则，mb_strlen 处理中文长度。',
 'php', 'published', '2026-09-03 09:00:00'),
('password_hash 与 bcrypt',
 'password_hash 默认使用 bcrypt，每次调用都会加随机盐，所以同一密码每次生成的哈希不同。用 password_verify 做常量时间比对。',
 'php', 'published', '2026-09-03 09:30:00'),
('PSR-4 自动加载详解',
 'PSR-4 是最常见的自动加载规范：命名空间 App\\Controllers 对应 app/Controllers 目录，类名对应文件名。Composer 的 autoload_psr4 配置里能看懂整张映射表。',
 'php', 'published', '2026-09-03 10:00:00'),
('Spring Boot 与 Laravel 对比',
 'Laravel 走注解 + 服务容器路线，与 Spring Boot 思路一致；本项目刻意不用框架，把"容器"用直接 new 代替，便于看清底层。',
 'java', 'published', '2026-09-04 10:00:00'),
('Spring MVC 的 @RequestMapping',
 'PHP 里没有注解，路由靠 Router::get() 显式注册。本项目的 app/routes.php 就是集中式路由表，类似 Django 的 urls.py。',
 'java', 'published', '2026-09-04 10:30:00'),
('MyBatis-Plus 的 LambdaQueryWrapper',
 '本项目的 QueryBuilder 是最小实现：Article::where("status","published")->all() 对应 Java 里的 LambdaQueryWrapper 链式写法。',
 'java', 'draft', '2026-09-05 10:00:00'),
('MySQL 索引设计入门',
 '本项目 articles 表加了三个索引：分类、状态、创建时间。EXPLAIN 命令可以看查询计划，判断索引是否生效。',
 'db', 'published', '2026-09-05 14:00:00'),
('事务：BEGIN / COMMIT / ROLLBACK',
 'PHP 8 用 PDO::beginTransaction() / commit() / rollBack()。本项目 Database 封装里没做事务（保持最小），但真实支付/订单场景必须加。',
 'db', 'published', '2026-09-05 15:00:00'),
('utf8mb4 与中文乱码',
 'MySQL 8 默认 utf8mb4_0900_ai_ci，客户端连接时也要 SET NAMES utf8mb4。本项目 config/db/charset = "utf8mb4"，Database.php 里拼进 DSN。',
 'db', 'published', '2026-09-06 09:00:00');

-- 活动（保留原有 ActivityController 演示用）
INSERT INTO `activities` (`title`, `location`, `start_time`, `end_time`, `description`, `created_at`) VALUES
('PHP 8.5 技术分享会', '北京·中关村', '2026-10-01 14:00:00', '2026-10-01 17:00:00',
 '面向 Java 开发者，讲清楚 PHP 8.5 相比 8.0 的新特性：readonly 类、枚举改进、JIT 调优。',
 '2026-09-01 12:00:00'),
('MySQL 高并发实战工作坊', '线上（腾讯会议）', '2026-10-15 19:00:00', '2026-10-15 21:00:00',
 '从索引设计、连接池、事务隔离级别到慢查询优化，一次讲透。',
 '2026-09-02 12:00:00'),
('PHP 与安全：CSRF/XSS/SQLi', '线上（B 站直播）', '2026-11-08 20:00:00', '2026-11-08 22:00:00',
 '通过本项目代码，逐行拆解三种最常见的注入攻击与防御手段。',
 '2026-09-03 12:00:00');

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- 完成
-- ============================================================
SELECT 'php-learn 数据库初始化完成' AS status;
SELECT COUNT(*) AS articles_count FROM `articles`;
SELECT COUNT(*) AS users_count    FROM `users`;
SELECT COUNT(*) AS activities_count FROM `activities`;
