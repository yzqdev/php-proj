<script setup lang="ts">
import { ElConfigProvider } from 'element-plus'
import zhCn from 'element-plus/es/locale/lang/zh-cn'
import GalleryView from '@/views/GalleryView.vue'
import LogsView from '@/views/LogsView.vue'

type View = 'gallery' | 'logs'

const view = ref<View>('gallery')
</script>

<template>
  <el-config-provider :locale="zhCn">
    <div class="min-h-screen">
      <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-3">
          <h1 class="text-lg font-bold">简单图床</h1>

          <el-radio-group v-model="view" class="flex">
            <el-radio-button value="gallery">图库</el-radio-button>
            <el-radio-button value="logs">日志</el-radio-button>
          </el-radio-group>

          <span class="text-sm text-gray-400">Vue 3 + Slim API 前后端分离</span>
        </div>
      </header>

      <main class="mx-auto max-w-6xl px-4 py-6">
        <!-- 用 v-if 而非 v-show:切走后销毁图库视图,顺便清掉它的 Ctrl+V 粘贴监听 -->
        <GalleryView v-if="view === 'gallery'" />
        <LogsView v-else />
      </main>
    </div>
  </el-config-provider>
</template>
