<script setup lang="ts">
import { logApi } from '@/api/logs'
import type { LogFileItem, LogRecord, LogTailResult } from '@/types/log'
import { LOG_LEVELS } from '@/types/log'
import { formatDateTime, formatIsoDateTime, formatSize } from '@/utils/format'

const files = ref<LogFileItem[]>([])
const current = ref<LogFileItem | null>(null)
const result = ref<LogTailResult | null>(null)
const loading = ref(false)
const filesLoading = ref(false)
const error = ref('')

const level = ref<string>('')
const limit = ref<number>(200)
const autoRefresh = ref(false)
let refreshTimer: ReturnType<typeof setInterval> | null = null

async function loadFiles(): Promise<void> {
  filesLoading.value = true
  try {
    const list = await logApi.list()
    files.value = list
    error.value = ''
    // 首次进入自动打开最新的那个文件
    const latest = list[0]
    if (!current.value && latest) {
      await selectFile(latest)
    }
  } catch (err) {
    error.value = err instanceof Error ? err.message : '日志列表加载失败'
  } finally {
    filesLoading.value = false
  }
}

async function selectFile(file: LogFileItem): Promise<void> {
  current.value = file
  level.value = ''
  await loadTail()
}

async function loadTail(): Promise<void> {
  if (!current.value) {
    return
  }
  loading.value = true
  try {
    result.value = await logApi.tail(current.value.name, {
      limit: limit.value,
      level: level.value || null,
    })
    error.value = ''
  } catch (err) {
    result.value = null
    error.value = err instanceof Error ? err.message : '日志内容加载失败'
  } finally {
    loading.value = false
  }
}

/** 表格行:在记录上附带已序列化的上下文,模板里只读纯文本 */
interface LogRow extends LogRecord {
  contextText: string
}

/**
 * 日志内容与上下文都是不可信文本,这里预先序列化成字符串,
 * 模板只通过 {{ }} 文本插值输出,不使用 v-html。
 */
const rows = computed<LogRow[]>(() => {
  const lines = result.value?.lines ?? []
  return lines.map((record) => ({
    ...record,
    contextText: record.context ? JSON.stringify(record.context, null, 2) : '',
  }))
})

function levelTagType(levelName: string): 'primary' | 'success' | 'warning' | 'danger' | 'info' {
  if (levelName === 'INFO') return 'primary'
  if (levelName === 'NOTICE') return 'success'
  if (levelName === 'WARNING') return 'warning'
  if (levelName === 'UNKNOWN') return 'info'
  return 'danger'
}

/** el-switch 的 change 回调参数类型偏宽,实际传入的就是 v-model 绑定的布尔值 */
function onAutoRefreshChange(value: string | number | boolean): void {
  if (refreshTimer !== null) {
    clearInterval(refreshTimer)
    refreshTimer = null
  }
  if (value === true) {
    refreshTimer = setInterval(() => void loadTail(), 5000)
  }
}

watch(level, () => void loadTail())
watch(limit, () => void loadTail())

onMounted(() => void loadFiles())
onUnmounted(() => {
  if (refreshTimer !== null) {
    clearInterval(refreshTimer)
    refreshTimer = null
  }
})
</script>

