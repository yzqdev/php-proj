<script setup lang="ts">
/**
 * ============================================================
 * 根组件
 * ============================================================
 *
 * 结构：
 *   - <AppHeader /> 顶部栏（登录状态 + 导航 + 登出）
 *   - <router-view /> 当前路由对应的页面
 *
 * 只有已登录才显示 Header，未登录只显示登录页
 *
 * 对应 Java：类似/layouts/app.html 模板
 */
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppHeader from '@/components/AppHeader.vue'

const authStore = useAuthStore()
const route = useRoute()

// 登录页不显示顶部栏（避免循环跳转：/login 未登录 → 守卫 → /login → Header 又触发守卫）
const isLoginPage = computed(() => route.path === '/login')
const showHeader = computed(() => authStore?.isLoggedIn && !isLoginPage.value)
</script>

<template>
  <div id="app" class="min-h-screen">
    <!-- 顶部栏 -->
    <AppHeader v-if="showHeader" />

    <!-- 页面内容 -->
    <main class="p-6">
      <router-view />
    </main>

    <!-- 页脚 -->
    <footer class="border-t border-[var(--el-border-color-light)] bg-white py-4">
      <div class="mx-auto max-w-6xl px-4 text-center text-sm text-[var(--el-text-color-secondary)]">
        php-learn · Vue 3 + TypeScript + Vite 8 + Element Plus ·
        <span class="font-mono">{{ new Date().getFullYear() }}</span>
      </div>
    </footer>
  </div>
</template>

<style scoped>
#app {
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC",
               "Microsoft YaHei", "Helvetica Neue", Helvetica, Arial, sans-serif;
}
</style>