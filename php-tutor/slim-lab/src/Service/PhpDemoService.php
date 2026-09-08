<?php

declare(strict_types=1);

namespace Service;

/**
 * 原 api/php-demo.php 的全部 action 业务逻辑，原样搬迁（演示代码一字未改）。
 * 方法名 = 旧接口 ?action= 参数值。
 */
final class PhpDemoService
{
    /**
     * @return array<string, mixed>
     */
    public function arrayOps(): array
    {
        $arr = [3, 1, 4, 1, 5, 9, 2, 6, 5];
        $ops = [
            '原数组' => $arr,
            'array_sum' => array_sum($arr),
            'array_product' => array_product($arr),
            'max' => max($arr),
            'min' => min($arr),
            'count' => count($arr),
            'rsort后' => (function ($a) {
                rsort($a);
                return $a;
            })($arr),
            'array_unique' => array_unique($arr),
            'array_reverse' => array_reverse($arr),
            'array_chunk(3)' => array_chunk($arr, 3),
            'array_column模拟' => array_map(fn($v) => ['val' => $v], $arr),
        ];

        return ['message' => json_encode($ops, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)];
    }

    /**
     * @return array<string, mixed>
     */
    public function stringOps(): array
    {
        $str = 'Hello PHP 8.5 World!';
        $ops = [
            '原字符串' => $str,
            'strlen' => strlen($str),
            'strtolower' => strtolower($str),
            'strtoupper' => strtoupper($str),
            'ucfirst' => ucfirst(strtolower($str)),
            'ucwords' => ucwords(strtolower($str)),
            'strrev' => strrev($str),
            'trim' => trim("  {$str}  "),
            'str_replace' => str_replace('PHP', 'Hypertext Preprocessor', $str),
            'substr(0,5)' => substr($str, 0, 5),
            'strpos("PHP")' => strpos($str, 'PHP'),
            'explode(" ")' => explode(' ', $str),
            'implode("-")' => implode('-', explode(' ', $str)),
            'json_encode' => json_encode($str),
            'base64_encode' => base64_encode($str),
        ];

        return ['message' => json_encode($ops, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)];
    }

