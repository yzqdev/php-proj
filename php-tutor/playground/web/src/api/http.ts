import axios, { AxiosError } from 'axios'
import type { ApiEnvelope } from '@/types/image'

/** 从 API 响应归一化出的业务错误,视图层用它展示用户可读的信息 */
export class ApiError extends Error {
  constructor(
    message: string,
    readonly status: number,
  ) {
    super(message)
    this.name = 'ApiError'
  }
}

/**
 * 统一的 Axios 实例:baseURL / 超时 / CSRF 安全头 / 错误归一化
 * 组件里不要直接用 axios,一律经由 src/api/ 下的模块调用。
 */
export const http = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  timeout: 30_000,
  // 后端 CSRF 中间件要求写请求携带该自定义头(表单类跨站请求无法携带自定义头)
  headers: { 'X-Requested-With': 'XMLHttpRequest' },
})

http.interceptors.response.use(
  (response) => {
    const body = response.data as ApiEnvelope
    // 后端正常时不会在 2xx 里返回 success:false,这里兜底归一化
    if (body && typeof body.success === 'boolean' && !body.success) {
      throw new ApiError(body.message ?? '请求失败', response.status)
    }
    return response
  },
  (error: AxiosError<ApiEnvelope>) => {
    const status = error.response?.status ?? 0
    const fallback = status === 0 ? '网络错误,无法连接到 API 服务器' : `请求失败(HTTP ${status})`
    throw new ApiError(error.response?.data?.message ?? fallback, status)
  },
)
