<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { Refresh, Right } from '@element-plus/icons-vue'
import { logDays, logQuery } from '@/api/logs'
import type { LogDay, LogLine, LogQueryResult, LogLevel } from '@/types/api'

/**
 * 运行日志：按天浏览后端 storage/logs/app-YYYY-MM-DD.log。
 * 日期与分页在服务端（单次遍历文件），级别与关键词为前端过滤。
 */

const LEVEL_OPTIONS: Array<{ label: string; value: LogLevel | '' }> = [
  { label: '全部级别', value: '' },
  { label: 'DEBUG', value: 'DEBUG' },
  { label: 'INFO', value: 'INFO' },
  { label: 'NOTICE', value: 'NOTICE' },
  { label: 'WARNING', value: 'WARNING' },
  { label: 'ERROR', value: 'ERROR' },
  { label: 'CRITICAL', value: 'CRITICAL' },
  { label: 'ALERT', value: 'ALERT' },
  { label: 'EMERGENCY', value: 'EMERGENCY' },
]

const LEVEL_TYPE: Record<LogLevel, 'primary' | 'success' | 'warning' | 'danger' | 'info'> = {
  DEBUG: 'info',
  INFO: 'success',
  NOTICE: 'primary',
  WARNING: 'warning',
  ERROR: 'danger',
  CRITICAL: 'danger',
  ALERT: 'danger',
  EMERGENCY: 'danger',
}

const PAGE_SIZES = [200, 500, 1000, 2000]
const AUTO_REFRESH_MS = 5000

const days = ref<LogDay[]>([])
const result = ref<LogQueryResult | null>(null)
const loading = ref(false)
const selectedDate = ref(formatDate(new Date()))
const levelFilter = ref<LogLevel | ''>('')
const keyword = ref('')
const pageSize = ref(500)
const offset = ref(0)
const autoRefresh = ref(false)

const dateShortcuts = [
  { text: '今天', value: () => new Date() },
  { text: '昨天', value: () => new Date(Date.now() - 86_400_000) },
  { text: '前天', value: () => new Date(Date.now() - 2 * 86_400_000) },
]

const lines = computed<LogLine[]>(() => result.value?.lines ?? [])
const totalPages = computed(() =>
  result.value && result.value.total > 0 ? Math.ceil(result.value.total / pageSize.value) : 0,
)
const currentPage = computed(() => Math.floor(offset.value / pageSize.value) + 1)
const hasMore = computed(
  () => (result.value?.truncated ?? false) || currentPage.value < totalPages.value,
)

/** 本页内按级别 + 关键词过滤（级别/关键词均为前端过滤，不改服务端窗口） */
const filtered = computed<LogLine[]>(() => {
  const raw = lines.value
  const kw = keyword.value.trim().toLowerCase()
  if (levelFilter.value === '' && kw === '') return raw

  return raw.filter((line) => {
    if (levelFilter.value !== '' && line.level !== levelFilter.value) return false
    if (kw === '') return true

    const haystack = [
      line.timestamp ?? '',
      line.channel ?? '',
      line.level ?? '',
      line.message,
      JSON.stringify(line.context ?? ''),
    ]
      .join(' ')
      .toLowerCase()

    return haystack.includes(kw)
  })
})

async function loadDays() {
  try {
    const { data: res } = await logDays(30)
    days.value = res.data?.days ?? []
  } catch {
    // 日期列表只是辅助信息，失败时不影响日志展示；提示已由拦截器弹出
  }
}

async function loadPage() {
  loading.value = true
  try {
    const { data: res } = await logQuery(selectedDate.value, offset.value, pageSize.value)
    result.value = res.data ?? null
  } finally {
    loading.value = false
  }
}

async function load() {
  await Promise.allSettled([loadDays(), loadPage()])
}

/** 选中日期变化：回到该天第一页 */
watch(selectedDate, () => {
  offset.value = 0
  void load()
})

/** 自动刷新只重拉当前窗口，不重置翻页位置 */
let timer: number | undefined

function stopTimer() {
  if (timer === undefined) return
  window.clearInterval(timer)
  timer = undefined
}

watch(
  autoRefresh,
  (on) => {
    stopTimer()
    if (!on) return
    timer = window.setInterval(() => {
      void loadPage()
    }, AUTO_REFRESH_MS)
  },
  { immediate: true },
)

function onPageChange(page: number) {
  offset.value = (page - 1) * pageSize.value
  void loadPage()
}

function onSizeChange(size: number | string) {
  pageSize.value = Number(size)
  offset.value = 0
  void loadPage()
}

/** 跳到当天最后一页，便于看最新日志 */
function gotoLast() {
  const total = result.value?.total ?? 0
  offset.value = Math.max(0, total - pageSize.value)
  void loadPage()
}

function formatDate(d: Date): string {
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')

  return `${d.getFullYear()}-${month}-${day}`
}

