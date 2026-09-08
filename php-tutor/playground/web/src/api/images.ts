import { http } from './http'
import type { ApiEnvelope, ImageItem } from '@/types/image'

/** 图片外链/下载地址要拼上 API 基础地址,因为前后端不同源 */
export function resolveUrl(path: string): string {
  return `${import.meta.env.VITE_API_BASE_URL}${path}`
}

export const imageApi = {
  /** 图片列表(按上传时间倒序) */
  async list(): Promise<ImageItem[]> {
    const res = await http.get<ApiEnvelope<ImageItem[]>>('/api/images')
    return res.data.data ?? []
  },

  /** 上传单张图片 */
  async upload(file: File): Promise<ImageItem> {
    const form = new FormData()
    form.append('file', file)
    const res = await http.post<ApiEnvelope<ImageItem>>('/api/images', form)
    if (!res.data.data) {
      throw new Error('上传响应缺少数据')
    }
    return res.data.data
  },

  /** 按存储名删除图片 */
  async remove(name: string): Promise<void> {
    await http.delete(`/api/images/${encodeURIComponent(name)}`)
  },
}