<template>
  <el-alert
    v-if="error"
    :title="error"
    type="error"
    :closable="false"
    show-icon
    class="mb-4"
  />

  <div class="flex flex-col gap-4 lg:flex-row">
    <!-- 左侧:按天分文件的列表 -->
    <aside class="lg:w-64 lg:shrink-0">
      <div v-loading="filesLoading" class="rounded-lg border border-gray-200 bg-white p-3">
        <div class="mb-2 flex items-center justify-between">
          <h2 class="text-sm font-semibold text-gray-700">日志文件</h2>
          <el-button size="small" text @click="loadFiles">刷新</el-button>
        </div>

        <p v-if="!filesLoading && files.length === 0" class="py-4 text-center text-xs text-gray-400">
          没有日志文件
        </p>

        <ul v-else class="space-y-1">
          <li
            v-for="file in files"
            :key="file.name"
            :class="[
              'cursor-pointer rounded px-2 py-1.5 text-xs transition',
              current?.name === file.name
                ? 'bg-blue-50 text-blue-700'
                : 'text-gray-600 hover:bg-gray-100',
            ]"
            @click="selectFile(file)"
          >
            <div class="flex items-center justify-between gap-2">
              <span class="truncate font-mono">{{ file.name }}</span>
              <el-tag
                :type="file.channel === 'error' ? 'danger' : 'info'"
                size="small"
                disable-transitions
              >
                {{ file.channel }}
              </el-tag>
            </div>
            <div class="mt-0.5 text-[11px] text-gray-400">
              {{ formatSize(file.size) }} · {{ formatDateTime(file.mtime) }}
            </div>
          </li>
        </ul>
      </div>
    </aside>

    <!-- 右侧:选中文件的末尾记录 -->
    <section class="min-w-0 flex-1">
      <div
        v-if="!current"
        class="rounded-lg border border-dashed border-gray-300 py-16 text-center text-sm text-gray-400"
      >
        从左侧选择一个日志文件开始查看
      </div>

      <div v-else v-loading="loading" class="rounded-lg border border-gray-200 bg-white">
        <div class="flex flex-wrap items-center gap-3 border-b border-gray-200 p-3">
          <span class="font-mono text-sm font-medium">{{ current.name }}</span>

          <el-select v-model="level" size="small" class="!w-32" placeholder="全部级别">
            <el-option label="全部级别" value="" />
            <el-option v-for="item in LOG_LEVELS" :key="item" :label="item" :value="item" />
          </el-select>

          <el-select v-model="limit" size="small" class="!w-28">
            <el-option :value="100" label="100 条" />
            <el-option :value="200" label="200 条" />
            <el-option :value="500" label="500 条" />
            <el-option :value="1000" label="1000 条" />
          </el-select>

          <label class="flex cursor-pointer items-center gap-1.5 text-xs text-gray-500">
            <el-switch v-model="autoRefresh" size="small" @change="onAutoRefreshChange" />
            每 5 秒刷新
          </label>

          <span v-if="result" class="ml-auto text-xs text-gray-400">
            返回 {{ result.lines.length }} 条 / 窗口内共 {{ result.total }} 条,最新在前
          </span>
        </div>

        <el-table v-if="rows.length > 0" :data="rows" size="small">
          <el-table-column type="expand">
            <template #default="scope">
              <pre class="max-h-72 overflow-auto whitespace-pre-wrap break-all bg-gray-50 p-3 font-mono text-xs leading-5 text-gray-700">{{ scope.row.contextText || '无上下文' }}</pre>
            </template>
          </el-table-column>
          <el-table-column label="时间" width="180">
            <template #default="scope">
              <span class="text-xs text-gray-500">{{ formatIsoDateTime(scope.row.ts) }}</span>
            </template>
          </el-table-column>
          <el-table-column label="级别" width="96">
            <template #default="scope">
              <el-tag :type="levelTagType(scope.row.level)" size="small" disable-transitions>
                {{ scope.row.level }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column label="消息" min-width="320">
            <template #default="scope">
              <div class="whitespace-pre-wrap break-all text-sm leading-5">{{ scope.row.message }}</div>
            </template>
          </el-table-column>
          <el-table-column label="上下文" width="80" align="center">
            <template #default="scope">
              <span v-if="scope.row.context" class="text-xs text-blue-600">有</span>
              <span v-else class="text-xs text-gray-300">—</span>
            </template>
          </el-table-column>
        </el-table>

        <el-empty
          v-else-if="result && rows.length === 0"
          description="该级别下没有匹配的记录"
          :image-size="60"
        />
      </div>
    </section>
  </div>
</template>
