<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import LayoutSidebar from './components/LayoutSidebar.vue'
import AppHeader from './components/AppHeader.vue'
import AppMain from './components/AppMain.vue'

/**
 * 后台布局：左侧固定侧边栏 + 右侧顶栏 / 独立滚动内容区。
 * 登录、注册页不套用此布局（App.vue 按路由 meta.public 判断）。
 */
const route = useRoute()

const pageTitle = computed(() => (route.meta.title as string | undefined) ?? '')

const collapsed = ref(false)
const isMobile = ref(false)
const drawerVisible = ref(false)

function checkMobile() {
  isMobile.value = window.innerWidth < 768
}

// 首帧即判定，避免小屏下先渲染桌面侧栏再切换的闪烁
checkMobile()

onMounted(() => {
  window.addEventListener('resize', checkMobile)
})
onBeforeUnmount(() => window.removeEventListener('resize', checkMobile))

function handleToggle() {
  if (isMobile.value) {
    drawerVisible.value = true
  } else {
    collapsed.value = !collapsed.value
  }
}
</script>

<template>
  <div class="flex h-screen overflow-hidden bg-gray-100">
    <!-- 桌面端固定侧边栏（shrink-0 保证不被内容区挤压） -->
    <LayoutSidebar v-if="!isMobile" :collapsed="collapsed" />

    <!-- 移动端抽屉侧边栏（覆盖内容，点击菜单或遮罩关闭） -->
    <el-drawer
      v-if="isMobile"
      v-model="drawerVisible"
      direction="ltr"
      size="220px"
      :with-header="false"
    >
      <LayoutSidebar :collapsed="false" @select="drawerVisible = false" />
    </el-drawer>

    <!-- 右侧：min-w-0 允许内容区收缩，防止表格等宽内容把侧边栏挤变形 -->
    <div class="flex min-w-0 flex-1 flex-col">
      <AppHeader :title="pageTitle" :collapsed="collapsed" @toggle="handleToggle" />
      <AppMain />
    </div>
  </div>
</template>

<style scoped>
:deep(.el-drawer__body) {
  padding: 0;
}
</style>
