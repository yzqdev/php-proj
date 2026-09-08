<?php

declare(strict_types=1);

namespace App\Helpers;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger as MonologLogger;

/**
 * ============================================================
 * 日志助手（Monolog 封装 · 单例 · 静态门面）
 * ============================================================
 *
 * 为什么不直接用 new Monolog\Logger()？
 *   - Monolog 的 Logger 是"可注入的"，适合有 DI 容器的框架；
 *   - 本项目无框架、无容器，每个地方都要 new 一遍 Logger 太啰嗦；
 *   - 用静态门面（Static Facade）模式，调用点一行搞定：
 *         Logger::info('用户登录', ['userId' => 1]);
 *
 * 对应 Java / Spring Boot：
 *   - Spring 用 SLF4J + Logback，注入 @Autowired Logger；
 *   - 本项目用 Monolog + Static Facade，效果等价但更轻量。
 *
 * PHP 8.x 特性：
 *   - readonly 属性：logger 实例只在初始化时赋值一次；
 *   - 命名参数：构造时参数顺序无关；
 *   - nullsafe operator：Config::get('logger.level') ?? 'info'。
 */
final class Logger
{
    /** @var readonly MonologLogger|null */
    private static ?MonologLogger $logger = null;

    private function __construct()
    {
        // 私有构造器：单例，禁止 new
    }

    /**
     * 初始化日志系统（幂等：重复调用返回同一个实例）
     *
     * 延迟初始化：直到第一次 Logger::info() 才真正创建 Monolog 实例。
     * 好处：config 未加载时不会报错（虽然正常流程里 config 总是先加载）。
     */
    private static function init(): MonologLogger
    {
        if (self::$logger !== null) {
            return self::$logger;
        }

        // 读取配置
        $path      = Config::get('logger.path', dirname(__DIR__, 2) . '/logs');
        $levelStr  = Config::get('logger.level', 'info');
        $maxFiles  = (int) Config::get('logger.max_files', 30);

        // 确保日志目录存在
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        // 把字符串级别转成 Monolog 2.x 的 int 常量
        // Monolog 2.x 级别是 int（Logger::DEBUG=100, Logger::INFO=200...），不是 enum
        $level = self::levelFromString($levelStr);

        // 创建 RotatingFileHandler（按天轮转日志）
        // 日志文件名格式：app-2026-09-05.log
        // maxFiles=30 表示保留最近 30 天，超出自动删除
        $handler = new RotatingFileHandler(
            $path . '/app',
            $maxFiles,
            $level,
            false,     // useLink 关闭（Windows 下不支持符号链接）
            0644,      // 文件权限
        );

        // 自定义日志格式（对应 Java Logback 的 patternLayout）
        // 示例输出：
        //   [2026-09-05T22:45:00+08:00] app.INFO: 用户登录 {"userId":1} {}
        //                    └─ 时间戳 ─┘ └─ 通道名 ─┘  └─ 消息 ─┘  └─ context ─┘  └─ extra ─┘
        $handler->setFormatter(new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            'Y-m-d\TH:i:sP',  // ISO 8601 + 时区偏移
            false,            // includeStacktracesOnExtraLevel
            true,             // ignoreEmptyContextAndExtra
        ));

        // 创建 Logger 实例
        $logger = new MonologLogger('app', [
            'handler' => $handler,
        ]);

        // 存入 readonly 属性
        self::$logger = $logger;

        return $logger;
    }

    /**
     * 把字符串级别转成 Monolog 2.x 的 int 级别常量
     *
     * Monolog 2.x 的级别是 int 常量（不是 enum），
     * 这里用 match 表达式做映射。
     *
     * @param string $levelStr debug|info|notice|warning|error|critical
     * @return int
     */
    private static function levelFromString(string $levelStr): int
    {
        return match (strtolower($levelStr)) {
            'debug'    => MonologLogger::DEBUG,
            'info'     => MonologLogger::INFO,
            'notice'   => MonologLogger::NOTICE,
            'warning'  => MonologLogger::WARNING,
            'error'    => MonologLogger::ERROR,
            'critical' => MonologLogger::CRITICAL,
            default    => MonologLogger::INFO,
        };
    }

    // =====================================================================
    // 静态门面方法（对应 Java 的 LoggerFactory.getLogger()）
    // =====================================================================

    /**
     * 记录 DEBUG 级别日志
     *
     * 用途：开发调试信息，生产环境通常关闭
     *
     * @param string               $message 日志消息
     * @param array<string, mixed> $context 上下文（结构化数据，PSR-3 标准）
     */
    public static function debug(string $message, array $context = []): void
    {
        self::init()->debug($message, $context);
    }

    /**
     * 记录 INFO 级别日志
     *
     * 用途：正常业务操作（登录、创建、更新、删除等）
     */
    public static function info(string $message, array $context = []): void
    {
        self::init()->info($message, $context);
    }

    /**
     * 记录 NOTICE 级别日志
     *
     * 用途：值得关注但不紧急的事件（如配置变更、缓存刷新）
     */
    public static function notice(string $message, array $context = []): void
    {
        self::init()->notice($message, $context);
    }

    /**
     * 记录 WARNING 级别日志
     *
     * 用途：潜在问题（如参数校验失败、权限不足、资源即将过期）
     */
    public static function warning(string $message, array $context = []): void
    {
        self::init()->warning($message, $context);
    }

    /**
     * 记录 ERROR 级别日志
     *
     * 用途：业务异常、非预期错误
     */
    public static function error(string $message, array $context = []): void
    {
        self::init()->error($message, $context);
    }

    /**
     * 记录 CRITICAL 级别日志
     *
     * 用途：严重故障（数据库连接失败、系统不可用）
     */
    public static function critical(string $message, array $context = []): void
    {
        self::init()->critical($message, $context);
    }

    /**
     * 获取底层 Monolog Logger 实例（高级用法）
     *
     * 用途：需要自定义 handler 或 push processor 时
     *
     * @return MonologLogger
     */
    public static function instance(): MonologLogger
    {
        return self::init();
    }
}
