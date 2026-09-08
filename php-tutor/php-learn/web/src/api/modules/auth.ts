/**
 * ============================================================
 * 认证 API 模块
 * ============================================================
 *
 * 对应后端：
 *   POST /api/v1/auth/login    → login()
 *   POST /api/v1/auth/refresh  → refresh()
 *   POST /api/v1/auth/logout   → logout()
 *   GET  /api/v1/auth/me       → me()
 *
 * 对应 Java：类似 AuthService 客户端
 */
import { get, post } from '../request'
import type { LoginPayload, LoginResponse, RefreshPayload, RefreshResponse } from '@/types/api'
import type { User } from '@/types/models'

/**
 * 登录
 *
 * @example
 *   const { accessToken, refreshToken, user } = await authApi.login({
 *     login: 'admin',
 *     password: 'admin123',
 *   })
 */
export const authApi = {
  login: (payload: LoginPayload) => post<LoginResponse>('/auth/login', payload),

  /** 用 Refresh Token 换新的 Access Token（一般由拦截器自动调，业务代码不用直接调） */
  refresh: (payload: RefreshPayload) => post<RefreshResponse>('/auth/refresh', payload),

  /** 登出（撤销所有 refresh token，access token 等自然过期） */
  logout: () => post<null>('/auth/logout', undefined, { skipRefresh: true } as never),

  /** 获取当前登录用户（Bearer Token 由拦截器注入） */
  me: () => get<User>('/auth/me'),
}
