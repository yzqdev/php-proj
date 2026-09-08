<script setup lang="ts">
/**
 * ============================================================
 * 首页 — Element Plus 版
 * ============================================================
 *
 * 展示：
 *   - 用户信息卡片（id / 用户名 / 邮箱 / 角色 / 注册时间）
 *   - 快速导航卡片（文章/活动 列表与新建入口）
 *   - 技术栈说明
 *
 * 数据来源：
 *   - authStore.user（登录后已经拉到）
 */
import { storeToRefs } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import { formatDate } from '@/utils/format'

const auth = useAuthStore()
const { user } = storeToRefs(auth)
</script>

<template>
  <div class="home-page">
    <!-- 欢迎语 -->
    <div class="welcome-section">
      <h1 class="welcome-title">
        欢迎回来，<span class="welcome-name">{{ user?.username ?? '' }}</span>
      </h1>
      <p class="welcome-desc">
        这是 php-learn 的前端 SPA，展示前后端分离架构下的 Vue 3 + Element Plus 应用。
      </p>
    </div>

    <!-- 用户信息卡 -->
    <el-card v-if="user" class="user-card" shadow="hover">
      <div class="user-card-header">
        <el-avatar :size="48" class="user-avatar">
          {{ user.username.charAt(0).toUpperCase() }}
        </el-avatar>
        <div class="user-card-info">
          <div class="user-card-name">
            {{ user.username }}
            <el-tag
              :type="user.role === 'admin' ? 'primary' : 'info'"
              size="small"
              class="role-tag"
            >
              {{ user.role === 'admin' ? '管理员' : '普通用户' }}
            </el-tag>
          </div>
          <div class="user-card-email">{{ user.email }}</div>
        </div>
      </div>

      <el-divider />

      <div class="user-card-meta">
        <div class="meta-item">
          <span class="meta-label">用户 ID</span>
          <span class="meta-value">#{{ user.id }}</span>
        </div>
        <div class="meta-item">
          <span class="meta-label">注册时间</span>
          <span class="meta-value">{{ formatDate(user.createdAt) }}</span>
        </div>
      </div>
    </el-card>

    <!-- 快速导航 -->
    <div class="quick-nav">
      <h2 class="nav-title">快速导航</h2>
      <div class="nav-cards">
        <router-link to="/articles" class="nav-card">
          <div class="nav-icon nav-icon-primary">
            <el-icon :size="24"><Document /></el-icon>
          </div>
          <h3 class="nav-card-title">文章列表</h3>
          <p class="nav-card-desc">查看已有文章</p>
        </router-link>

        <router-link v-if="auth.isAdmin" to="/articles/new" class="nav-card">
          <div class="nav-icon nav-icon-primary">
            <el-icon :size="24"><Edit /></el-icon>
          </div>
          <h3 class="nav-card-title">新建文章</h3>
          <p class="nav-card-desc">直接创建新文章</p>
        </router-link>

        <router-link to="/activities" class="nav-card">
          <div class="nav-icon nav-icon-success">
            <el-icon :size="24"><Calendar /></el-icon>
          </div>
          <h3 class="nav-card-title">活动列表</h3>
          <p class="nav-card-desc">即将开始的活动</p>
        </router-link>

        <router-link v-if="auth.isAdmin" to="/activities/new" class="nav-card">
          <div class="nav-icon nav-icon-success">
            <el-icon :size="24"><Plus /></el-icon>
          </div>
          <h3 class="nav-card-title">创建活动</h3>
          <p class="nav-card-desc">直接创建新活动</p>
        </router-link>
      </div>
    </div>

    <!-- 技术栈说明 -->
    <el-card class="tech-card" shadow="hover">
      <template #header>
        <span class="tech-title">当前技术栈</span>
      </template>
      <div class="tech-list">
        <div class="tech-item">
          <el-icon class="tech-dot" color="primary"><CircleFilled /></el-icon>
          <div>
            <div class="tech-name">Vue 3 + TypeScript</div>
            <div class="tech-desc">Composition API · Script Setup · 严格类型</div>
          </div>
        </div>
        <div class="tech-item">
          <el-icon class="tech-dot" color="primary"><CircleFilled /></el-icon>
          <div>
            <div class="tech-name">Vite 8</div>
            <div class="tech-desc">ESM 配置文件 · 插件生态</div>
          </div>
        </div>
        <div class="tech-item">
          <el-icon class="tech-dot" color="primary"><CircleFilled /></el-icon>
          <div>
            <div class="tech-name">Element Plus</div>
            <div class="tech-desc">组件库 · 自动导入 · 中文包</div>
          </div>
        </div>
        <div class="tech-item">
          <el-icon class="tech-dot" color="success"><CircleFilled /></el-icon>
          <div>
            <div class="tech-name">Axios + Bearer Token</div>
            <div class="tech-desc">拦截器拆壳 · 401 自动 refresh</div>
          </div>
        </div>
        <div class="tech-item">
          <el-icon class="tech-dot" color="success"><CircleFilled /></el-icon>
          <div>
            <div class="tech-name">Pinia + Vue Router 5</div>
            <div class="tech-desc">Setup store 语法 · 路由守卫</div>
          </div>
        </div>
        <div class="tech-item">
          <el-icon class="tech-dot" color="success"><CircleFilled /></el-icon>
          <div>
            <div class="tech-name">PHP 8.5 后端</div>
            <div class="tech-desc">无框架 · Bearer Token · 手写 JWT</div>
          </div>
        </div>
      </div>
    </el-card>
  </div>
