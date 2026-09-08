import request from '@/utils/request'
import type { ApiResponse, LoginResult, UserInfoResult } from '@/types/api'

/** 认证模块 API：/api/auth/*（Bearer Token 方案，信封与全站一致） */

export function authLogin(username: string, password: string) {
  return request.post<ApiResponse<LoginResult>>('/auth/login', { username, password })
}

export function authRegister(username: string, password: string) {
  return request.post<ApiResponse<null>>('/auth/register', { username, password })
}

export function authLogout() {
  // 退出时 token 可能已失效，跳过 401 的全局重定向，由调用方处理
  return request.post<ApiResponse<null>>('/auth/logout', undefined, { skipAuthRedirect: true })
}

export function authGetUserInfo() {
  return request.get<ApiResponse<UserInfoResult>>('/auth/userInfo')
}
