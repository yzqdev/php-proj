<script setup lang="ts">
/**
 * ============================================================
 * 通用分页条组件 — Element Plus 版
 * ============================================================
 *
 * Props:
 *   - total: 总条数
 *   - page: 当前页码
 *   - pageSize: 每页条数
 *   - totalPage: 总页数
 *   - loading: 加载中（禁用所有按钮）
 *
 * Emits:
 *   - change: (newPage: number) => void
 */
import { computed, ref, watch } from 'vue'
import { pageWindow } from '@/utils/format'

const props = defineProps<{
  total: number
  page: number
  pageSize: number
  totalPage: number
  loading?: boolean
}>()

const emit = defineEmits<{
  (e: 'change', page: number): void
}>()

// 内部页码副本（el-pagination 的 v-model:current-page 必须绑定可写变量）
const currentPage = ref(props.page)

// props.page 变化时同步内部值
watch(
  () => props.page,
  (newVal) => {
    currentPage.value = newVal
  },
)

// 是否禁用
const disabled = computed(() => props.loading)
</script>

<template>
  <el-pagination
    v-model:current-page="currentPage"
    :total="total"
    :page-size="pageSize"
    :page-count="totalPage"
    :disabled="disabled"
    :background="true"
    layout="prev, pager, next, jumper, total"
    :default-page-sizes="[10, 20, 50]"
    @current-change="(p: number) => emit('change', p)"
  />
</template>