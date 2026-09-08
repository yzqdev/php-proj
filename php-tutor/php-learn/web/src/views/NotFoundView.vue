<script setup lang="ts">
/**
 * ============================================================
 * 404 页面 — Element Plus 版
 * ============================================================
 */
import { useRouter } from 'vue-router'

const router = useRouter()
</script>

<template>
  <div class="not-found-page">
    <el-result
      icon="error"
      title="404"
      sub-title="页面不存在"
    >
      <template #sub-title>
        <p class="not-found-desc">
          您访问的页面不存在或已被移除
        </p>
      </template>
      <template #extra>
        <div class="not-found-actions">
          <el-button type="primary" @click="router.push('/')">
            <el-icon><Home /></el-icon>
            回到首页
          </el-button>
          <el-button @click="router.back()">
            <el-icon><ArrowLeft /></el-icon>
            返回上一页
          </el-button>
        </div>
      </template>
    </el-result>
  </div>
</template>

<style scoped>
.not-found-page {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 70vh;
  padding: 24px;
}

.not-found-desc {
  margin: 0;
  font-size: 15px;
  color: var(--el-text-color-secondary);
}

.not-found-actions {
  display: flex;
  gap: 12px;
  margin-top: 20px;
}
</style>