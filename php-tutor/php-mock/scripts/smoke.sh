#!/usr/bin/env bash
# ============================================================================
# 模拟数据 API 平台 - curl 冒烟脚本
# 流程：登录拿 token → 创建项目 → 定义资源（含生成）→ 匿名访问列表
#       → 新增一条 → 更新 → 删除 → 重新生成
# 用法：bash scripts/smoke.sh [BASE_URL]   （默认 http://127.0.0.1:8001）
# 依赖：curl、php（用于解析 JSON 响应）
# ============================================================================
set -e
BASE_URL="${1:-http://127.0.0.1:8001}"
API="$BASE_URL/api"

# 从 stdin 的 JSON 中按路径取值，如 JPATH=data.token
jget() { JPATH="$1" php -r '$d=json_decode(stream_get_contents(STDIN),true); foreach(explode(".", getenv("JPATH")) as $k){ $d=is_array($d)?($d[$k]??null):null; } echo is_array($d)?json_encode($d,JSON_UNESCAPED_UNICODE):$d;' ; }

echo "==================== 1. 登录拿 token ===================="
LOGIN=$(curl -s -X POST "$API/auth/login" -H "Content-Type: application/json" \
  -d '{"email":"demo@mock.dev","password":"password"}')
echo "$LOGIN" | head -c 200; echo " ..."
TOKEN=$(echo "$LOGIN" | jget data.token)
AUTH="Authorization: Bearer $TOKEN"

echo ""
echo "==================== 2. 创建项目 ===================="
PROJECT=$(curl -s -X POST "$API/projects" -H "$AUTH" -H "Content-Type: application/json" \
  -d '{"name":"smoke-test-project"}')
echo "$PROJECT"
PROJECT_ID=$(echo "$PROJECT" | jget data.id)
PROJECT_SLUG=$(echo "$PROJECT" | jget data.slug)
echo "PROJECT_ID=$PROJECT_ID  SLUG=$PROJECT_SLUG"

echo ""
echo "==================== 3. 定义资源（含生成 count=30） ===================="
RESOURCE=$(curl -s -X POST "$API/projects/$PROJECT_ID/resources" -H "$AUTH" -H "Content-Type: application/json" -d '{
  "name": "orders",
  "count": 30,
  "fields": [
    {"name": "order_no", "type": "uuid"},
    {"name": "customer", "type": "name"},
    {"name": "phone", "type": "phone"},
    {"name": "amount", "type": "number", "min": 10, "max": 9999},
    {"name": "status", "type": "enum", "options": ["pending", "paid", "refunded"]},
    {"name": "paid", "type": "bool"},
    {"name": "created", "type": "date"},
    {"name": "remark", "type": "text", "length": 100}
  ]
}')
echo "$RESOURCE" | head -c 400; echo " ..."
RESOURCE_ID=$(echo "$RESOURCE" | jget data.resource.id)
echo "RESOURCE_ID=$RESOURCE_ID  generated=$(echo "$RESOURCE" | jget data.generated)"

echo ""
echo "==================== 4. 匿名访问列表（size=3, sort=-id, keyword 搜索） ===================="
curl -s "$API/v1/mock/$PROJECT_SLUG/orders?page=1&size=3&sort=-id&keyword=pending&search_in=status"
echo ""

echo ""
echo "==================== 5. 新增一条 ===================="
CREATED=$(curl -s -X POST "$API/v1/mock/$PROJECT_SLUG/orders" \
  -H "Content-Type: application/json" \
  -d '{"order_no":"SMOKE-001","customer":"ZhangSan","amount":42,"status":"pending"}')
echo "$CREATED"
RECORD_ID=$(echo "$CREATED" | jget data.id)
echo "RECORD_ID=$RECORD_ID"

echo ""
echo "==================== 6. 更新该条 ===================="
curl -s -X PUT "$API/v1/mock/$PROJECT_SLUG/orders/$RECORD_ID" \
  -H "Content-Type: application/json" \
  -d '{"status":"paid","amount":99}'
echo ""

echo ""
echo "==================== 7. 查看详情 ===================="
curl -s "$API/v1/mock/$PROJECT_SLUG/orders/$RECORD_ID"
echo ""

echo ""
echo "==================== 8. 删除该条 ===================="
curl -s -X DELETE "$API/v1/mock/$PROJECT_SLUG/orders/$RECORD_ID"
echo ""

echo ""
echo "==================== 9. 重新生成（count=10） ===================="
curl -s -X POST "$API/resources/$RESOURCE_ID/generate" -H "$AUTH" -H "Content-Type: application/json" -d '{"count":10}'
echo ""

echo ""
echo "==================== 10. 验证 404 统一格式 ===================="
curl -s "$API/v1/mock/no-such-project/no-such-resource"
echo ""
echo ""
echo "==================== 冒烟测试完成 ===================="
