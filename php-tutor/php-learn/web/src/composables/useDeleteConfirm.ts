/**
 * ============================================================
 * 删除二次确认 composable
 * ============================================================
 *
 * 使用方式：
 *   const { show, confirm, cancel, loading, visible, message } = useDeleteConfirm(async () => {
 *     await articleApi.remove(id)
 *     router.push('/articles')
 *   })
 *
 * 模板：
 *   <button @click="show()">删除</button>
 *   <DeleteConfirmDialog :visible="visible" :message="message" :loading="loading"
 *     @confirm="confirm" @cancel="cancel" />
 */
import { ref } from 'vue'

export interface UseDeleteConfirmResult {
  /** 弹窗是否可见 */
  visible: ReturnType<typeof ref<boolean>>
  /** 确认加载状态 */
  loading: ReturnType<typeof ref<boolean>>
  /** 当前显示的消息（可自定义） */
  message: ReturnType<typeof ref<string>>
  /** 显示弹窗（可传入自定义消息） */
  show: (customMessage?: string) => void
  /** 用户点击"确认" */
  confirm: () => Promise<void>
  /** 用户点击"取消" */
  cancel: () => void
}

export function useDeleteConfirm(
  onConfirm: () => Promise<unknown> | unknown,
  defaultMessage = '确认要删除吗？此操作不可撤销。',
): UseDeleteConfirmResult {
  const visible = ref(false)
  const loading = ref(false)
  const message = ref(defaultMessage)

  function show(customMessage?: string): void {
    if (customMessage) message.value = customMessage
    visible.value = true
  }

  async function confirm(): Promise<void> {
    loading.value = true
    try {
      await onConfirm()
      visible.value = false
    } catch (e) {
      // 错误已经由 useApiResource 或 request.ts 拦截器处理
      // 弹窗保持可见，用户可看到错误后重试或取消
      visible.value = false
    } finally {
      loading.value = false
    }
  }

  function cancel(): void {
    visible.value = false
  }

  return { visible, loading, message, show, confirm, cancel }
}
