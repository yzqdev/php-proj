import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import { authGetUserInfo, authLogin, authLogout, authRegister } from '@/api/auth'
import type { AuthUser } from '@/types/api'

const TOKEN_KEY = 'slim-lab-token'

/** 登录态全局存储：token 持久化 localStorage，页面刷新后经路由守卫调 getUserInfo 恢复 */
export const useUserStore = defineStore('user', () => {
  const token = ref<string>(localStorage.getItem(TOKEN_KEY) ?? '')
  const userInfo = ref<AuthUser | null>(null)

  const isLoggedIn = computed(() => token.value !== '')
  const username = computed(() => userInfo.value?.username ?? '')

  function setToken(value: string) {
    token.value = value
    if (value === '') {
      localStorage.removeItem(TOKEN_KEY)
    } else {
      localStorage.setItem(TOKEN_KEY, value)
    }
  }

  /** 401 拦截器使用：仅清本地凭证，不再发起请求 */
  function clearSession() {
    setToken('')
    userInfo.value = null
  }

  async function login(username: string, password: string) {
    const { data: res } = await authLogin(username, password)
    setToken(res.data.token)
    userInfo.value = res.data.user
  }

  async function register(username: string, password: string) {
    await authRegister(username, password)
  }

  async function fetchUserInfo() {
    const { data: res } = await authGetUserInfo()
    userInfo.value = res.data.user
  }

  async function logout() {
    try {
      await authLogout()
    } finally {
      clearSession()
    }
  }

  return {
    token,
    userInfo,
    isLoggedIn,
    username,
    setToken,
    clearSession,
    login,
    register,
    fetchUserInfo,
    logout,
  }
})