function formatBytes(bytes: number): string {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`

  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

function contextText(context: LogLine['context']): string {
  return context === null ? '' : JSON.stringify(context)
}

/** el-tag 的级别配色；无级别（原文无法解析的行）统一按 DEBUG 处理 */
function levelType(level: LogLine['level']): 'primary' | 'success' | 'warning' | 'danger' | 'info' {
  return level === null ? 'info' : LEVEL_TYPE[level]
}

onMounted(() => {
  void load()
})

onBeforeUnmount(stopTimer)
</script>

<template>
  <div class="space-y-5">
    <div class="rounded-xl bg-white p-5 shadow-md">
      <div class="flex flex-wrap items-end gap-3">
        <div class="w-44">
          <div class="mb-1 text-xs text-gray-400">日志日期</div>
          <el-date-picker
            v-model="selectedDate"
            type="date"
            size="default"
            value-format="YYYY-MM-DD"
            :shortcuts="dateShortcuts"
            :clearable="false"
            class="!w-full"
          />
        </div>

        <div class="w-32">
          <div class="mb-1 text-xs text-gray-400">级别</div>
          <el-select v-model="levelFilter" size="default" class="!w-full">
            <el-option
              v-for="item in LEVEL_OPTIONS"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </div>

        <div class="min-w-48 flex-1">
          <div class="mb-1 text-xs text-gray-400">关键词（时间 / 消息 / 上下文）</div>
          <el-input
            v-model="keyword"
            size="default"
            placeholder="如 500、exception、/api/logs"
            clearable
          />
        </div>

        <div class="w-28">
          <div class="mb-1 text-xs text-gray-400">每页行数</div>
          <el-select v-model="pageSize" size="default" class="!w-full" @change="onSizeChange">
            <el-option v-for="size in PAGE_SIZES" :key="size" :label="`${size} 行`" :value="size" />
          </el-select>
        </div>

        <div class="flex items-center gap-4 pt-5">
          <el-checkbox v-model="autoRefresh" :label="`每 ${AUTO_REFRESH_MS / 1000} 秒自动刷新`" />
          <el-button :icon="Refresh" :loading="loading" @click="() => void load()">刷新</el-button>
        </div>
      </div>

      <div v-if="days.length > 0" class="mt-4 border-t border-gray-100 pt-3">
        <div class="mb-2 text-xs text-gray-400">可查日期（新 → 旧）</div>
        <div class="flex flex-wrap gap-2">
          <el-tag
            v-for="day in days"
            :key="day.date"
            :type="day.date === selectedDate ? 'primary' : 'info'"
            :effect="day.date === selectedDate ? 'dark' : 'light'"
            class="cursor-pointer"
            @click="
              () => {
                selectedDate = day.date
              }
            "
          >
            {{ day.date }} · {{ formatBytes(day.bytes) }}
          </el-tag>
        </div>
      </div>
    </div>

    <div v-loading="loading" class="overflow-hidden rounded-xl bg-white shadow-md">
      <div
        v-if="result !== null && result.total === 0"
        class="flex flex-col items-center justify-center px-6 py-16 text-gray-400"
      >
        <div class="mb-2 text-3xl">📭</div>
        <div class="text-base font-medium text-gray-500">{{ selectedDate }} 暂无日志</div>
        <div class="mt-1 text-sm">
          日志写在 storage/logs/app-{{ selectedDate }}.log，产生请求后会自动出现
        </div>
      </div>

      <el-table
        v-else
        :data="filtered"
        size="small"
        :row-key="(row: LogLine) => String(row.no)"
        :show-overflow-tooltip="false"
      >
        <el-table-column prop="no" label="#" width="76" align="right">
          <template #default="{ row }">
            <span class="font-mono text-xs text-gray-400">{{ row.no }}</span>
          </template>
        </el-table-column>
        <el-table-column label="时间" width="150">
          <template #default="{ row }">
            <span class="font-mono text-xs">{{ row.timestamp ?? '—' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="级别" width="100">
          <template #default="{ row }">
            <el-tag v-if="row.level" :type="levelType(row.level)" size="small" effect="light">
              {{ row.level }}
            </el-tag>
            <span v-else class="text-xs text-gray-300">—</span>
          </template>
        </el-table-column>
        <el-table-column label="消息" min-width="320">
          <template #default="{ row }">
            <span class="text-sm">{{ row.message }}</span>
          </template>
        </el-table-column>
        <el-table-column label="上下文" min-width="300">
          <template #default="{ row }">
            <div
              v-if="row.context !== null"
              class="max-h-16 overflow-y-auto break-all rounded bg-gray-50 px-2 py-1 font-mono text-xs leading-4 text-gray-500"
            >
              {{ contextText(row.context) }}
            </div>
            <span v-else class="text-xs text-gray-300">—</span>
          </template>
        </el-table-column>
      </el-table>
    </div>

    <div
      v-if="result !== null && result.total > 0"
      class="flex flex-wrap items-center justify-between gap-3"
    >
      <div class="text-sm text-gray-500">
        <span>共 {{ result.total.toLocaleString() }} 行</span>
        <span class="mx-2 text-gray-300">·</span>
        <span> 本页 {{ filtered.length }} / {{ lines.length }} 行 </span>
        <span v-if="levelFilter !== '' || keyword.trim() !== ''" class="ml-2 text-amber-600">
          （已按级别 / 关键词过滤）
        </span>
      </div>

      <div class="flex items-center gap-3">
        <el-button :disabled="currentPage <= 1" @click="() => onPageChange(currentPage - 1)"
          >上一页</el-button
        >
        <el-pagination
          :current-page="currentPage"
          :page-size="pageSize"
          :total="result.total"
          layout="prev, pager, next"
          :pager-count="7"
          background
          @current-change="onPageChange"
        />
        <el-button v-if="hasMore" :icon="Right" @click="gotoLast">跳到最新</el-button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.el-table {
  --el-table-border-color: #f1f5f9;
}

.el-table .cell {
  word-break: break-word;
}
</style>
