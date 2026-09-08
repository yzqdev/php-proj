/** 日志文件条目(对应 PHP 侧 LogService::list 的返回) */
export interface LogFileItem {
  /** 文件名,形如 app-2026-09-07.log;查看内容时作为 tail 接口的路径参数 */
  name: string
  /** 通道:app 记录常规应用日志,error 只记录错误及以上 */
  channel: 'app' | 'error'
  /** 记录日期,YYYY-MM-DD */
  date: string
  /** 字节数 */
  size: number
  /** 最后写入时间(Unix 秒) */
  mtime: number
}

/** 一条解析后的日志记录 */
export interface LogRecord {
  /** ISO 8601 时间戳,解析失败时为 null */
  ts: string | null
  channel: string | null
  /** Monolog 级别;无法识别时为 UNKNOWN */
  level: string
  /** 原始消息文本 */
  message: string
  /** 日志上下文,可能包含异常堆栈、绝对路径等不可信内容 */
  context: Record<string, unknown> | null
}

/** tail 接口的返回 */
export interface LogTailResult {
  file: string
  /** 回读窗口内的记录总数,不代表本次返回条数 */
  total: number
  limit: number
  /** 本次生效的级别过滤,null 表示未过滤 */
  level: string | null
  lines: LogRecord[]
}

/** 后端支持的级别枚举,与 LogController 的 OpenAPI 定义保持一致 */
export const LOG_LEVELS = [
  'DEBUG',
  'INFO',
  'NOTICE',
  'WARNING',
  'ERROR',
  'CRITICAL',
  'ALERT',
  'EMERGENCY',
] as const

export type LogLevel = (typeof LOG_LEVELS)[number]
