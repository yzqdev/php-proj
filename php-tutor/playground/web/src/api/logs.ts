import { http } from './http'
import type { ApiEnvelope } from '@/types/image'
import type { LogFileItem, LogTailResult } from '@/types/log'

/** 日志文件列表与内容查看;后端只允许本机来源访问,非本机会得到 403 */
export const logApi = {
  async list(): Promise<LogFileItem[]> {
    const res = await http.get<ApiEnvelope<LogFileItem[]>>('/api/logs')
    return res.data.data ?? []
  },

  async tail(
    name: string,
    options?: { limit?: number; level?: string | null },
  ): Promise<LogTailResult> {
    const res = await http.get<ApiEnvelope<LogTailResult>>(
      `/api/logs/${encodeURIComponent(name)}`,
      { params: { limit: options?.limit, level: options?.level || undefined } },
    )
    if (!res.data.data) {
      throw new Error('日志响应缺少数据')
    }
    return res.data.data
  },
}
