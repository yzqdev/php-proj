/**
 * ============================================================
 * Axios 请求封装（项目核心，所有 API 调用都要走这里）
 * ============================================================
 *
 * 设计目标：
 *   1. 拆壳：拦截器自动把 {code, message, data} 拆成只返回 data
 *   2. 自动鉴权：请求头注入 Bearer Token（从 localStorage 读）
 *   3. 401 自动 refresh：Access Token 过期时自动换新并重试
 *   4. 422 抛字段错误：让表单能绑定 errors 字段
 *   5. 并发 refresh 去重：多个请求同时 401 时只发一次 refresh
 *   6. 类型安全：get<T>/post<T>/put<T>/del<T> 泛型 helper
 *
 * 对应 Java / Spring Boot：
 *   - 类似 RestTemplate 的拦截器链
 *   - 类似 OkHttpClient 的 Interceptor
 *
 * 加载状态由调用方管（不在拦截器里做）：
 *   - 用 Util/composables/useApiResource.ts 的 loading ref
 *   - 或用 async 直接 await + try/catch
 *
 * 关键约束（与用户要求一致）：
 *   - baseURL 从环境变量取
 *   - 无 Authorization 头时后端返回 40101，前端跳登录
 *   - Token 无效时后端返回 40102，前端尝试 refresh
 *   - Refresh 失败或过期时清空所有 token 并跳登录
 *
 * PHP 对应概念：
 *   - 后端 App\Helpers\ApiResponse 输出的 {code, message, data}
 *   - 后端 App\Middleware\ApiAuthenticate 解析 Authorization: Bearer xxx
 *   - 后端 App\Services\TokenService::refresh() 轮转令牌
 */
import axios, { AxiosError, type AxiosInstance, type AxiosRequestConfig, type InternalAxiosRequestConfig } from 'axios'

// ============================================================
// 类型：业务异常（拦截器抛出，view 层用）
// ============================================================

/** 字段级错误映射 */
export type FieldErrors = Record<string, string[]>

/**
 * 业务异常
 *
 * view 层用：
 *   try {
 *     const data = await articleApi.create(...)
 *   } catch (e) {
 *     if (e instanceof BusinessError) {
 *       form.errors = e.errors  // 绑定到表单字段
 *     }
 *   }
 */
export class BusinessError extends Error {
  constructor(
    public readonly code: number,
    message: string,
    public readonly httpStatus: number,
    public readonly errors?: FieldErrors,
    public readonly original?: AxiosError,
  ) {
    super(message)
    this.name = 'BusinessError'
  }
}

// ============================================================
// 常量与存储 key
// ============================================================

/** 业务 code 常量（与后端保持一致） */
export const CODE = {
  OK: 0,
  UNAUTHORIZED: 40101,
  INVALID_TOKEN: 40102,
  USER_NOT_FOUND: 40103,
  FORBIDDEN: 40301,
  NOT_FOUND: 40401,
  VALIDATION: 42201,
  INTERNAL: 50000,
} as const

/** localStorage key：Access Token（短期，JWT） */
export const ACCESS_TOKEN_KEY = 'php-learn.accessToken'
/** localStorage key：Refresh Token（长期，DB 存储） */
export const REFRESH_TOKEN_KEY = 'php-learn.refreshToken'
/** localStorage key：记住密码（登录名 + 密码，勾选后持久化） */
export const REMEMBER_ME_KEY = 'php-learn.rememberMe'

/** 请求超时（毫秒） */
const REQUEST_TIMEOUT = 15000

// ============================================================
// Axios 实例
// ============================================================

/** 后端返回的 envelope 类型（拦截器已解包，但这里类型上仍保留 envelope 用于错误分支） */
interface Envelope<T = unknown> {
  code: number
  message: string
  data: T | null
  errors?: FieldErrors
}

