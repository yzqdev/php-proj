<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * HTML 输出助手
 *
 * 安全重点：凡是【来自用户输入】的字符串，进入 HTML 前必须转义。
 * 否则用户输入 <script>alert(1)</script> 就会被当成标签执行 —— XSS 攻击。
 *
 * Java 世界里对应的坑：Spring 的 ${} 默认不转义（SpEL 注入 / XSS），
 * 需要靠 @{...} 或显式 &quot; 处理。这里养成"输出即转义"的习惯。
 *
 * PHP 特性：htmlspecialchars() 内置函数，等价于 Java 的
 *            org.apache.commons.text.StringEscapeUtils.escapeHtml4()。
 */
final class Html
{
    /** 转义双引号是重点（属性值里的注入） */
    public static function escape(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * 便捷取值：$v('title') 会自动转义。模板里写起来比 Html::escape(...) 简洁。
     *
     * @param mixed|null $value 值；null 渲染为空串
     */
    public static function e(mixed $value): string
    {
        return $value === null ? '' : self::escape($value);
    }



    /**
     * UTF-8 安全的字符数统计
     *
     * PHP 8.5 本机可能未加载 mbstring 扩展（mb_strlen 不存在），
     * 用正则的 /u 修饰符按"码点"计数：
     *   preg_match_all('/./u', '你好abc') => 5（3 个中文 + 2 个英文）
     * 这是 PHP 8 处理中文/emoji 的现代替代方案。
     */
    public static function charLen(string $value): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($value, 'UTF-8');
        }
        return preg_match_all('/./us', $value);
    }


}
