/**
 * ============================================================
 * 认证状态管理（Pinia）
 * ============================================================
 *
 * 状态来源：
 *   - localStorage：token 持久化（跨会话保持登录）
 *   - /api/v1/auth/me：拉取当前用户完整信息
 *
 * 与后端配合：
 *   - 登录成功：后端返回 { accessToken, refreshToken, user }
 *     → 前端存 localStorage + 写入 store
 *   - 登出：调 /auth/logout（后端撤销 refresh）+ 清 localStorage + 清 store
 *   - 页面刷新后：从 localStorage 恢复 token，再调 /me 拉最新用户信息
 *
 * 对应 Java / Redux：
 *   - Redux Toolkit 的 createSlice + createAsyncThunk
 *   - Vuex 的 store + actions（现代做法已转 Pinia）
 *
 * 关键点：
 *   - setup store 语法（Pinia 推荐）：ref / computed / function
 *   - 初始化时从 localStorage 恢复 state
 *   - isLoggedIn 是 computed，不是 state（避免手动同步）
 */
import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import { authApi } from '@/api/modules/auth'
import { ACCESS_TOKEN_KEY, REFRESH_TOKEN_KEY, registerTokenRefreshCallback } from '@/api/request'
import type { LoginPayload, UserSummary } from '@/types/api'
import type { User } from '@/types/models'

export const useAuthStore = defineStore('auth', () => {
  // ============================================================
  // State
  // ============================================================

  /** 当前登录用户（登录后或 /me 接口返回后填充） */
  const user = ref<User | null>(null)

  /** Access Token（存 localStorage 用于持久化，state 里放一份用于快速读） */
  const accessToken = ref<string>('')

  /** Refresh Token（只存 localStorage，不在 state 里冗余） */
  const refreshToken = ref<string>('')

  // ============================================================
  // Getters
  // ============================================================

  /** 是否已登录（token 存在即视为登录态，实际可用性由后端校验） */
  const isLoggedIn = computed(() => accessToken.value.length > 0)

  /** 用户名简写（未登录返回空字符串） */
  const username = computed(() => user.value?.username ?? '')

  /** 是否管理员 */
  const isAdmin = computed(() => user.value?.role === 'admin')

  // ============================================================
  // Actions
  // ============================================================

  /**
   * 初始化：从 localStorage 恢复 state
   * 调用时机：应用启动时（可以在 router 守卫里懒加载）
   */
  function init(): void {
    accessToken.value = localStorage.getItem(ACCESS_TOKEN_KEY) ?? ''
    refreshToken.value = localStorage.getItem(REFRESH_TOKEN_KEY) ?? ''
  }

  /**
   * 登录
   *
   * @returns 登录后的用户信息
   * @throws BusinessError 登录失败（账号密码错误）
   */
  async function login(payload: LoginPayload): Promise<UserSummary> {
    const result = await authApi.login(payload)

    // 存 token（localStorage 用于跨会话持久化）
    localStorage.setItem(ACCESS_TOKEN_KEY, result.accessToken)
    localStorage.setItem(REFRESH_TOKEN_KEY, result.refreshToken)

    // 更新 state
    accessToken.value = result.accessToken
    refreshToken.value = result.refreshToken
    // 用简版 user 先填，稍后可调 me() 拉完整信息
    user.value = {
      id: result.user.id,
      username: result.user.username,
      email: result.user.email,
      role: result.user.role,
      createdAt: '',
    }

    return result.user
  }

  /**
   * 登出
   *
   * 步骤：
   *   1. 调后端 /logout 撤销所有 refresh token
   *   2. 清 localStorage（accessToken / refreshToken）
   *   3. 清 store state
   *
   * 注意：即使后端 /logout 失败，也要清本地（避免用户以为登出但实际没登出）
   */
  async function logout(): Promise<void> {
    try {
      // skipRefresh 已放在 config 里，避免登出请求本身触发 refresh 逻辑
      await authApi.logout()
    } catch {
      // 忽略后端错误：本地一定要清干净
    } finally {
      localStorage.removeItem(ACCESS_TOKEN_KEY)
      localStorage.removeItem(REFRESH_TOKEN_KEY)
      accessToken.value = ''
      refreshToken.value = ''
      user.value = null
    }
  }

  /**
   * 拉取当前用户完整信息
   *
   * 调用时机：
   *   - 应用启动且 isLoggedIn 为 true 时（router 守卫）
   *   - 需要刷新用户名/角色时
   *
   * @returns 用户信息；未登录时返回 null
   */
  async function me(): Promise<User | null> {
    if (!isLoggedIn.value) return null
    try {
      const u = await authApi.me()
      user.value = u
      return u
    } catch {
      // 后端返回 401：request.ts 拦截器已自动触发 refresh
      // 如果 refresh 也失败，拦截器已清 localStorage 并跳登录
      // 这里只需清 state
      user.value = null
      return null
    }
  }

  /**
   * 强制登出（不清后端）：
   *   - 用于请求拦截器发现 token 失效时
   *   - 不调后端 /logout，只清本地
   */
  function forceLogout(): void {
    localStorage.removeItem(ACCESS_TOKEN_KEY)
    localStorage.removeItem(REFRESH_TOKEN_KEY)
    accessToken.value = ''
    refreshToken.value = ''
    user.value = null
  }

  // ============================================================
  // 初始化：立刻从 localStorage 恢复（不 await 网络请求）
  // ============================================================
  init()

  // ============================================================
  // 注册 token 刷新回调：performRefresh() 更新 localStorage 后
  // 通过此回调同步 store 内部 ref，避免 isLoggedIn 等 computed
  // 拿到过期值，导致路由守卫误判为"未登录"。
  // ============================================================
  registerTokenRefreshCallback((newAccess: string, newRefresh: string) => {
    accessToken.value = newAccess
    refreshToken.value = newRefresh
  })

  return {
    // State
    user,
    accessToken,
    refreshToken,
    // Getters
    isLoggedIn,
    username,
    isAdmin,
    // Actions
    login,
    logout,
    me,
    forceLogout,
  }
})
