import axios from 'axios'
import { ElMessage } from 'element-plus'
import type { ApiResponse } from '@/types/api'
import router from '@/router'

declare module 'axios' {
  export interface AxiosRequestConfig {
    /** true 时 401 不触发全局清凭证+跳登录（如主动登出请求） */
    skipAuthRedirect?: boolean
  }
}

const TOKEN_KEY = 'slim-lab-token'

const request = axios.create({
  baseURL: '/api',
  timeout: 30000,
  withCredentials: true,
})

// 请求拦截器：自动附加 Bearer Token
request.interceptors.request.use((config) => {
  const token = localStorage.getItem(TOKEN_KEY)
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

request.interceptors.response.use(
  (response) => {
    const data = response.data as ApiResponse
    if (data.code !== 0 && data.code !== undefined) {
      ElMessage.error(data.message || '请求失败')
      return Promise.reject(new Error(data.message))
    }
    return response
  },
  (error) => {
    // 401：凭证缺失/失效 —— 清本地登录态并跳登录页（记录来源路由，登录后跳回）
    if (error.response?.status === 401 && !error.config?.skipAuthRedirect) {
      localStorage.removeItem(TOKEN_KEY)
      const current = router.currentRoute.value
      if (current.path !== '/login' && current.path !== '/register') {
        ElMessage.error('登录状态已失效，请重新登录')
        router.push({ name: 'login', query: { redirect: current.fullPath } })
      }
      return Promise.reject(error)
    }

    const message = error.response?.data?.message || error.message || '网络错误'
    ElMessage.error(message)
    return Promise.reject(error)
  },
)

export default request
