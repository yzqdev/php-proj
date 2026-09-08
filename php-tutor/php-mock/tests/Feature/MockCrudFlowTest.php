<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\MockRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 完整链路特性测试：
 * 注册/登录 → 创建项目 → 定义资源（生成数据）→ 列表分页/搜索
 * → Mock 新增 → 更新 → 删除 → 重新生成 / 清空
 */
class MockCrudFlowTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->token = User::factory()->create()->createToken('test')->plainTextToken;
    }

    private function authHeaders(): array
    {
        return ['Authorization' => "Bearer {$this->token}"];
    }

    private function fieldsPayload(): array
    {
        return [
            'name' => 'orders',
            'count' => 30,
            'fields' => [
                ['name' => 'customer', 'type' => 'name'],
                ['name' => 'amount', 'type' => 'number', 'min' => 10, 'max' => 999],
                ['name' => 'status', 'type' => 'enum', 'options' => ['pending', 'paid']],
            ],
        ];
    }

    public function testFullCrudFlow(): void
    {
        // 1. 创建项目
        $project = $this->postJson('/api/projects', ['name' => 'flow-project'], $this->authHeaders())
            ->assertCreated()
            ->assertJsonPath('code', 0)
            ->assertJsonPath('meta', [])
            ->json('data');

        $this->assertNotEmpty($project['slug']);
        $this->assertSame(64, strlen($project['api_key']));

        // 2. 定义资源并生成 30 条
        $resource = $this->postJson(
            "/api/projects/{$project['id']}/resources",
            $this->fieldsPayload(),
            $this->authHeaders(),
        )
            ->assertCreated()
            ->assertJsonPath('data.generated', 30)
            ->json('data.resource');

        $this->assertSame('orders', $resource['name']);
        $this->assertSame(30, $resource['total']);

        // 3. 匿名访问列表（分页）
        $list = $this->getJson("/api/v1/mock/{$project['slug']}/orders?page=1&size=10")
            ->assertOk()
            ->assertHeader('X-Mock-Data', 'true')
            ->json();

        $this->assertSame(0, $list['code']);
        $this->assertCount(10, $list['data']);
        $this->assertSame(30, $list['meta']['total']);
        $this->assertSame(3, $list['meta']['last_page']);
        // id 已合并进 data
        $this->assertArrayHasKey('id', $list['data'][0]);
        $this->assertArrayHasKey('customer', $list['data'][0]);

        // 4. keyword + search_in 模糊搜索：先生成含确定关键词的数据
        $record = MockRecord::create([
            'mock_resource_id' => $resource['id'],
            'data' => ['customer' => 'UNIQUE_HARRY', 'amount' => 1, 'status' => 'pending'],
        ]);

        $searched = $this->getJson(
            "/api/v1/mock/{$project['slug']}/orders?keyword=UNIQUE_HARRY&search_in=customer"
        )
            ->assertOk()
            ->json();

        $this->assertSame(1, $searched['meta']['total']);
        $this->assertSame($record->id, $searched['data'][0]['id']);

        // 5. Mock 新增
        $created = $this->postJson(
            "/api/v1/mock/{$project['slug']}/orders",
            ['customer' => 'LiLei', 'amount' => 42, 'status' => 'pending'],
        )
            ->assertCreated()
            ->json('data');

        $this->assertSame('LiLei', $created['customer']);
        $this->assertArrayHasKey('id', $created);

        // 6. Mock 更新（合并 data）
        $this->putJson(
            "/api/v1/mock/{$project['slug']}/orders/{$created['id']}",
            ['status' => 'paid'],
        )
            ->assertOk()
            ->assertJsonPath('data.status', 'paid')
            ->assertJsonPath('data.customer', 'LiLei');

        // 7. Mock 详情
        $this->getJson("/api/v1/mock/{$project['slug']}/orders/{$created['id']}")
            ->assertOk()
            ->assertJsonPath('data.status', 'paid');

        // 8. Mock 删除
        $this->deleteJson("/api/v1/mock/{$project['slug']}/orders/{$created['id']}")
            ->assertOk()
            ->assertJsonPath('data.deleted', $created['id']);

        // 删除后详情 404
        $this->getJson("/api/v1/mock/{$project['slug']}/orders/{$created['id']}")
            ->assertNotFound()
            ->assertJsonPath('code', 404);

        // 9. 重新生成：先清空旧数据（30 条 + 搜索用的 1 条 → 清空后变 100 条）
        $this->postJson("/api/resources/{$resource['id']}/generate", ['count' => 100], $this->authHeaders())
            ->assertOk()
            ->assertJsonPath('data.generated', 100)
            ->assertJsonPath('data.resource.total', 100);

        $this->assertSame(100, MockRecord::where('mock_resource_id', $resource['id'])->count());
        $this->assertSame(
            0,
            MockRecord::where('mock_resource_id', $resource['id'])->where('data->customer', 'UNIQUE_HARRY')->count(),
            '重新生成后旧记录应被清空，不应再有 UNIQUE_HARRY'
        );

        // 10. 清空数据
        $this->deleteJson("/api/resources/{$resource['id']}/records", [], $this->authHeaders())
            ->assertOk()
            ->assertJsonPath('data.deleted', 100);

        $this->assertSame(0, MockRecord::where('mock_resource_id', $resource['id'])->count());
    }

    public function testUnauthenticatedManagementRoutesAreRejected(): void
    {
        $this->getJson('/api/projects')->assertUnauthorized()->assertJsonPath('code', 401);
        $this->postJson('/api/projects', ['name' => 'x'])->assertUnauthorized();
    }

    public function testOtherUsersProjectIsHiddenAs404(): void
    {
        $project = $this->postJson('/api/projects', ['name' => 'mine'], $this->authHeaders())->json('data');

        $other = User::factory()->create();

        // 注：测试进程内 Sanctum Guard 会缓存已解析用户，跨用户切换用 actingAs 模拟
        $this->actingAs($other, 'sanctum')
            ->getJson("/api/projects/{$project['id']}")
            ->assertNotFound();
    }

    public function testInvalidFieldTypeIsRejected(): void
    {
        $project = $this->postJson('/api/projects', ['name' => 'schema-test'], $this->authHeaders())->json('data');

        $this->postJson(
            "/api/projects/{$project['id']}/resources",
            [
                'name' => 'bad',
                'fields' => [['name' => 'a', 'type' => 'made_up_type']],
            ],
            $this->authHeaders(),
        )->assertStatus(422)->assertJsonPath('code', 422);

        // enum 缺 options
        $this->postJson(
            "/api/projects/{$project['id']}/resources",
            [
                'name' => 'bad2',
                'fields' => [['name' => 'a', 'type' => 'enum']],
            ],
            $this->authHeaders(),
        )->assertStatus(422);
    }

    public function testResourceNameMustBeUniqueInProject(): void
    {
        $project = $this->postJson('/api/projects', ['name' => 'dup-test'], $this->authHeaders())->json('data');
        $payload = ['name' => 'dup', 'fields' => [['name' => 'a', 'type' => 'name']]];

        $this->postJson("/api/projects/{$project['id']}/resources", $payload, $this->authHeaders())->assertCreated();
        $this->postJson("/api/projects/{$project['id']}/resources", $payload, $this->authHeaders())
            ->assertStatus(409);
    }
}
