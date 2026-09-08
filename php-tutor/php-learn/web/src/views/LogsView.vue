<script setup lang="ts">
/**
 * ============================================================
 * 系统日志查看页面
 * ============================================================
 *
 * 功能：
 *   - 左侧：日志文件列表（按日期倒序）
 *   - 右侧：选中日期的日志内容（带级别过滤 + 分页）
 *
 * 使用 Element Plus 组件 + TailwindCSS 工具类。
 * 日志内容用 <pre> 等宽字体展示，保持原始格式。
 */
import { ref, onMounted } from 'vue'
import {
  fetchLogList,
  fetchLogContent,
  type LogFileInfo,
  type LogContentResponse,
  type LogLevel,
} from '@/api/modules/log'

// ---- 列表状态 ----
const logFiles = ref<LogFileInfo[]>([])
const listLoading = ref(false)

// ---- 内容状态 ----
const selectedDate = ref<string>('')
const logContent = ref<LogContentResponse | null>(null)
const contentLoading = ref(false)

// ---- 过滤与分页 ----
const levelFilter = ref<LogLevel>('')
const pageOffset = ref(0)
const pageSize = ref(500)

// ---- 级别选项 ----
const levelOptions: { value: LogLevel; label: string }[] = [
  { value: '', label: '全部级别' },
  { value: 'debug', label: 'DEBUG' },
  { value: 'info', label: 'INFO' },
  { value: 'notice', label: 'NOTICE' },
  { value: 'warning', label: 'WARNING' },
  { value: 'error', label: 'ERROR' },
  { value: 'critical', label: 'CRITICAL' },
]

/** 加载日志文件列表 */
async function loadLogList(): Promise<void> {
  listLoading.value = true
  try {
    const res = await fetchLogList()
    logFiles.value = res.list
    // 默认选中最新日志
    if (res.list.length > 0 && !selectedDate.value) {
      await selectDate(res.list[0].date)
    }
  } catch {
    // 401/403 由拦截器处理
  } finally {
    listLoading.value = false
  }
}

/** 加载指定日期的日志内容 */
async function loadLogContent(): Promise<void> {
  if (!selectedDate.value) return
  contentLoading.value = true
  try {
    logContent.value = await fetchLogContent(selectedDate.value, {
      offset: pageOffset.value,
      limit: pageSize.value,
      level: levelFilter.value || undefined,
    })
  } catch {
    logContent.value = null
  } finally {
    contentLoading.value = false
  }
}

/** 选择日期 */
async function selectDate(date: string): Promise<void> {
  selectedDate.value = date
  pageOffset.value = 0
  await loadLogContent()
}

/** 级别变更 */
async function onLevelChange(): Promise<void> {
  pageOffset.value = 0
  await loadLogContent()
}

/** 上一页 */
async function prevPage(): Promise<void> {
  pageOffset.value = Math.max(0, pageOffset.value - pageSize.value)
  await loadLogContent()
}

/** 下一页 */
async function nextPage(): Promise<void> {
  if (!logContent.value) return
  pageOffset.value += pageSize.value
  await loadLogContent()
}

/** 格式化日期显示 */
function formatDate(dateStr: string): string {
  const d = new Date(dateStr + 'T00:00:00')
  const weekdays = ['周日', '周一', '周二', '周三', '周四', '周五', '周六']
  return `${dateStr} ${weekdays[d.getDay()]}`
}

/** 判断是否为今天 */
function isToday(dateStr: string): boolean {
  return dateStr === new Date().toISOString().slice(0, 10)
}

onMounted(() => {
  loadLogList()
})
</script>

<template>
  <div class="log-page">
    <!-- 页面标题 -->
    <div class="page-header">
      <h1 class="page-title">📋 系统日志</h1>
      <p class="page-desc">查看后端应用日志，支持按日期和级别筛选</p>
    </div>

    <div class="log-layout">
      <!-- ===== 左侧：日志文件列表 ===== -->
      <div class="log-sidebar">
        <div class="sidebar-header">
          <span class="sidebar-title">日志文件</span>
          <el-button text size="small" @click="loadLogList" :loading="listLoading">
            <el-icon><Refresh /></el-icon>
          </el-button>
        </div>

        <div v-loading="listLoading" class="file-list">
          <div
            v-for="file in logFiles"
            :key="file.date"
            class="file-item"
            :class="{ active: selectedDate === file.date }"
            @click="selectDate(file.date)"
          >
            <div class="file-date">
              <el-tag v-if="isToday(file.date)" type="success" size="small" effect="dark" class="today-tag">
                今天
              </el-tag>
              <span>{{ formatDate(file.date) }}</span>
            </div>
            <span class="file-size">{{ file.sizeHuman }}</span>
          </div>

          <el-empty v-if="!listLoading && logFiles.length === 0" description="暂无日志文件" :image-size="60" />
        </div>
      </div>

      <!-- ===== 右侧：日志内容 ===== -->
      <div class="log-content">
        <!-- 工具栏 -->
        <div class="content-toolbar" v-if="selectedDate">
          <div class="toolbar-left">
            <el-tag type="info" effect="plain" size="large">
              {{ selectedDate }}
            </el-tag>
            <span v-if="logContent" class="line-count">
              共 {{ logContent.totalLines }} 行
            </span>
          </div>
          <div class="toolbar-right">
            <el-select
              v-model="levelFilter"
              placeholder="全部级别"
              size="default"
              style="width: 130px"
              @change="onLevelChange"
            >
              <el-option
                v-for="opt in levelOptions"
                :key="opt.value"
                :label="opt.label"
                :value="opt.value"
              />
            </el-select>
            <el-button
              :disabled="pageOffset <= 0"
              @click="prevPage"
              size="default"
            >
              上一页
            </el-button>
            <el-button
              :disabled="!logContent || pageOffset + pageSize >= logContent.totalLines"
              @click="nextPage"
              size="default"
            >
              下一页
            </el-button>
          </div>
        </div>

        <!-- 日志内容区域 -->
        <div v-loading="contentLoading" class="log-output">
          <template v-if="logContent">
            <div v-if="logContent.lines.length === 0" class="empty-log">
              <el-empty description="该日期暂无日志记录" :image-size="60" />
            </div>
            <pre v-else class="log-pre"><code v-for="(line, idx) in logContent.lines" :key="idx" class="log-line">{{ line }}</code></pre>
          </template>
          <div v-else-if="!contentLoading" class="empty-state">
            <el-empty description="请选择左侧的日志文件" :image-size="80" />
          </div>
        </div>

        <!-- 分页信息 -->
        <div v-if="logContent && logContent.totalLines > 0" class="content-footer">
          <span class="page-info">
            显示第 {{ pageOffset + 1 }}-{{ Math.min(pageOffset + pageSize, logContent.totalLines) }} 行
            / 共 {{ logContent.totalLines }} 行
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* ============================================================
 * 页面布局
 * ============================================================ */