/** Axios 实例（baseURL 从 VITE_API_BASE_URL 读） */
export const request: AxiosInstance = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL ?? '/api/v1',
  timeout: REQUEST_TIMEOUT,
  headers: {
    'Content-Type': 'application/json',
  },
})

// ============================================================
// 请求拦截器：注入 Bearer Token
// ============================================================

request.interceptors.request.use((config: InternalAxiosRequestConfig) => {
  const token = localStorage.getItem(ACCESS_TOKEN_KEY)
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// ============================================================
// Refresh 并发去重：多个 401 同时发生时只发一次 refresh
// ============================================================

/** 标记已触发过 refresh 的请求，防止 refresh 失败后又重试 → 无限循环 */
type RequestConfig = AxiosRequestConfig & {
  _retry?: boolean
  _skipRefresh?: boolean
}

/** 已发起的 refresh 请求（Promise），等待者共享同一个 Promise */
let refreshPromise: Promise<string> | null = null

/**
 * Token 刷新回调（由 auth store 注册）
 *
 * 为什么需要这个？
 *   performRefresh() 更新 localStorage 后，auth store 的 accessToken / refreshToken ref
 *   不会自动同步，导致 isLoggedIn 等 computed 拿到过期值，路由守卫误判为"未登录"。
 *   通过回调让 store 在 refresh 成功后立即更新内部 ref，消除这个时间窗口。
 *
 * 为什么不直接 import useAuthStore？
 *   auth.ts 已经 import 了 request.ts 的常量，再反向 import 会产生循环依赖。
 *   用注册回调模式解耦两个模块。
 */
let _onTokensRefreshed: ((accessToken: string, refreshToken: string) => void) | null = null

/** auth store 在初始化时调用此函数注册回调 */
export function registerTokenRefreshCallback(cb: (accessToken: string, refreshToken: string) => void): void {
  _onTokensRefreshed = cb
}

// ============================================================
// 内部：调用 /auth/refresh 换新 Access Token
// 直接读 localStorage 的 refreshToken，不经过 request 拦截器
// ============================================================
async function performRefresh(): Promise<string> {
  const refreshToken = localStorage.getItem(REFRESH_TOKEN_KEY)
  if (!refreshToken) {
    throw new Error('Refresh token 缺失')
  }

  // 用裸 axios（不走本实例的 response 拦截器），避免无限递归
  const resp = await axios.post<Envelope<{ accessToken: string; refreshToken: string; expiresIn: number }>>(
    `${request.defaults.baseURL}/auth/refresh`,
    { refreshToken },
    { timeout: REQUEST_TIMEOUT, headers: { 'Content-Type': 'application/json' } },
  )

  const env = resp.data
  if (env.code !== 0) {
    throw new Error(`刷新失败: ${env.message}`)
  }

  const newAccess = env.data!.accessToken
  const newRefresh = env.data!.refreshToken

  // 存新 token（旧 refresh 已被服务端撤销）
  localStorage.setItem(ACCESS_TOKEN_KEY, newAccess)
  localStorage.setItem(REFRESH_TOKEN_KEY, newRefresh)

  // 通知 auth store 同步内部 ref（避免 isLoggedIn 等 computed 拿到过期值）
  _onTokensRefreshed?.(newAccess, newRefresh)

  return newAccess
}

/**
 * 触发 refresh（并发安全：多个调用者共享同一个 Promise）
 *
 * 关键点：
 *   - 第一次调用创建 Promise 并启动 refresh
 *   - 后续并发调用直接返回同一个 Promise（不重复发请求）
 *   - Promise settle 后清空，下次 401 会重新走完整流程
 */
function ensureRefresh(): Promise<string> {
  if (!refreshPromise) {
    refreshPromise = performRefresh().finally(() => {
      refreshPromise = null
    })
  }
  return refreshPromise
}

// ============================================================
// 响应拦截器：拆壳 + 401 自动 refresh + 错误分发
// ============================================================

request.interceptors.response.use(
  async (response) => {
    const env = response.data as Envelope
    if (env.code === CODE.OK) {
      // 成功：直接返回 data（调用方拿到的是 T，不再是 envelope）
      // 注意：返回 config 会导致调用方拿到 undefined，进而触发
      // "Cannot read properties of undefined" 类错误
      return env.data as any
    }
    // 业务错误码（HTTP 200 但 code != 0）—— 抛出 BusinessError
    throw new BusinessError(env.code, env.message, response.status, env.errors, undefined)
  },
  async (error: AxiosError<Envelope>) => {
    const { response, config } = error

    // 请求被取消（AbortController）：静默处理
    if (axios.isCancel(error)) {
      return Promise.reject(error)
    }

    // 无响应（超时、断网）
    if (!response) {
      const isTimeout = error.code === 'ECONNABORTED' || error.message.includes('timeout')
      return Promise.reject(
        new BusinessError(
          CODE.INTERNAL,
          isTimeout ? '请求超时，请检查网络后重试' : '网络错误，请检查网络连接',
          0,
          undefined,
          error,
        ),
      )
    }

    const env = response.data
    const httpStatus = response.status

    // 401 且带 BusinessError.code：尝试自动 refresh
    if (httpStatus === 401 && env?.code === CODE.INVALID_TOKEN && !config?._retry && !config?._skipRefresh) {
      try {
        await ensureRefresh()
        // refresh 成功：重试原请求
        // 关键点：必须删掉原 config 上的 Authorization 头，否则 request interceptor 会直接用旧 header
        // 而不从 localStorage 重新读取新 token（interceptor 只在 header 不存在时才读 localStorage）
        const originalConfig = config as RequestConfig
        delete (originalConfig.headers as Record<string, unknown>)?.Authorization
        originalConfig._retry = true
        return request(originalConfig)
      } catch {
        // refresh 失败：清空所有 token + 跳登录
        localStorage.removeItem(ACCESS_TOKEN_KEY)
        localStorage.removeItem(REFRESH_TOKEN_KEY)
        window.location.href = '/login'
        return Promise.reject(new BusinessError(CODE.INVALID_TOKEN, '会话已过期，请重新登录', 401))
      }
    }

    // 其他情况：构造 BusinessError 抛出
    const bizCode = env?.code ?? CODE.INTERNAL
    const message = env?.message ?? '请求失败'
    return Promise.reject(new BusinessError(bizCode, message, httpStatus, env?.errors, error))
  },
)

// ============================================================
// 泛型 helper：get<T> / post<T> / put<T> / patch<T> / del<T>
// ============================================================

/**
 * GET 请求
 *
 * @typeParam T 后端返回的 data 类型（拦截器已拆壳，直接返回 data）
 */
export async function get<T>(url: string, config?: AxiosRequestConfig): Promise<T> {
  return (await request.get(url, config)) as unknown as T
}

/**
 * POST 请求
 *
 * @typeParam T 后端返回的 data 类型
 * @typeParam B 请求体类型
 */
export async function post<T, B = unknown>(url: string, body?: B, config?: AxiosRequestConfig): Promise<T> {
  return (await request.post(url, body, config)) as unknown as T
}

/** PUT 请求（全量更新） */
export async function put<T, B = unknown>(url: string, body?: B, config?: AxiosRequestConfig): Promise<T> {
  return (await request.put(url, body, config)) as unknown as T
}

/** PATCH 请求（部分更新） */
export async function patch<T, B = unknown>(url: string, body?: B, config?: AxiosRequestConfig): Promise<T> {
  return (await request.patch(url, body, config)) as unknown as T
}

/** DELETE 请求 */
export async function del<T = null>(url: string, config?: AxiosRequestConfig): Promise<T> {
  const result = (await request.delete(url, config)) as unknown as T | ''
  // 204 No Content 时 axios 返回空字符串，需兜底成 null
  return (result === '' ? (null as T) : result) as T
}
