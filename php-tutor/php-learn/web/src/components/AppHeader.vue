<script setup lang="ts">
/**
 * ============================================================
 * 顶部栏组件（Element Plus 版）
 * ============================================================
 *
 * 显示：
 *   - 左侧：站点 logo + 标题
 *   - 中间：主导航（首页 / 文章 / 活动）
 *   - 右侧：用户名 + 角色标签 + 登出按钮
 *
 * 对应 Java：类似 th:replace="layouts/app :: navbar"
 */
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { computed } from 'vue'

const router = useRouter()
const auth = useAuthStore()

// 导航链接
const navLinks = computed(() => [
  { to: '/', label: '首页', exact: true },
  { to: '/articles', label: '文章' },
  { to: '/activities', label: '活动' },
])

// 是否当前路由（支持 exact 匹配首页）
function isActive(to: string, exact?: boolean): boolean {
  if (exact) return router.currentRoute.value.path === to
  return router.currentRoute.value.path.startsWith(to)
}

async function handleLogout(): Promise<void> {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <el-header class="app-header">
    <div class="header-inner">
      <!-- Logo -->
      <router-link to="/" class="logo-link">
        <el-icon class="logo-icon" :size="28">
          <Document />
        </el-icon>
        <span class="logo-text">php-learn</span>
      </router-link>

      <!-- 导航 -->
      <el-menu
        :default-active="router.currentRoute.value.path"
        mode="horizontal"
        :router="false"
        class="nav-menu"
        @select="(path: string) => router.push(path)"
      >
        <el-menu-item
          v-for="link in navLinks"
          :key="link.to"
          :index="link.to"
          :class="{ 'is-active': isActive(link.to, link.exact) }"
        >
          {{ link.label }}
        </el-menu-item>
      </el-menu>

      <!-- 右侧：用户信息 + 登出 -->
      <div class="header-right">
        <template v-if="auth.user">
          <el-tag
            :type="auth.isAdmin ? 'primary' : 'info'"
            size="small"
            class="role-tag"
          >
            {{ auth.isAdmin ? '管理员' : '用户' }}
          </el-tag>
          <span class="username">{{ auth.username }}</span>
        </template>
        <el-button
          type="danger"
          text
          bg
          @click="handleLogout"
        >
          <el-icon><SwitchButton /></el-icon>
          登出
        </el-button>
      </div>
    </div>
  </el-header>
</template>

<style scoped>
.app-header {
  position: sticky;
  top: 0;
  z-index: 100;
  border-bottom: 1px solid var(--el-border-color-light);
  background-color: var(--el-bg-color);
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
}

.header-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

/* Logo */
.logo-link {
  display: flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  color: var(--el-text-color-primary);
  font-size: 18px;
  font-weight: 700;
}

.logo-icon {
  color: var(--el-color-primary);
}

.logo-text {
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC",
               "Microsoft YaHei", "Helvetica Neue", Helvetica, Arial, sans-serif;
}

/* 导航菜单 */
.nav-menu {
  flex: 1;
  justify-content: center;
  border-bottom: none;
  background: transparent;
}

.nav-menu .el-menu-item {
  font-weight: 500;
  color: var(--el-text-color-secondary);
  border-bottom: 2px solid transparent;
  transition: all 0.2s ease;
}

.nav-menu .el-menu-item:hover {
  color: var(--el-color-primary);
  background-color: var(--el-fill-color-lighter);
}

.nav-menu .el-menu-item.is-active {
  color: var(--el-color-primary);
  border-bottom-color: var(--el-color-primary);
}

/* 右侧用户区 */
.header-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.username {
  font-size: 14px;
  color: var(--el-text-color-primary);
  font-weight: 500;
}

.role-tag {
  margin-right: 4px;
}
</style>