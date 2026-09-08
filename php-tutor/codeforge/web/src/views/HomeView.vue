<template>
  <div class="max-w-6xl mx-auto py-8 px-4">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-800">欢迎使用 CodeForge</h1>
      <p class="text-gray-500 mt-2">代码托管与文件管理平台</p>
    </div>

    <el-row :gutter="20">
      <el-col :span="6">
        <el-card class="stat-card" shadow="hover">
          <div class="stat-value">{{ userTotal }}</div>
          <div class="stat-label">用户总数</div>
          <el-button type="primary" size="small" class="mt-4" @click="$router.push('/users')">
            管理用户
          </el-button>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card class="stat-card" shadow="hover">
          <div class="stat-value">{{ fileTotal }}</div>
          <div class="stat-label">文件总数</div>
          <el-button type="success" size="small" class="mt-4" @click="$router.push('/files')">
            管理文件
          </el-button>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card class="stat-card" shadow="hover">
          <div class="stat-value">{{ logTotal }}</div>
          <div class="stat-label">日志条数</div>
          <el-button type="info" size="small" class="mt-4" @click="$router.push('/logs')">
            查看日志
          </el-button>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card class="stat-card" shadow="hover">
          <div class="stat-value">v1.0</div>
          <div class="stat-label">系统版本</div>
          <div class="mt-4 text-sm text-gray-400">Vue 3 + PHP API</div>
        </el-card>
      </el-col>
    </el-row>

    <el-card class="mt-8" header="快速开始">
      <el-space direction="vertical" :size="12" class="w-full">
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
          <span>👥 管理用户</span>
          <el-button type="primary" size="small" @click="$router.push('/users')">前往</el-button>
        </div>
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
          <span>📁 上传与管理文件</span>
          <el-button type="success" size="small" @click="$router.push('/files')">前往</el-button>
        </div>
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
          <span>📋 查看系统日志</span>
          <el-button type="info" size="small" @click="$router.push('/logs')">前往</el-button>
        </div>
      </el-space>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useUserStore } from '@/stores/user'
import { useFileStore } from '@/stores/file'
import { useLogStore } from '@/stores/log'

const userStore = useUserStore()
const fileStore = useFileStore()
const logStore = useLogStore()

const userTotal = ref(0)
const fileTotal = ref(0)
const logTotal = ref(0)

onMounted(async () => {
  try {
    const userRes = await userStore.fetchUsers(1, 1)
    userTotal.value = userRes.total
  } catch {
    // ignore
  }
  try {
    const fileRes = await fileStore.fetchFiles(1)
    fileTotal.value = fileRes.length
  } catch {
    // ignore
  }
  try {
    await logStore.fetchLogs(undefined, 1)
    logTotal.value = logStore.total
  } catch {
    // ignore
  }
})
</script>

<style scoped>
.stat-card {
  text-align: center;
  padding: 1rem;
}

.stat-value {
  font-size: 2.5rem;
  font-weight: bold;
  color: #409eff;
}

.stat-label {
  color: #606266;
  margin-top: 0.5rem;
}
</style>
