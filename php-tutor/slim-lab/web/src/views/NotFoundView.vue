<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>

<template>
  <div class="py-20 text-center">
    <h1 class="text-8xl font-bold text-white opacity-30">404</h1>
    <p class="mt-5 text-xl text-white">页面不存在</p>
    <RouterLink
      to="/"
      class="mt-8 inline-block rounded-lg bg-white px-8 py-3 font-semibold text-[#667eea] transition hover:scale-105"
    >
      返回首页
    </RouterLink>
  </div>
</template>
