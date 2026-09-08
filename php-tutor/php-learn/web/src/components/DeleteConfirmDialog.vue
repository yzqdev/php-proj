<script setup lang="ts">
/**
 * ============================================================
 * 通用二次确认弹窗 — Element Plus 版
 * ============================================================
 *
 * 用法：
 *   <DeleteConfirmDialog
 *     v-model="show"
 *     :message="deleteMsg"
 *     :loading="loading"
 *     @confirm="onDelete"
 *     @cancel="onCancel"
 *   />
 *
 * Props:
 *   - modelValue: 弹窗是否可见（v-model）
 *   - message: 弹窗正文
 *   - title: 弹窗标题（默认"确认操作"）
 *   - confirmText: 确认按钮文字（默认"确认删除"）
 *   - loading: 加载中（禁用确认按钮）
 *   - danger: 是否是危险操作（红色按钮，默认 true）
 *
 * Emits:
 *   - update:modelValue: 更新 visible
 *   - confirm: 用户点击确认
 *   - cancel: 用户点击取消或关闭
 */
import { computed } from 'vue'

const props = withDefaults(
  defineProps<{
    modelValue: boolean
    message?: string
    title?: string
    confirmText?: string
    loading?: boolean
    danger?: boolean
  }>(),
  {
    message: '确认要删除吗？此操作不可撤销。',
    title: '确认操作',
    confirmText: '确认删除',
    loading: false,
    danger: true,
  },
)

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'confirm'): void
  (e: 'cancel'): void
}>()

function close(): void {
  if (props.loading) return
  emit('update:modelValue', false)
  emit('cancel')
}

function confirm(): void {
  if (props.loading) return
  emit('confirm')
}

// 按钮类型：危险操作用 danger，普通用 primary
const confirmType = computed(() => props.danger ? 'danger' : 'primary')
</script>

<template>
  <el-dialog
    v-model="props.modelValue"
    :title="title"
    :close-on-click-modal="false"
    :close-on-press-escape="false"
    width="440px"
    @close="close"
  >
    <!-- 内容：图标 + 消息 -->
    <div class="dialog-content">
      <el-alert
        v-if="props.danger"
        :title="props.message"
        type="error"
        :closable="false"
        show-icon
      />
      <el-alert
        v-else
        :title="props.message"
        type="warning"
        :closable="false"
        show-icon
      />
    </div>

    <!-- 底部按钮 -->
    <template #footer>
      <el-button
        @click="close"
        :disabled="props.loading"
      >
        取消
      </el-button>
      <el-button
        type="danger"
        @click="confirm"
        :loading="props.loading"
      >
        {{ props.confirmText }}
      </el-button>
    </template>
  </el-dialog>
</template>

<style scoped>
.dialog-content {
  padding: 4px 0;
}
</style>