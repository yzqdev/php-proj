<script setup lang="ts">
import type { Component } from 'vue'
import { ArrowRight, Coin, Cpu, Document, FolderOpened } from '@element-plus/icons-vue'

const tools: Array<{ slug: string; name: string; desc: string; icon: Component }> = [
  {
    slug: 'doctrine',
    name: 'Doctrine ORM 演示',
    desc: '完整的 CRUD、DQL 查询、事务回滚示例（Redis 缓存加速）',
    icon: Coin,
  },
  {
    slug: 'php',
    name: 'PHP 操作演示',
    desc: '数组/字符串/日期/JSON/正则/文件/随机数/过滤/闭包等',
    icon: Cpu,
  },
  {
    slug: 'clouddrive',
    name: '网盘系统',
    desc: '文件上传/下载/分享/搜索/预览等完整功能',
    icon: FolderOpened,
  },
  {
    slug: 'logs',
    name: '运行日志',
    desc: '按天查看后端日志，支持级别 / 关键词过滤与自动刷新',
    icon: Document,
  },
]
</script>

<template>
  <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <RouterLink
      v-for="tool in tools"
      :key="tool.slug"
      :to="'/' + tool.slug"
      class="group rounded-xl bg-white p-5 no-underline shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
    >
      <div class="flex items-center gap-3">
        <span
          class="grid size-10 place-items-center rounded-lg bg-gradient-to-br from-[#667eea] to-[#764ba2] text-white"
        >
          <el-icon :size="20"><component :is="tool.icon" /></el-icon>
        </span>
        <span class="text-lg font-semibold text-gray-800 transition group-hover:text-[#667eea]">
          {{ tool.name }}
        </span>
      </div>
      <p class="mt-3 min-h-10 text-sm leading-5 text-gray-500">{{ tool.desc }}</p>
      <div
        class="mt-2 flex items-center gap-1 text-sm font-medium text-[#667eea] opacity-0 transition group-hover:opacity-100"
      >
        进入演示
        <el-icon><ArrowRight /></el-icon>
      </div>
    </RouterLink>
  </div>
</template>
