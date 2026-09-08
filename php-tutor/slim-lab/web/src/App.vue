<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, RouterView } from 'vue-router'
import MainLayout from '@/layout/MainLayout.vue'

/**
 * 根组件：登录/注册（meta.public）整屏独立渲染；
 * 其余页面（含 404）套用后台 Layout（其内容区自带 RouterView）。
 */
const route = useRoute()

const useLayout = computed(() => route.meta.public !== true)
</script>

<template>
  <MainLayout v-if="useLayout" />
  <RouterView v-else />
</template>