.log-page {
  max-width: 1400px;
  margin: 0 auto;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.page-header {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.page-title {
  margin: 0;
  font-size: 24px;
  font-weight: 700;
  color: var(--el-text-color-primary);
}

.page-desc {
  margin: 0;
  font-size: 14px;
  color: var(--el-text-color-secondary);
}

/* ============================================================
 * 主布局（左sidebar + 右content）
 * ============================================================ */
.log-layout {
  display: flex;
  gap: 16px;
  min-height: 600px;
}

/* ---- 左侧 sidebar ---- */
.log-sidebar {
  width: 280px;
  flex-shrink: 0;
  border: 1px solid var(--el-border-color-lighter);
  border-radius: 8px;
  background: var(--el-bg-color);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.sidebar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  border-bottom: 1px solid var(--el-border-color-lighter);
  background: var(--el-fill-color-blank);
}

.sidebar-title {
  font-size: 15px;
  font-weight: 600;
  color: var(--el-text-color-primary);
}

.file-list {
  flex: 1;
  overflow-y: auto;
}

.file-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 16px;
  cursor: pointer;
  transition: background-color 0.15s;
  border-bottom: 1px solid var(--el-border-color-extra-light);
}

.file-item:hover {
  background: var(--el-fill-color-light);
}

.file-item.active {
  background: var(--el-color-primary-light-9);
  border-left: 3px solid var(--el-color-primary);
  padding-left: 13px;
}

.file-date {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: var(--el-text-color-regular);
}

.today-tag {
  font-size: 11px;
}

.file-size {
  font-size: 12px;
  color: var(--el-text-color-secondary);
  font-family: monospace;
}

/* ---- 右侧 content ---- */
.log-content {
  flex: 1;
  border: 1px solid var(--el-border-color-lighter);
  border-radius: 8px;
  background: var(--el-bg-color);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.content-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 16px;
  border-bottom: 1px solid var(--el-border-color-lighter);
  background: var(--el-fill-color-blank);
  flex-wrap: wrap;
  gap: 8px;
}

.toolbar-left {
  display: flex;
  align-items: center;
  gap: 10px;
}

.line-count {
  font-size: 13px;
  color: var(--el-text-color-secondary);
}

.toolbar-right {
  display: flex;
  align-items: center;
  gap: 8px;
}

/* ---- 日志输出区域 ---- */
.log-output {
  flex: 1;
  overflow: auto;
  background: #1e1e2e;
  padding: 0;
}

.log-pre {
  margin: 0;
  padding: 12px 16px;
  font-family: 'Cascadia Code', 'Fira Code', 'JetBrains Mono', Consolas, monospace;
  font-size: 12.5px;
  line-height: 1.6;
  color: #cdd6f4;
  white-space: pre-wrap;
  word-break: break-all;
}

.log-line {
  display: block;
}

.log-line:hover {
  background: rgba(255, 255, 255, 0.04);
}

.empty-log {
  padding: 40px;
  text-align: center;
}

.empty-state {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  min-height: 400px;
}

/* ---- 分页信息 ---- */
.content-footer {
  padding: 8px 16px;
  border-top: 1px solid var(--el-border-color-lighter);
  background: var(--el-fill-color-blank);
}

.page-info {
  font-size: 12px;
  color: var(--el-text-color-secondary);
}

/* ============================================================
 * 响应式适配
 * ============================================================ */
@media (max-width: 768px) {
  .log-layout {
    flex-direction: column;
  }

  .log-sidebar {
    width: 100%;
    max-height: 200px;
  }

  .content-toolbar {
    flex-direction: column;
    align-items: flex-start;
  }

  .log-page {
    padding: 16px 12px;
  }
}
</style>
