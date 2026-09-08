/** 字节数 → 人类可读大小 */
export function formatSize(bytes: number): string {
  const units = ['B', 'KB', 'MB', 'GB']
  let size = bytes
  let unit = 0
  while (size >= 1024 && unit < units.length - 1) {
    size /= 1024
    unit += 1
  }
  return `${size.toFixed(2)} ${units[unit]}`
}

/** Unix 秒 → 本地日期时间 */
export function formatDateTime(unixSeconds: number): string {
  return new Date(unixSeconds * 1000).toLocaleString('zh-CN', { hour12: false })
}

/** ISO 8601 时间戳(可能带时区偏移)→ 本地日期时间;解析失败时原样返回 */
export function formatIsoDateTime(iso: string | null): string {
  if (!iso) {
    return '-'
  }
  const date = new Date(iso)
  return Number.isNaN(date.getTime()) ? iso : date.toLocaleString('zh-CN', { hour12: false })
}

/** 复制文本到剪贴板;非安全上下文(如 http 局域网地址)时降级为 execCommand */
export async function copyText(text: string): Promise<boolean> {
  if (navigator.clipboard) {
    try {
      await navigator.clipboard.writeText(text)
      return true
    } catch {
      // 落入下面的降级方案
    }
  }
  const textarea = document.createElement('textarea')
  textarea.value = text
  textarea.style.position = 'fixed'
  textarea.style.opacity = '0'
  document.body.appendChild(textarea)
  textarea.select()
  const ok = document.execCommand('copy')
  textarea.remove()
  return ok
}
