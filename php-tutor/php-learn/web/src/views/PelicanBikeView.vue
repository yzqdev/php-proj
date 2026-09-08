<script setup lang="ts">
/**
 * ============================================================
 * 鹈鹕骑自行车 — 页面组件
 * ============================================================
 *
 * 展示 PelicanBikeSvg 动态插画的独立页面。
 * 包含标题、描述、动画播放/暂停控制按钮。
 *
 * 使用 Element Plus 组件 + TailwindCSS 工具类实现页面布局。
 * 不依赖任何 API 或认证（meta: { public: true }）。
 */
import { ref } from 'vue'
import PelicanBikeSvg from '@/components/PelicanBikeSvg.vue'

/** 动画是否暂停 */
const isPaused = ref(false)

/** 动画 key（用于强制重置动画） */
const animKey = ref(0)

/** 切换播放/暂停 */
function togglePlayPause(): void {
  isPaused.value = !isPaused.value
}

/** 重新开始动画（通过切换 key 强制重建 SVG DOM） */
function restartAnimation(): void {
  animKey.value++
  isPaused.value = false
}
</script>

<template>
  <div class="pelican-bike-page">
    <!-- 页面标题区 -->
    <div class="page-header">
      <h1 class="page-title">
        🐦 Pelican on a Bike
      </h1>
      <p class="page-desc">
        一只快乐的鹈鹕正在悠闲地骑自行车穿越阳光明媚的午后
      </p>
    </div>

    <!-- SVG 插画容器 -->
    <div class="svg-container">
      <div class="svg-wrapper">
        <PelicanBikeSvg
          :key="animKey"
          :paused="isPaused"
        />
      </div>
    </div>

    <!-- 动画控制按钮 -->
    <div class="controls">
      <el-button
        :type="isPaused ? 'primary' : 'default'"
        size="large"
        round
        @click="togglePlayPause"
      >
        <span v-if="isPaused" class="btn-icon">▶</span>
        <span v-else class="btn-icon">⏸</span>
        {{ isPaused ? '播放' : '暂停' }}
      </el-button>
      <el-button
        size="large"
        round
        @click="restartAnimation"
      >
        <span class="btn-icon">🔄</span>
        重新开始
      </el-button>
    </div>

    <!-- 底部信息 -->
    <div class="page-footer">
      <p class="footer-text">
        纯 SVG + CSS Animation · 无位图 · 无 GIF
      </p>
    </div>
  </div>
</template>

<style scoped>
/* ============================================================
 * 页面布局
 * ============================================================
 * 全屏展示，居中对齐，柔和渐变背景。
 * SVG 插画占据视觉中心，控制按钮简洁不抢眼。
 */

.pelican-bike-page {
  min-height: calc(100vh - 120px);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 24px;
  padding: 32px 16px;
  background: linear-gradient(180deg, #E3F2FD 0%, #BBDEFB 40%, #E8F5E9 100%);
}

/* ---- 标题区 ---- */
.page-header {
  text-align: center;
}

.page-title {
  margin: 0 0 8px 0;
  font-size: 2.2rem;
  font-weight: 700;
  color: #1a1a2e;
  letter-spacing: -0.02em;
}

.page-desc {
  margin: 0;
  font-size: 1rem;
  color: #546e7a;
  max-width: 420px;
}

/* ---- SVG 容器 ---- */
.svg-container {
  width: 100%;
  max-width: 800px;
}

.svg-wrapper {
  width: 100%;
  border-radius: 16px;
  overflow: hidden;
  box-shadow:
    0 4px 24px rgba(0, 0, 0, 0.08),
    0 1px 4px rgba(0, 0, 0, 0.04);
  background: white;
}

/* ---- 控制按钮 ---- */
.controls {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  justify-content: center;
}

.btn-icon {
  margin-right: 4px;
}

/* ---- 底部信息 ---- */
.page-footer {
  margin-top: 8px;
}

.footer-text {
  margin: 0;
  font-size: 0.8rem;
  color: #90a4ae;
  letter-spacing: 0.02em;
}

/* ============================================================
 * 响应式适配
 * ============================================================ */
@media (max-width: 640px) {
  .pelican-bike-page {
    padding: 20px 12px;
    gap: 16px;
  }

  .page-title {
    font-size: 1.6rem;
  }

  .page-desc {
    font-size: 0.9rem;
  }

  .svg-wrapper {
    border-radius: 10px;
  }
}
</style>
