/**
 * ============================================================
 * 日志查看 API（后端 /api/v1/logs）
 * ============================================================
 *
 * 对应后端 LogController：
 *   - GET /api/v1/logs           → 列出所有日志文件
 *   - GET /api/v1/logs/{date}    → 读取指定日期的日志内容
 */
import { get } from '@/api/request'

/** 日志文件摘要 */
export interface LogFileInfo {
  date: string
  fileName: string
  size: number
  sizeHuman: string
}

/** 日志文件列表响应 */
export interface LogListResponse {
  list: LogFileInfo[]
  total: number
}

/** 日志内容响应 */
export interface LogContentResponse {
  date: string
  fileName: string
  totalLines: number
  offset: number
  limit: number
  lines: string[]
}

/** 日志级别选项 */
export type LogLevel = '' | 'debug' | 'info' | 'notice' | 'warning' | 'error' | 'critical'

/** 获取日志文件列表 */
export function fetchLogList(): Promise<LogListResponse> {
  return get<LogListResponse>('/logs')
}

/** 获取指定日期的日志内容 */
export function fetchLogContent(
  date: string,
  params?: { offset?: number; limit?: number; level?: LogLevel },
): Promise<LogContentResponse> {
  const query = new URLSearchParams()
  if (params?.offset) query.set('offset', String(params.offset))
  if (params?.limit) query.set('limit', String(params.limit))
  if (params?.level) query.set('level', params.level)

  const qs = query.toString()
  return get<LogContentResponse>(`/logs/${date}${qs ? `?${qs}` : ''}`)
}
