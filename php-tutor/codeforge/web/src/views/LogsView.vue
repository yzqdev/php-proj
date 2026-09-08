<template>
  <div class="max-w-6xl mx-auto py-8 px-4">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">系统日志</h1>
      <el-button @click="handleRefresh" :loading="logStore.loading">
        <el-icon><Refresh /></el-icon>
        刷新
      </el-button>
    </div>

    <el-card>
      <el-table :data="logStore.entries" v-loading="logStore.loading" stripe size="small">
        <el-table-column label="时间" width="180">
          <template #default="{ row }">
            {{ formatTime(row.datetime) }}
          </template>
        </el-table-column>
        <el-table-column prop="level" label="级别" width="100">
          <template #default="{ row }">
            <el-tag :type="levelTagType(row.level)" size="small">
              {{ row.level }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="channel" label="频道" width="120" />
        <el-table-column prop="message" label="消息" />
      </el-table>

      <div class="mt-4 text-sm text-gray-400">
        共 {{ logStore.total }} 条日志
      </div>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import dayjs from 'dayjs'
import { Refresh } from '@element-plus/icons-vue'
import { useLogStore } from '@/stores/log'

const logStore = useLogStore()

function formatTime(datetime: string): string {
  if (!datetime) return ''
  return dayjs(datetime).format('YYYY-MM-DD HH:mm:ss')
}

function levelTagType(level: string): 'success' | 'warning' | 'danger' | 'info' {
  return level === 'INFO' ? 'info' : level === 'WARNING' ? 'warning' : level === 'ERROR' ? 'danger' : 'info'
}

onMounted(() => {
  logStore.fetchLogs()
})

function handleRefresh() {
  logStore.fetchLogs()
}
</script>
