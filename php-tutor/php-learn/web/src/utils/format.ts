/**
 * ============================================================
 * 工具函数
 * ============================================================
 *
 * 关键约束（与后端一致）：
 *   - 后端返回时间字符串格式：'YYYY-MM-DD HH:MM:SS'
 *   - 不用 ISO8601（前端好显示，避免时区混淆）
 *
 * 对应 Java：类似 StringUtils / DateUtils
 */
import type { FieldErrors } from '@/types/api'

/** 时间字符串格式（后端约定） */
export const TIME_PATTERN = /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/

/**
 * 格式化时间字符串（'YYYY-MM-DD HH:MM:SS' → 'YYYY-MM-DD HH:MM'）
 * 只显示到分钟（后端秒总是 :00）
 */
export function formatDate(input: string | null | undefined): string {
  if (!input) return '-'
  if (!TIME_PATTERN.test(input)) return input
  // 去掉秒部分（后 3 字符 :SS）
  return input.slice(0, 16)
}

/**
 * 完整格式化（保留秒）
 */
export function formatDateTime(input: string | null | undefined): string {
  if (!input) return '-'
  return input
}

/**
 * 相对时间（如 "3 分钟前"）
 * 简化实现，不引入 dayjs
 */
export function relativeTime(input: string | null | undefined): string {
  if (!input) return '-'
  const date = new Date(input.replace(' ', 'T'))
  if (isNaN(date.getTime())) return input
  const diff = (Date.now() - date.getTime()) / 1000
  if (diff < 60) return '刚刚'
  if (diff < 3600) return `${Math.floor(diff / 60)} 分钟前`
  if (diff < 86400) return `${Math.floor(diff / 3600)} 小时前`
  if (diff < 604800) return `${Math.floor(diff / 86400)} 天前`
  return formatDate(input)
}

/**
 * 合并两个字段错误对象（用于表单多字段合并）
 */
export function mergeFieldErrors(a: FieldErrors | undefined, b: FieldErrors | undefined): FieldErrors {
  const result: FieldErrors = { ...(a ?? {}) }
  if (b) {
    for (const [key, msgs] of Object.entries(b)) {
      result[key] = [...(result[key] ?? []), ...msgs]
    }
  }
  return result
}

/**
 * 判断字段是否有错误
 */
export function hasError(errors: FieldErrors | undefined, field: string): boolean {
  return !!errors && Array.isArray(errors[field]) && errors[field].length > 0
}

/**
 * 取字段第一条错误（用于表单控件下方提示）
 */
export function firstError(errors: FieldErrors | undefined, field: string): string {
  return errors?.[field]?.[0] ?? ''
}

/**
 * 计算分页页码列表（带省略号）
 * 例：totalPage=15, current=8, span=2 → [1, 2, '...', 6, 7, 8, 9, 10, '...', 14, 15]
 *
 * 简化版：只显示 [currentSpan] 附近的窗口
 */
export function pageWindow(totalPage: number, current: number, span = 2): (number | '...')[] {
  if (totalPage <= span * 2 + 1) {
    return Array.from({ length: totalPage }, (_, i) => i + 1)
  }

  const result: (number | '...')[] = []

  // 起始 1
  result.push(1)
  if (current - span > 2) result.push('...')

  // 中间窗口
  const start = Math.max(2, current - span)
  const end = Math.min(totalPage - 1, current + span)
  for (let i = start; i <= end; i++) result.push(i)

  // 末尾
  if (current + span < totalPage - 1) result.push('...')
  result.push(totalPage)

  return result
}