    /**
     * @return array<string, mixed>
     */
    public function dateOps(): array
    {
        $now = new \DateTime();
        $ops = [
            '当前时间' => $now->format('Y-m-d H:i:s'),
            'format("Y-m-d")' => $now->format('Y-m-d'),
            'format("H:i:s")' => $now->format('H:i:s'),
            'format("l")' => $now->format('l'),
            'format("W")' => $now->format('W'),
            'format("z")' => $now->format('z'),
            'timestamp' => $now->getTimestamp(),
            'timezone' => $now->getTimezone()->getName(),
            'tomorrow' => (clone $now)->modify('+1 day')->format('Y-m-d'),
            'last_monday' => (clone $now)->modify('last monday')->format('Y-m-d'),
            'first_day_month' => (clone $now)->modify('first day of this month')->format('Y-m-d'),
            'last_day_month' => (clone $now)->modify('last day of this month')->format('Y-m-d'),
            'diff_now' => $now->diff(new \DateTime('2026-12-31'))->format('%a天'),
        ];

        return ['message' => json_encode($ops, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonOps(): array
    {
        $data = [
            'name' => '张三',
            'age' => 28,
            'skills' => ['PHP', 'MySQL', 'Redis', 'Docker'],
            'active' => true,
            'metadata' => (object) ['created' => '2024-01-15', 'version' => 1.2],
        ];
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $ops = [
            '原数据' => $data,
            'json_encode' => $json,
            'json_decode(关联数组)' => json_decode($json, true),
            'json_decode(对象)' => json_decode($json),
            'JSON_UNESCAPED_SLASHES' => json_encode(['url' => 'https://example.com/api'], JSON_UNESCAPED_SLASHES),
            'JSON_NUMERIC_CHECK' => json_encode(['price' => '19.99', 'count' => '100'], JSON_NUMERIC_CHECK),
            'JSON_THROW_ON_ERROR' => 'json_encode 遇错抛异常 (需 try-catch)',
        ];

        return ['message' => json_encode($ops, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)];
    }

    /**
     * @return array<string, mixed>
     */
    public function regexOps(): array
    {
        $text = '联系电话: 138-1234-5678, 邮箱: test@example.com, 网址: https://php.net';
        $ops = [
            '原文本' => $text,
            '手机号匹配' => preg_match('/1[3-9]\d{9}/', $text, $m) ? $m[0] : '未找到',
            '所有手机号' => preg_match_all('/1[3-9]\d{9}/', $text, $m) ? $m[0] : '未找到',
            '邮箱匹配' => preg_match('/[\w.+-]+@[\w-]+\.[\w.-]+/', $text, $m) ? $m[0] : '未找到',
            'URL匹配' => preg_match('/https?:\/\/[\w.-]+\.[\w]+/', $text, $m) ? $m[0] : '未找到',
            '替换手机号' => preg_replace('/1[3-9]\d{9}/', '[手机号]', $text),
            '分割非数字' => preg_split('/\D+/', $text),
            '命名捕获' => preg_match('/(?P<phone>1[3-9]\d{9})/', $text, $m) ? json_encode($m, JSON_UNESCAPED_UNICODE) : '未找到',
        ];

        return ['message' => json_encode($ops, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)];
    }

    /**
     * @return array<string, mixed>
     */
    public function fileOps(): array
    {
        $tmpFile = sys_get_temp_dir() . '/php_demo_' . uniqid() . '.txt';
        $content = "PHP 文件操作演示\n" . date('Y-m-d H:i:s') . "\n随机数: " . random_int(1000, 9999);
        $ops = [];

        file_put_contents($tmpFile, $content);
        $ops['写入文件'] = $tmpFile;
        $ops['文件大小'] = filesize($tmpFile) . ' 字节';
        $ops['读取内容'] = file_get_contents($tmpFile);
        $ops['逐行读取'] = file($tmpFile);
        $ops['文件存在'] = file_exists($tmpFile) ? '是' : '否';
        $ops['可读'] = is_readable($tmpFile) ? '是' : '否';
        $ops['可写'] = is_writable($tmpFile) ? '是' : '否';
        $ops['MIME类型'] = mime_content_type($tmpFile);
        $ops['目录'] = dirname($tmpFile);
        $ops['文件名'] = basename($tmpFile);
        $ops['扩展名'] = pathinfo($tmpFile, PATHINFO_EXTENSION);

        unlink($tmpFile);
        $ops['删除后存在'] = file_exists($tmpFile) ? '是' : '否';

        return ['message' => json_encode($ops, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)];
    }

    /**
     * @return array<string, mixed>
     */
    public function randomOps(): array
    {
        $ops = [
            'random_int(1,100)' => random_int(1, 100),
            'random_bytes(16) hex' => bin2hex(random_bytes(16)),
            '随机UUID' => vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4)),
            'shuffle数组' => (function ($a) {
                shuffle($a);
                return $a;
            })(range(1, 10)),
            'array_rand(3)' => array_rand(range('a', 'z'), 3),
            'password_hash' => password_hash('secret123', PASSWORD_BCRYPT),
            'password_verify' => password_verify('secret123', password_hash('secret123', PASSWORD_BCRYPT)) ? '通过' : '失败',
        ];

        return ['message' => json_encode($ops, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)];
    }

    /**
     * @return array<string, mixed>
     */
    public function filterOps(): array
    {
        $data = [
            'email' => '  Test@Example.COM  ',
            'age' => '25',
            'price' => '199.99',
            'active' => 'yes',
            'tags' => 'php, mysql, docker',
            'url' => 'https://example.com/path?query=1',
        ];
        $ops = [
            '原数据' => $data,
            'FILTER_VALIDATE_EMAIL' => filter_var($data['email'], FILTER_VALIDATE_EMAIL),
            'FILTER_SANITIZE_EMAIL' => filter_var($data['email'], FILTER_SANITIZE_EMAIL),
            'FILTER_VALIDATE_INT(age)' => filter_var($data['age'], FILTER_VALIDATE_INT),
            'FILTER_VALIDATE_FLOAT(price)' => filter_var($data['price'], FILTER_VALIDATE_FLOAT),
            'FILTER_VALIDATE_BOOLEAN(active)' => filter_var($data['active'], FILTER_VALIDATE_BOOLEAN),
            'FILTER_VALIDATE_URL' => filter_var($data['url'], FILTER_VALIDATE_URL),
            'FILTER_SANITIZE_STRING(tags)' => filter_var($data['tags'], FILTER_SANITIZE_SPECIAL_CHARS),
            'filter_var_array' => filter_var_array($data, [
                'email' => FILTER_VALIDATE_EMAIL,
                'age' => ['filter' => FILTER_VALIDATE_INT, 'options' => ['min_range' => 18]],
                'price' => FILTER_VALIDATE_FLOAT,
                'active' => FILTER_VALIDATE_BOOLEAN,
                'url' => FILTER_VALIDATE_URL,
            ]),
        ];

        return ['message' => json_encode($ops, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)];
    }

    /**
     * @return array<string, mixed>
     */
    public function closureOps(): array
    {
        $multiply = fn($a, $b) => $a * $b;
        $numbers = [1, 2, 3, 4, 5];
        $ops = [
            '箭头函数 3*4' => $multiply(3, 4),
            'array_map 平方' => array_map(fn($n) => $n * $n, $numbers),
            'array_filter 偶数' => array_filter($numbers, fn($n) => $n % 2 === 0),
            'array_reduce 求和' => array_reduce($numbers, fn($carry, $n) => $carry + $n, 0),
            'array_reduce 乘积' => array_reduce($numbers, fn($carry, $n) => $carry * $n, 1),
            '闭包 use 外部变量' => (function ($nums) {
                $factor = 10;
                return array_map(fn($n) => $n * $factor, $nums);
            })($numbers),
            'First-class callable' => array_map(strlen(...), ['apple', 'banana', 'cherry']),
            'fn() 自动捕获' => (function ($nums) {
                $prefix = 'Item: ';
                return array_map(fn($n) => $prefix . $n, $nums);
            })($numbers),
        ];

        return ['message' => json_encode($ops, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)];
    }
}
