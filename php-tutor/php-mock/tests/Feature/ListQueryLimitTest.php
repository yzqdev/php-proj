<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 列表查询参数的边界测试：size 上限 100、sort 排序
 */
class ListQueryLimitTest extends TestCase
{
    use RefreshDatabase;

    public function testSizeAbove100IsCappedTo100(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('t')->plainTextToken;

        $resource = $this->postJson(
            '/api/projects',
            ['name' => 'limit-test'],
            ['Authorization' => "Bearer {$token}"],
        )->json('data');

        $this->postJson(
            "/api/projects/{$resource['id']}/resources",
            [
                'name' => 'big',
                'count' => 250,
                'fields' => [
                    ['name' => 'n', 'type' => 'number', 'min' => 1, 'max' => 100],
                ],
            ],
            ['Authorization' => "Bearer {$token}"],
        )->assertCreated();

        // size=500 超上限 → 被限制为 100：每页最多 100 条，250 条分 3 页
        $this->getJson('/api/v1/mock/limit-test/big?size=500')
            ->assertOk()
            ->assertJsonCount(100, 'data')
            ->assertJsonPath('meta.total', 250)
            ->assertJsonPath('meta.last_page', 3);
    }

    public function testSortAscendingAndDescending(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('t')->plainTextToken;

        $project = $this->postJson(
            '/api/projects',
            ['name' => 'sort-test'],
            ['Authorization' => "Bearer {$token}"],
        )->json('data');

        $this->postJson(
            "/api/projects/{$project['id']}/resources",
            [
                'name' => 'items',
                'count' => 10,
                'fields' => [['name' => 'n', 'type' => 'number', 'min' => 1, 'max' => 1]],
            ],
            ['Authorization' => "Bearer {$token}"],
        )->assertCreated();

        $asc = $this->getJson('/api/v1/mock/sort-test/items?sort=id&size=3')->json('data');
        $desc = $this->getJson('/api/v1/mock/sort-test/items?sort=-id&size=3')->json('data');

        $this->assertGreaterThan($asc[0]['id'], $asc[2]['id'], '升序：id 递增');
        $this->assertLessThan($desc[0]['id'], $desc[2]['id'], '降序：id 递减');
    }
}
