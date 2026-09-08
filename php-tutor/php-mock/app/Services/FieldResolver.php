<?php

declare(strict_types=1);

namespace App\Services;

use Faker\Generator;
use Illuminate\Support\Str;

/**
 * 字段解析器：根据字段定义（Schema）生成一个假数据值
 *
 * 纯类（无状态），通过 AppServiceProvider 以单例绑定进容器。
 * match 表达式是 PHP 8 的严格 switch（严格比较、无穿透），非常适合这种
 * 「类型 -> 分支」的映射，类似 Java 的 switch 表达式（Java 14+）。
 */
class FieldResolver
{
    public function __construct(private readonly Generator $faker)
    {
    }

    /**
     * @param  array{name:string, type:string, ...}  $field  字段定义
     * @return mixed 生成的值；未知类型返回 null
     */
    public function resolve(array $field): mixed
    {
        $type = $field['type'] ?? '';

        return match ($type) {
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'company' => $this->faker->company,
            'number' => random_int(
                (int) ($field['min'] ?? 0),
                (int) ($field['max'] ?? 100)
            ),
            'enum' => $this->pickEnum($field),
            'bool' => $this->faker->boolean,
            'date' => $this->faker->dateTimeBetween('-2 years')->format('Y-m-d H:i:s'),
            'text' => $this->makeText((int) ($field['length'] ?? 200)),
            'uuid' => $this->faker->uuid,
            'url' => $this->faker->url,
            'image' => sprintf(
                'https://picsum.photos/seed/%s/%dx%d',
                Str::uuid()->toString(),
                (int) ($field['width'] ?? 200),
                (int) ($field['height'] ?? 200)
            ),
            // 未知类型统一返回 null，不抛异常（生成流程不因单个脏字段中断）
            default => null,
        };
    }

    private function pickEnum(array $field): ?string
    {
        $options = $field['options'] ?? [];

        return $options === [] ? null : $options[array_rand($options)];
    }

    /**
     * faker->text() 最多生成 200 字符，超长时先取全量文本再截断
     */
    private function makeText(int $length): string
    {
        $text = $length > 200
            ? $this->faker->sentence(200)
            : $this->faker->text($length);

        return mb_substr($text, 0, max(1, $length));
    }
}
