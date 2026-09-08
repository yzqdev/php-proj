<script setup lang="ts">
</script>

<template>
  <!--
    唯一职责是承载路由视图。单独拆出来而不是直接改 App.vue,
    是为了让 App.vue(图库+日志)保持零改动。
  -->
  <router-view />
</template>
