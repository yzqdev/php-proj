<script setup lang="ts">
/**
 * ============================================================
 * 通用 Loading 组件 — Element Plus 版
 * ============================================================
 *
 * 用法：
 *   <PageLoader v-if="loading" :text="'加载中...'" />
 *   <PageLoader :rows="5" />  // 显示骨架屏（骨架条数）
 */
defineProps<{
  /** 显示的文字（不传默认"加载中..."） */
  text?: string
  /** 骨架条数（>=1 时显示骨架屏，否则只显示文字） */
  rows?: number
  /** 高度（CSS 值，默认 auto） */
  height?: string
}>()
</script>

<template>
  <div
    class="page-loader"
    :style="{ height: height ?? 'auto' }"
  >
    <!-- 骨架屏模式 -->
    <template v-if="rows && rows > 0">
      <el-skeleton
        :rows="rows"
        animated
      />
    </template>

    <!-- 文字 + 图标模式 -->
    <template v-else>
      <el-icon class="loader-icon" :size="32" color="var(--el-color-primary)">
        <Loading />
      </el-icon>
      <p class="loader-text">{{ text ?? '加载中...' }}</p>
    </template>
  </div>
</template>

<style scoped>
.page-loader {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 24px;
}

.loader-text {
  margin: 0;
  font-size: 14px;
  color: var(--el-text-color-secondary);
}
</style>