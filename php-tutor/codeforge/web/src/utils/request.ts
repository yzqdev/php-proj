import axios from 'axios'
import { ElMessage } from 'element-plus'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL ?? '/api',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
  },
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status
    const message = error.response?.data?.message ?? '请求失败'

    if (status === 401) {
      ElMessage.error('未授权，请登录')
    } else if (status === 403) {
      ElMessage.error('权限不足')
    } else if (status === 404) {
      ElMessage.warning(message)
    } else if (status === 422) {
      ElMessage.warning(message)
    } else if (status >= 500) {
      ElMessage.error('服务器错误，请稍后重试')
    } else {
      ElMessage.error(message)
    }

    return Promise.reject(error)
  },
)

export default api
