/**
 * ============================================================
 * 活动表单 composable
 * ============================================================
 *
 * 后端校验规则：
 *   - title: 必填，最多 100 字符
 *   - start_time: 必填，格式 YYYY-MM-DD HH:MM:SS
 *   - end_time: 可选，若提供必须晚于 start_time
 *   - location / description: 可选
 */
import { reactive, ref } from 'vue'
import { activityApi } from '@/api/modules/activity'
import { BusinessError } from '@/api/request'
import { fromInputValue } from '@/utils/dateTime'

export interface UseActivityFormOptions {
  /** 提交成功后的回调 */
  onSuccess: () => void
}

export interface UseActivityFormResult {
  form: {
    title: string
    location: string
    startTimeInput: string  // HTML datetime-local 格式
    endTimeInput: string
    description: string
  }
  errors: Record<string, string[]>
  submitting: ReturnType<typeof ref<boolean>>
  validate: () => boolean
  submit: () => Promise<void>
  reset: () => void
}

const EMPTY_FORM = {
  title: '',
  location: '',
  startTimeInput: '',
  endTimeInput: '',
  description: '',
}

export function useActivityForm(options: UseActivityFormOptions): UseActivityFormResult {
  const { onSuccess } = options

  const form = reactive({ ...EMPTY_FORM })
  const errors = ref<Record<string, string[]>>({})
  const submitting = ref(false)

  function validate(): boolean {
    const errs: Record<string, string[]> = {}

    // title: 1-100
    const title = form.title.trim()
    if (!title) {
      errs.title = ['标题不能为空']
    } else if (title.length > 100) {
      errs.title = ['标题最多 100 个字符']
    }

    // startTime: 必填
    if (!form.startTimeInput) {
      errs.startTime = ['开始时间不能为空']
    }

    // endTime: 可选，若填必须晚于 start
    if (form.endTimeInput && form.startTimeInput && form.endTimeInput <= form.startTimeInput) {
      errs.endTime = ['结束时间必须晚于开始时间']
    }

    errors.value = errs
    return Object.keys(errs).length === 0
  }

  async function submit(): Promise<void> {
    if (!validate()) return

    submitting.value = true
    try {
      await activityApi.create({
        title: form.title.trim(),
        location: form.location.trim() || undefined,
        startTime: fromInputValue(form.startTimeInput),
        endTime: fromInputValue(form.endTimeInput) || undefined,
        description: form.description.trim() || undefined,
      })
      onSuccess()
    } catch (e) {
      if (e instanceof BusinessError && e.errors) {
        errors.value = e.errors
      }
    } finally {
      submitting.value = false
    }
  }

  function reset(): void {
    Object.assign(form, EMPTY_FORM)
    errors.value = {}
  }

  return { form, errors, submitting, validate, submit, reset }
}
