<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\FieldResolver;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;

/**
 * FieldResolver 各字段类型生成逻辑的单元测试
 */
class FieldResolverTest extends TestCase
{
    private FieldResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $faker = Factory::create('zh_CN');
        $this->resolver = new FieldResolver($faker);
    }

    public function testResolvesAllSimpleTypes(): void
    {
        $this->assertNotEquals('', $this->resolver->resolve(['name' => 'f', 'type' => 'name']));
        $this->assertMatchesRegularExpression('/^.*@.*$/', $this->resolver->resolve(['name' => 'f', 'type' => 'email']));
        $this->assertMatchesRegularExpression('/^1\d{10}$/', (string) $this->resolver->resolve(['name' => 'f', 'type' => 'phone']));
        $this->assertNotEquals('', $this->resolver->resolve(['name' => 'f', 'type' => 'address']));
        $this->assertNotEquals('', $this->resolver->resolve(['name' => 'f', 'type' => 'company']));
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $this->resolver->resolve(['name' => 'f', 'type' => 'date']));
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $this->resolver->resolve(['name' => 'f', 'type' => 'uuid']));
        $this->assertStringStartsWith('http', $this->resolver->resolve(['name' => 'f', 'type' => 'url']));
    }

    public function testNumberRespectsMinAndMax(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $value = $this->resolver->resolve(['name' => 'f', 'type' => 'number', 'min' => 18, 'max' => 60]);
            $this->assertIsInt($value);
            $this->assertGreaterThanOrEqual(18, $value);
            $this->assertLessThanOrEqual(60, $value);
        }
    }

    public function testNumberDefaultsToZeroHundred(): void
    {
        $value = $this->resolver->resolve(['name' => 'f', 'type' => 'number']);
        $this->assertIsInt($value);
        $this->assertGreaterThanOrEqual(0, $value);
        $this->assertLessThanOrEqual(100, $value);
    }

    public function testEnumPicksFromOptions(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $value = $this->resolver->resolve([
                'name' => 'f',
                'type' => 'enum',
                'options' => ['active', 'disabled'],
            ]);
            $this->assertContains($value, ['active', 'disabled']);
        }
    }

    public function testBoolAndTextAndImage(): void
    {
        $this->assertIsBool($this->resolver->resolve(['name' => 'f', 'type' => 'bool']));

        $text = $this->resolver->resolve(['name' => 'f', 'type' => 'text', 'length' => 50]);
        $this->assertIsString($text);
        $this->assertLessThanOrEqual(50, mb_strlen($text));

        $image = $this->resolver->resolve(['name' => 'f', 'type' => 'image', 'width' => 300, 'height' => 120]);
        $this->assertMatchesRegularExpression('#^https://picsum\.photos/seed/[0-9a-f-]{36}/300x120$#', $image);
    }

    public function testUnknownTypeReturnsNull(): void
    {
        $this->assertNull($this->resolver->resolve(['name' => 'f', 'type' => 'nonexistent']));
        $this->assertNull($this->resolver->resolve(['name' => 'f']));
    }
}
