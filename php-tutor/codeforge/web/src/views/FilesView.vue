<template>
  <div class="max-w-6xl mx-auto py-8 px-4">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">文件管理</h1>
    </div>

    <el-card class="mb-6">
      <el-upload
        action=""
        :auto-upload="false"
        :show-file-list="false"
        @change="handleUpload"
        :disabled="uploading"
      >
        <el-button type="primary" :loading="uploading">
          <el-icon><Upload /></el-icon>
          {{ uploading ? '上传中...' : '上传文件' }}
        </el-button>
      </el-upload>
      <div class="mt-2 text-sm text-gray-400">支持图片、文档、压缩包等，最大 8MB</div>
    </el-card>

    <el-card>
      <el-table :data="fileStore.files" v-loading="fileStore.loading" stripe>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="original_name" label="文件名">
          <template #default="{ row }">
            <div class="flex items-center gap-2">
              <el-icon><Document /></el-icon>
              <span>{{ row.original_name }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="mime" label="类型" width="150" />
        <el-table-column prop="size" label="大小" width="100">
          <template #default="{ row }">
            {{ formatSize(row.size) }}
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="上传时间" width="180" />
        <el-table-column label="操作" width="200">
          <template #default="{ row }">
            <el-button
              size="small"
              type="primary"
              link
              @click="handleDownload(row)"
            >
              下载
            </el-button>
            <el-button
              size="small"
              type="danger"
              link
              @click="handleDelete(row)"
            >
              删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Upload, Document } from '@element-plus/icons-vue'
import { useFileStore } from '@/stores/file'
import { getDownloadUrl } from '@/api/file'

const fileStore = useFileStore()
const uploading = ref(false)

onMounted(() => {
  fileStore.fetchFiles()
})

function formatSize(bytes: number): string {
  const units = ['B', 'KB', 'MB', 'GB']
  let value = bytes
  let unitIndex = 0
  while (value >= 1024 && unitIndex < units.length - 1) {
    value /= 1024
    unitIndex++
  }
  return `${value.toFixed(1)} ${units[unitIndex]}`
}

async function handleUpload(uploadFile: { raw: File }) {
  if (!uploadFile.raw) return
  uploading.value = true
  try {
    await fileStore.uploadFile(uploadFile.raw)
    ElMessage.success('上传成功')
    await fileStore.fetchFiles()
  } finally {
    uploading.value = false
  }
}

function handleDownload(file: { id: number; original_name: string }) {
  window.open(getDownloadUrl(file.id), '_blank')
}

async function handleDelete(file: { id: number; original_name: string }) {
  await ElMessageBox.confirm(`确定要删除文件 "${file.original_name}" 吗？`, '确认删除', {
    type: 'warning',
  })
  await fileStore.deleteFile(file.id)
  ElMessage.success('删除成功')
  await fileStore.fetchFiles()
}
</script>
