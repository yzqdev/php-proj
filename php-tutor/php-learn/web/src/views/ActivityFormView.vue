<script setup lang="ts">
/**
 * ============================================================
 * 活动新建视图 — Element Plus 版
 * ============================================================
 *
 * 路由：/activities/new
 */
import { useRouter } from 'vue-router'
import ActivityForm from '@/components/ActivityForm.vue'

const router = useRouter()

function onSuccess(): void {
  router.push('/activities')
}

function onCancel(): void {
  router.push('/activities')
}
</script>

<template>
  <div class="activity-form-page">
    <!-- 返回 -->
    <el-button
      text
      @click="onCancel"
      class="back-btn"
    >
      <el-icon><ArrowLeft /></el-icon>
      返回活动列表
    </el-button>

    <h1 class="form-title">创建活动</h1>

    <div class="form-wrap">
      <ActivityForm @success="onSuccess" @cancel="onCancel" />
    </div>
  </div>
</template>

<style scoped>
.activity-form-page {
  max-width: 720px;
  margin: 0 auto;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.back-btn {
  align-self: flex-start;
  color: var(--el-text-color-secondary);
  font-size: 14px;
  padding: 4px 8px;
  margin: 0 0 8px 0;
}

.back-btn:hover {
  color: var(--el-color-primary);
}

.form-title {
  margin: 0;
  font-size: 26px;
  font-weight: 700;
  color: var(--el-text-color-primary);
}

.form-wrap {
  background-color: var(--el-bg-color);
  border: 1px solid var(--el-border-color-lighter);
  border-radius: 12px;
  padding: 24px;
}
</style>