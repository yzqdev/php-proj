<script setup lang="ts">
/**
 * 分类徽章组件
 *
 * 用法：
 *   <CategoryBadge :category="article.category" />
 *
 * 每类分类对应不同颜色，让列表视觉区分清晰。
 */
import type { ArticleCategory } from '@/types/models'

const props = defineProps<{
  category: ArticleCategory
  /** 显示自定义文本，不传则用默认 */
  label?: string
}>()

const categoryMap: Record<ArticleCategory, { label: string; cls: string }> = {
  php: { label: 'PHP', cls: 'bg-primary-50 text-primary-600' },
  java: { label: 'Java', cls: 'bg-success-50 text-success-500' },
  db: { label: 'Database', cls: 'bg-warning-50 text-warning-500' },
  other: { label: '其他', cls: 'bg-surface-100 text-surface-700' },
}

const current = categoryMap[props.category] ?? categoryMap.other
</script>

<template>
  <span
    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
    :class="current.cls"
  >
    {{ label ?? current.label }}
  </span>
</template>
