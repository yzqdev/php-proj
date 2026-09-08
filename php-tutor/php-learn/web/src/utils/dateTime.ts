/**
 * ============================================================
 * 日期时间工具
 * ============================================================
 *
 * 后端约定：'YYYY-MM-DD HH:MM:SS'（空格分隔 + 秒总是 :00）
 * HTML input[type="datetime-local"] 输出：'YYYY-MM-DDTHH:MM'（T 分隔 + 无秒）
 *
 * 本工具负责两者间的转换。
 *
 * 对应 Java：类似 Joda-Time / java.time.format.DateTimeFormatter 的简化版
 */

/** 后端格式正则 */
export const BACKEND_TIME_PATTERN = /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/

/** HTML datetime-local 输出格式正则 */
export const INPUT_TIME_PATTERN = /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/

/**
 * 后端格式 → HTML input value
 * 例：'2026-09-06 19:00:00' → '2026-09-06T19:00'
 *
 * @param value 后端时间字符串，可为空
 * @returns HTML input value（空字符串表示未设置）
 */
export function toInputValue(value: string | null | undefined): string {
  if (!value) return ''
  if (INPUT_TIME_PATTERN.test(value)) return value // 已是 input 格式
  if (BACKEND_TIME_PATTERN.test(value)) {
    // 'YYYY-MM-DD HH:MM:SS' → 'YYYY-MM-DDTHH:MM'（去秒 + 空格换 T）
    return value.slice(0, 16).replace(' ', 'T')
  }
  // 未知格式：原样返回（让浏览器容错）
  return value
}

/**
 * HTML input value → 后端格式
 * 例：'2026-09-06T19:00' → '2026-09-06 19:00:00'
 *
 * @param value HTML input value
 * @returns 后端时间字符串（空字符串表示未设置）
 */
export function fromInputValue(value: string): string {
  if (!value) return ''
  if (BACKEND_TIME_PATTERN.test(value)) return value // 已是后端格式
  if (INPUT_TIME_PATTERN.test(value)) {
    // 'YYYY-MM-DDTHH:MM' → 'YYYY-MM-DD HH:MM:00'（T 换空格 + 补秒）
    return value.replace('T', ' ') + ':00'
  }
  // 未知格式：原样返回
  return value
}

/**
 * 校验时间字符串是否符合后端格式
 */
export function isValidBackendTime(value: string | null | undefined): boolean {
  return !!value && BACKEND_TIME_PATTERN.test(value)
}

/**
 * 将后端时间格式转换为 Date 对象
 *
 * 注意：'YYYY-MM-DD HH:MM:SS' 不是标准 ISO8601，
 * 直接 new Date() 在部分浏览器解析失败，需要先转成 T 分隔。
 */
export function parseBackendTime(value: string | null | undefined): Date | null {
  if (!value) return null
  const normalized = value.replace(' ', 'T')
  const date = new Date(normalized)
  if (isNaN(date.getTime())) return null
  return date
}

/**
 * 比较两个后端时间字符串（返回 -1 / 0 / 1）
 *
 * 由于格式固定为 'YYYY-MM-DD HH:MM:SS'（零填充），
 * 字符串字典序比较就等于时间序比较，不需要解析成 Date。
 */
export function compareTime(a: string, b: string): number {
  if (a < b) return -1
  if (a > b) return 1
  return 0
}
