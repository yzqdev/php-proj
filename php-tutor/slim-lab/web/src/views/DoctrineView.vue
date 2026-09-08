<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { doctrineAction, doctrineStats } from '@/api/doctrine'

const userCount = ref(0)
const productCount = ref(0)
const logContainer = ref<{ type: string; time: string; action: string; message: string }[]>([])

interface ActionResult {
  title: string
  desc: string
  action: string
  variant: 'primary' | 'success' | 'warning' | 'danger'
}

const actions: ActionResult[] = [
  { title: '数据库初始化', desc: '重建所有表结构（清空数据）', action: 'init', variant: 'danger' },
  { title: '创建用户', desc: 'INSERT — 创建张三、李四两个用户', action: 'create_users', variant: 'primary' },
  { title: '创建商品', desc: 'INSERT + 关联 — 创建商品并分配给用户', action: 'create_products', variant: 'primary' },
  { title: '按 ID 查询', desc: 'SELECT — EntityManager::find()', action: 'find_user', variant: 'success' },
  { title: 'DQL 条件查询', desc: 'QueryBuilder — LIKE 模糊匹配', action: 'dql_query', variant: 'success' },
  { title: 'LEFT JOIN', desc: '关联查询 — 用户及其商品', action: 'join_query', variant: 'success' },
  { title: '修改数据', desc: 'UPDATE — 修改用户名', action: 'update_user', variant: 'warning' },
  { title: '删除数据', desc: 'DELETE — 删除第一个商品', action: 'delete_product', variant: 'danger' },
  { title: 'Repository', desc: 'Repository::findAll() 查询全部', action: 'repository', variant: 'success' },
  { title: 'COUNT 统计', desc: '标量结果 — 商品总数', action: 'count', variant: 'success' },
  { title: '事务回滚', desc: 'beginTransaction + rollback', action: 'transaction', variant: 'warning' },
]

async function refreshStats() {
  try {
    const { data: res } = await doctrineStats()
    if (res.data) {
      userCount.value = res.data.userCount
      productCount.value = res.data.productCount
    }
  } catch {
    // ignore
  }
}

async function runAction(item: ActionResult) {
  const time = new Date().toLocaleTimeString('zh-CN')
  try {
    const { data: res } = await doctrineAction(item.action)
    logContainer.value.unshift({
      type: 'success',
      time,
      action: item.action,
      message: res.data?.message ?? '',
    })
    refreshStats()
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : String(e)
    logContainer.value.unshift({
      type: 'error',
      time,
      action: item.action,
      message,
    })
  }
}

function clearLog() {
  logContainer.value = []
}

onMounted(refreshStats)
</script>

<template>
  <div class="space-y-6">
    <div class="flex gap-5">
      <div
        class="flex-1 rounded-xl bg-white/15 p-4 text-center text-white backdrop-blur-md"
      >
        <div class="text-3xl font-bold">{{ userCount }}</div>
        <div class="mt-1 text-sm opacity-90">用户数量</div>
      </div>
      <div
        class="flex-1 rounded-xl bg-white/15 p-4 text-center text-white backdrop-blur-md"
      >
        <div class="text-3xl font-bold">{{ productCount }}</div>
        <div class="mt-1 text-sm opacity-90">商品数量</div>
      </div>
    </div>

    <div class="grid grid-cols-[repeat(auto-fill,minmax(280px,1fr))] gap-4">
      <div
        v-for="item in actions"
        :key="item.action"
        class="rounded-xl bg-white p-5 shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
      >
        <h3 class="mb-2 text-base font-semibold text-gray-800">
          {{ item.title }}
        </h3>
        <p class="mb-3 text-sm text-gray-500">{{ item.desc }}</p>
        <el-button
          :type="item.variant"
          class="w-full"
          @click="runAction(item)"
        >
          {{ item.title }}
        </el-button>
      </div>
    </div>

    <div class="overflow-hidden rounded-2xl bg-[#1e1e2e] shadow-xl">
      <div
        class="flex items-center justify-between bg-gradient-to-r from-[#334155] to-[#1e293b] px-5 py-4"
      >
        <h2 class="text-base font-semibold text-white">操作日志</h2>
        <el-button size="small" @click="clearLog">清空</el-button>
      </div>
      <div class="max-h-[500px] overflow-y-auto p-5">
        <div v-if="logContainer.length === 0" class="py-10 text-center text-gray-500">
          点击上方按钮执行数据库操作
        </div>
        <div
          v-for="(log, index) in logContainer"
          :key="index"
          :class="[
            'mb-2 rounded-lg border-l-3 p-3 font-mono text-sm',
            log.type === 'success'
              ? 'border-green-500 bg-green-500/15 text-green-300'
              : 'border-red-500 bg-red-500/15 text-red-300',
          ]"
        >
          <div class="mb-1 text-xs text-gray-400">{{ log.time }}</div>
          <div class="font-semibold text-indigo-300">[{{ log.action }}]</div>
          <div class="whitespace-pre-wrap">{{ log.message }}</div>
        </div>
      </div>
    </div>
  </div>
</template>
