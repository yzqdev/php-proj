<template>
  <el-container class="app-shell">
    <el-aside :width="asideWidth" class="app-aside">
      <div class="app-logo">
        <img v-show="!isCollapse" src="@/assets/logo.svg" alt="CodeForge" class="logo-mark" />
        <span v-show="!isCollapse" class="logo-text">CodeForge</span>
      </div>

      <el-menu
        :default-active="activeMenu"
        :collapse="isCollapse"
        :collapse-transition="false"
        background-color="#304156"
        text-color="#bfcbd9"
        active-text-color="#ffffff"
        class="app-menu"
        router
      >
        <el-menu-item v-for="item in menuItems" :key="item.path" :index="item.path">
          <el-icon>
            <component :is="item.icon" />
          </el-icon>
          <span>{{ item.title }}</span>
        </el-menu-item>
      </el-menu>
    </el-aside>

    <el-container direction="vertical" class="app-body">
      <el-header class="app-header">
        <div class="header-left">
          <button
            type="button"
            class="collapse-btn"
            :aria-label="isCollapse ? '展开菜单' : '收起菜单'"
            @click="toggleCollapse"
          >
            <el-icon>
              <Fold v-if="!isCollapse" />
              <Expand v-else />
            </el-icon>
          </button>
          <el-breadcrumb separator="/">
            <el-breadcrumb-item @click="goHome">首页</el-breadcrumb-item>
            <el-breadcrumb-item v-if="currentTitle !== '首页'">{{ currentTitle }}</el-breadcrumb-item>
          </el-breadcrumb>
        </div>

        <div class="header-right">
          <span class="clock">{{ now }}</span>
          <el-tag size="small" effect="light">v1.0</el-tag>
        </div>
      </el-header>

      <el-main class="app-main">
        <RouterView />
      </el-main>
    </el-container>
  </el-container>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { RouterView, useRoute, useRouter } from 'vue-router'
import dayjs from 'dayjs'

const COLLAPSE_STORAGE_KEY = 'codeforge-aside-collapse'

const route = useRoute()
const router = useRouter()

interface MenuItem {
  path: string
  title: string
  icon: string
}

const menuItems: MenuItem[] = [
  { path: '/', title: '首页', icon: 'HomeFilled' },
  { path: '/users', title: '用户管理', icon: 'User' },
  { path: '/files', title: '文件管理', icon: 'Folder' },
  { path: '/logs', title: '日志查看', icon: 'Document' },
]

const isCollapse = ref(localStorage.getItem(COLLAPSE_STORAGE_KEY) === '1')
const asideWidth = computed(() => (isCollapse.value ? '64px' : '220px'))
const activeMenu = computed(() => route.path)
const currentTitle = computed(() => (route.meta.title as string | undefined) ?? 'CodeForge')

const now = ref(dayjs().format('YYYY-MM-DD HH:mm:ss'))
let clockTimer: number | undefined

function toggleCollapse(): void {
  isCollapse.value = !isCollapse.value
  localStorage.setItem(COLLAPSE_STORAGE_KEY, isCollapse.value ? '1' : '0')
}

function goHome(): void {
  if (route.path !== '/') {
    void router.push('/')
  }
}

onMounted(() => {
  clockTimer = window.setInterval(() => {
    now.value = dayjs().format('YYYY-MM-DD HH:mm:ss')
  }, 1000)
})

onUnmounted(() => {
  if (clockTimer !== undefined) {
    window.clearInterval(clockTimer)
  }
})
</script>

<style scoped>
.app-shell {
  height: 100vh;
  overflow: hidden;
}

.app-aside {
  background-color: #304156;
  overflow: hidden;
  transition: width 0.28s;
}

.app-logo {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  height: 56px;
  overflow: hidden;
  white-space: nowrap;
}

.logo-mark {
  width: 28px;
  height: 28px;
}

.logo-text {
  color: #ffffff;
  font-size: 18px;
  font-weight: 700;
  letter-spacing: 0.5px;
}

.app-menu {
  border-right: none;
}

/* 选中项左侧高亮条 + 背景色 */
.app-menu .el-menu-item.is-active {
  background-color: #409eff;
}

.app-menu .el-menu-item:hover {
  background-color: #263445;
}

.app-body {
  overflow: hidden;
}

.app-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 56px;
  padding: 0 20px;
  background-color: #ffffff;
  border-bottom: 1px solid #e4e7ed;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 14px;
}

.collapse-btn {
  display: inline-flex;
  align-items: center;
  padding: 6px;
  font-size: 20px;
  line-height: 1;
  color: #5a5e66;
  cursor: pointer;
  background: transparent;
  border: none;
  border-radius: 4px;
}

.collapse-btn:hover {
  color: #409eff;
  background-color: #f0f2f5;
}

.collapse-btn:focus-visible {
  outline: 2px solid #409eff;
  outline-offset: 2px;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.clock {
  font-size: 13px;
  color: #909399;
  font-variant-numeric: tabular-nums;
}

.app-main {
  padding: 0;
  background-color: #f0f2f5;
  overflow-y: auto;
}
</style>
