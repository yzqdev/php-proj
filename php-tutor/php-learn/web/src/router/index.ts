/**
 * ============================================================
 * Vue Router 5 配置
 * ============================================================
 *
 * 路由表：
 *   /            → HomeView（首页，展示用户信息）
 *   /login       → LoginView（登录页）
 *   /articles    → 待 Step 4 实现
 *   /activities  → 待 Step 4 实现
 *   /404         → 404 页面（兜底）
 *
 * 全局前置守卫（beforeEach）：
 *   - 未登录访问受保护页面 → 跳 /login
 *   - 已登录访问 /login   → 跳 /
 *   - 首次进入且已登录     → 拉取用户信息（/me）
 *
 * 对应 Java / React Router：
 *   - React Router 的 <ProtectedRoute>
 *   - Angular 的 CanActivate
 */
import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// 路由表
const routes: RouteRecordRaw[] = [
  {
    path: '/',
    name: 'home',
    component: () => import('@/views/HomeView.vue'),
    meta: { requiresAuth: true, title: '首页' },
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/LoginView.vue'),
    meta: { public: true, title: '登录' },
  },
  // 文章列表（Step 4 前只是占位）
  {
    path: '/articles',
    name: 'articles',
    component: () => import('@/views/ArticlesView.vue'),
    meta: { requiresAuth: true, title: '文章列表' },
  },
  // 文章新建（路径参数 id='new'，先注册以便优先匹配）
  {
    path: '/articles/new',
    name: 'article-new',
    component: () => import('@/views/ArticleEditView.vue'),
    meta: { requiresAuth: true, title: '新建文章' },
  },
  // 文章详情
  {
    path: '/articles/:id(\\d+)',
    name: 'article-detail',
    component: () => import('@/views/ArticleDetailView.vue'),
    meta: { requiresAuth: true, title: '文章详情' },
  },
  // 文章编辑
  {
    path: '/articles/:id(\\d+)/edit',
    name: 'article-edit',
    component: () => import('@/views/ArticleEditView.vue'),
    meta: { requiresAuth: true, title: '编辑文章' },
  },
  // 活动列表
  {
    path: '/activities',
    name: 'activities',
    component: () => import('@/views/ActivitiesView.vue'),
    meta: { requiresAuth: true, title: '活动列表' },
  },
  // 活动新建
  {
    path: '/activities/new',
    name: 'activity-new',
    component: () => import('@/views/ActivityFormView.vue'),
    meta: { requiresAuth: true, title: '创建活动' },
  },
  // 系统日志（管理员）
  {
    path: '/logs',
    name: 'logs',
    component: () => import('@/views/LogsView.vue'),
    meta: { requiresAuth: true, title: '系统日志' },
  },
  // 鹈鹕骑自行车插画页（公开页面，无需登录）
  {
    path: '/pelican-bike',
    name: 'pelican-bike',
    component: () => import('@/views/PelicanBikeView.vue'),
    meta: { public: true, title: 'Pelican on a Bike' },
  },
  // 404 兜底
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/views/NotFoundView.vue'),
    meta: { public: true, title: '页面不存在' },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  // 严格模式：路径必须精确匹配
  strict: false,
  // 滚动行为：切换路由回到顶部
  scrollBehavior: (_to, _from, savedPosition) => {
    return savedPosition ?? { top: 0 }
  },
})

// ============================================================
// 全局前置守卫：鉴权 + 用户信息懒加载
// ============================================================

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  // 1) 首次进入应用：如果 localStorage 里有 token，但 state 里 user 还没拉，先拉一次
  //    注意：init() 在 store 创建时已自动跑，这里只处理"有 token 但 user 为空"的情况
  if (auth.isLoggedIn && !auth.user) {
    // 异步拉取用户信息，如果失败（token 无效）拦截器会自动跳登录
    try {
      await auth.me()
    } catch {
      // 401 时拦截器已跳 /login，这里直接放行（守卫后续会重新判断）
    }
  }

  // 2) 需要登录但未登录 → 跳登录
  if (to.meta.requiresAuth && !auth.isLoggedIn) {
    return {
      name: 'login',
      query: { redirect: to.fullPath }, // 登录成功后跳回原页面
    }
  }

  // 3) 已登录却访问登录页 → 跳首页
  if (to.meta.public && auth.isLoggedIn && to.name === 'login') {
    return { name: 'home' }
  }

  // 4) 更新页面标题
  document.title = (to.meta.title as string | undefined) ?? 'php-learn'

  return true
})

export default router
