<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import type { Component } from 'vue'
import { Coin, Cpu, Document, FolderOpened, HomeFilled } from '@element-plus/icons-vue'

/**
 * 侧边栏：Logo + 由路由表（meta.menu / meta.title / meta.icon）自动生成的菜单，
 * 当前路由高亮；折叠时只显示图标（w-16=64px 与 el-menu 折叠宽度一致）。
 * 菜单点击后向父组件发 select（用于关闭移动端抽屉）。
 */
const props = withDefaults(defineProps<{ collapsed?: boolean }>(), { collapsed: false })
const emit = defineEmits<{ select: [] }>()

const route = useRoute()
const router = useRouter()

const iconMap: Record<string, Component> = { HomeFilled, Cpu, Coin, FolderOpened, Document }

const menus = computed(() =>
  router.options.routes
    .filter((r) => r.meta?.menu)
    .map((r) => ({
      path: r.path,
      title: r.meta?.title as string,
      icon: r.meta?.icon as string,
    })),
)

function goHome() {
  router.push({ name: 'home' })
}
</script>

<template>
  <aside
    class="flex h-full shrink-0 flex-col overflow-hidden bg-[#1e1b4b] transition-all duration-200"
    :class="props.collapsed ? 'w-16' : 'w-[220px]'"
  >
    <div
      class="flex h-14 shrink-0 cursor-pointer items-center gap-2 px-4 text-white"
      @click="goHome"
    >
      <span
        class="grid size-8 shrink-0 place-items-center rounded-lg bg-gradient-to-br from-[#667eea] to-[#764ba2] text-xs font-bold"
      >
        SL
      </span>
      <span v-if="!props.collapsed" class="whitespace-nowrap font-bold">slim-lab</span>
    </div>

    <el-menu
      class="flex-1 overflow-y-auto overflow-x-hidden !border-r-0"
      :default-active="route.path"
      :collapse="props.collapsed"
      :collapse-transition="false"
      background-color="#1e1b4b"
      text-color="#c7d2fe"
      active-text-color="#ffffff"
      router
      @select="emit('select')"
    >
      <el-menu-item v-for="item in menus" :key="item.path" :index="item.path">
        <el-icon>
          <component :is="iconMap[item.icon]" />
        </el-icon>
        <template #title>{{ item.title }}</template>
      </el-menu-item>
    </el-menu>
  </aside>
</template>

<style scoped>
:deep(.el-menu-item.is-active) {
  background-color: #4f46e5;
}
:deep(.el-menu-item:hover) {
  background-color: rgb(255 255 255 / 8%);
}
</style>
