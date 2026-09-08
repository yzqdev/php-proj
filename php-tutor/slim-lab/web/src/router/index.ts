import { createRouter, createWebHistory } from 'vue-router'
import { useUserStore } from '@/stores/user'
import type { RouteRecordRaw } from 'vue-router'

/**
 * 路由表：meta.public 页面（登录/注册）不套后台布局；
 * meta.menu + meta.title + meta.icon 供 Layout 侧边栏自动生成菜单；
 * 全部页面懒加载。
 */
const routes: RouteRecordRaw[] = [
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/auth/LoginView.vue'),
    meta: { public: true, title: '登录' },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/views/auth/RegisterView.vue'),
    meta: { public: true, title: '注册' },
  },
  {
    path: '/share/:token',
    name: 'share',
    component: () => import('@/views/ShareView.vue'),
    meta: { public: true, title: '文件分享' },
  },
  {
    path: '/',
    name: 'home',
    component: () => import('@/views/HomeView.vue'),
    meta: { title: '首页', menu: true, icon: 'HomeFilled' },
  },
  {
    path: '/doctrine',
    name: 'doctrine',
    component: () => import('@/views/DoctrineView.vue'),
    meta: { title: 'Doctrine', menu: true, icon: 'Coin' },
  },
  {
    path: '/php',
    name: 'php',
    component: () => import('@/views/PhpDemoView.vue'),
    meta: { title: 'PHP Demo', menu: true, icon: 'Cpu' },
  },
  {
    path: '/clouddrive',
    name: 'clouddrive',
    component: () => import('@/views/CloudDriveView.vue'),
    meta: { title: '网盘', menu: true, icon: 'FolderOpened' },
  },
  {
    path: '/logs',
    name: 'logs',
    component: () => import('@/views/LogsView.vue'),
    meta: { title: '日志', menu: true, icon: 'Document' },
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/views/NotFoundView.vue'),
    meta: { title: '页面不存在' },
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

router.afterEach((to) => {
  const title = (to.meta.title as string | undefined) ?? ''
  document.title = title === '' ? 'slim-lab' : `${title} · slim-lab`
})

/**
 * 全局守卫：
 *   - 未登录访问受保护页面 → /login?redirect=来源（登录成功后跳回）
 *   - 已登录访问 /login、/register → 首页
 *   - 有 token 但尚无用户信息（刷新后）→ 调 getUserInfo 恢复登录态
 */
router.beforeEach(async (to) => {
  const userStore = useUserStore()

  if (to.meta.public && userStore.isLoggedIn && (to.name === 'login' || to.name === 'register')) {
    return { name: 'home' }
  }

  if (!to.meta.public && !userStore.isLoggedIn) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (userStore.isLoggedIn && userStore.userInfo === null) {
    try {
      await userStore.fetchUserInfo()
    } catch {
      // 401 已由拦截器清凭证并跳登录；其余错误也回登录页兜底
      return { name: 'login', query: { redirect: to.fullPath } }
    }
  }

  return true
})

export default router