</template>

<style scoped>
.home-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* 欢迎语 */
.welcome-section {
  margin-bottom: 8px;
}

.welcome-title {
  margin: 0 0 8px 0;
  font-size: 28px;
  font-weight: 700;
  color: var(--el-text-color-primary);
}

.welcome-name {
  color: var(--el-color-primary);
}

.welcome-desc {
  margin: 0;
  font-size: 15px;
  color: var(--el-text-color-secondary);
}

/* 用户卡片 */
.user-card {
  border-radius: 12px;
}

.user-card-header {
  display: flex;
  align-items: center;
  gap: 16px;
}

.user-card-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.user-card-name {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 18px;
  font-weight: 600;
  color: var(--el-text-color-primary);
}

.user-card-email {
  font-size: 14px;
  color: var(--el-text-color-secondary);
}

.role-tag {
  margin-left: 4px;
}

.user-card-meta {
  display: flex;
  gap: 32px;
}

.meta-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.meta-label {
  font-size: 12px;
  color: var(--el-text-color-secondary);
}

.meta-value {
  font-size: 15px;
  font-weight: 600;
  color: var(--el-text-color-primary);
}

/* 快速导航 */
.quick-nav {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.nav-title {
  margin: 0;
  font-size: 20px;
  font-weight: 600;
  color: var(--el-text-color-primary);
}

.nav-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 16px;
}

.nav-card {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 12px;
  padding: 20px;
  border-radius: 12px;
  text-decoration: none;
  color: var(--el-text-color-primary);
  transition: all 0.2s ease;
  border: 1px solid var(--el-border-color-lighter);
  background-color: var(--el-bg-color);
}

.nav-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  border-color: var(--el-color-primary);
}

.nav-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 44px;
  height: 44px;
  border-radius: 10px;
}

.nav-icon-primary {
  background-color: var(--el-color-primary-light-9);
  color: var(--el-color-primary);
}

.nav-icon-success {
  background-color: var(--el-color-success-light-9);
  color: var(--el-color-success);
}

.nav-card-title {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
}

.nav-card-desc {
  margin: 0;
  font-size: 13px;
  color: var(--el-text-color-secondary);
}

/* 技术栈 */
.tech-card {
  border-radius: 12px;
}

.tech-title {
  font-size: 18px;
  font-weight: 600;
  color: var(--el-text-color-primary);
}

.tech-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.tech-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.tech-dot {
  margin-top: 4px;
  flex-shrink: 0;
}

.tech-name {
  font-size: 15px;
  font-weight: 500;
  color: var(--el-text-color-primary);
}

.tech-desc {
  margin: 2px 0 0 0;
  font-size: 13px;
  color: var(--el-text-color-secondary);
}
</style>