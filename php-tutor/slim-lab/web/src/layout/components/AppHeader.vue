<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { Expand, Fold, SwitchButton } from '@element-plus/icons-vue'
import { useUserStore } from '@/stores/user'

/**
 * 顶部栏：折叠按钮 / 当前页面标题 / 用户头像下拉（退出登录）。
 */
defineProps<{ title: string; collapsed: boolean }>()
const emit = defineEmits<{ toggle: [] }>()

const router = useRouter()
const userStore = useUserStore()

const initials = computed(() => userStore.username.slice(0, 1).toUpperCase() || 'U')

async function handleCommand(command: string | number | object) {
  if (command !== 'logout') return
  try {
    await userStore.logout()
  } catch {
    // 失败提示已由拦截器弹出；本地凭证已在 store 中清除
  } finally {
    router.push({ name: 'login' })
  }
}
</script>

<template>
  <header
    class="flex h-14 shrink-0 items-center justify-between border-b border-gray-200 bg-white px-4"
  >
    <div class="flex items-center gap-3">
      <el-icon
        class="cursor-pointer text-lg text-gray-600"
        :title="collapsed ? '展开菜单' : '收起菜单'"
        @click="emit('toggle')"
      >
        <Expand v-if="collapsed" />
        <Fold v-else />
      </el-icon>
      <span class="text-base font-semibold text-gray-700">{{ title }}</span>
    </div>

    <el-dropdown trigger="click" @command="handleCommand">
      <span class="flex cursor-pointer items-center gap-2">
        <el-avatar :size="32" class="!bg-[#667eea]">{{ initials }}</el-avatar>
        <span class="hidden text-sm text-gray-700 sm:inline">{{ userStore.username }}</span>
      </span>
      <template #dropdown>
        <el-dropdown-menu>
          <el-dropdown-item command="logout">
            <el-icon class="mr-1"><SwitchButton /></el-icon>退出登录
          </el-dropdown-item>
        </el-dropdown-menu>
      </template>
    </el-dropdown>
  </header>
</template>
