<script setup lang="ts">
/**
 * ============================================================
 * 空状态通用组件 — Element Plus 版
 * ============================================================
 *
 * Props:
 *   - title: 标题（默认"暂无数据"）
 *   - description: 描述（可选）
 *   - actionLabel: 操作按钮文字（可选，传了就显示按钮）
 *
 * Emits:
 *   - action: 用户点击操作按钮
 */
import { h } from 'vue'

defineProps<{
  title?: string
  description?: string
  actionLabel?: string
}>()

const emit = defineEmits<{
  (e: 'action'): void
}>()

// Element Plus 的空状态组件（el-empty）已内置图标和空状态样式
// 这里用 el-empty 包一层，保留 action 按钮的灵活性
</script>

<template>
  <el-empty
    :description="title ?? '暂无数据'"
    :image="undefined"
  >
    <template #description>
      <div class="empty-title">{{ title ?? '暂无数据' }}</div>
      <div v-if="description" class="empty-desc">{{ description }}</div>
    </template>

    <template v-if="actionLabel" #default>
      <el-button type="primary" @click="emit('action')">
        {{ actionLabel }}
      </el-button>
    </template>
  </el-empty>
</template>

<style scoped>
.empty-title {
  font-size: 16px;
  font-weight: 500;
  color: var(--el-text-color-primary);
  margin-bottom: 4px;
}

.empty-desc {
  font-size: 14px;
  color: var(--el-text-color-secondary);
}
</style>