<script setup lang="ts">
import { RouterView } from 'vue-router'
</script>

<template>
  <!-- 独立滚动：内容区自己滚，不与侧边栏/顶栏联动；居中限宽避免宽屏拉伸 -->
  <main class="min-h-0 flex-1 overflow-y-auto">
    <div class="mx-auto w-full max-w-400 p-5">
      <RouterView />
    </div>
  </main>
</template>
