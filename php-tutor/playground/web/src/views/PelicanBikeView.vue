<script setup lang="ts">
import PelicanBikeScene from '@/components/PelicanBikeScene.vue'

const paused = ref(false)
const sceneKey = ref(0)

const togglePlay = () => {
  paused.value = !paused.value
}

// 用 key 变化强制重挂载整棵 SVG,动画回到第 0 帧;单纯改 animation-play-state
// 无法"从头再播"。
const restart = () => {
  paused.value = false
  sceneKey.value += 1
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-b from-sky-100 via-sky-50 to-slate-100 px-4 py-8 sm:py-12">
    <div class="mx-auto w-full max-w-4xl">
      <!-- 标题区 -->
      <header class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
          <router-link
            to="/"
            class="mb-2 inline-flex items-center gap-1 text-sm text-sky-600 transition-colors hover:text-sky-800"
          >
            <svg viewBox="0 0 16 16" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M10 3 5 8l5 5" />
            </svg>
            返回图床
          </router-link>
          <h1 class="text-2xl font-bold tracking-tight text-slate-800 sm:text-3xl">Pelican on a Bike</h1>
          <p class="mt-1.5 text-sm text-slate-500 sm:text-base">一只不肯上岸的鹈鹕,正在认真蹬车。</p>
        </div>

        <!-- 控制区 -->
        <div class="flex items-center gap-2">
          <el-button-group>
            <el-button :type="paused ? 'primary' : 'default'" @click="togglePlay">
              <span class="inline-flex items-center gap-1.5">
                <svg v-if="!paused" viewBox="0 0 16 16" class="h-4 w-4" fill="currentColor">
                  <rect x="3.5" y="2.5" width="3.4" height="11" rx="1" />
                  <rect x="9.1" y="2.5" width="3.4" height="11" rx="1" />
                </svg>
                <svg v-else viewBox="0 0 16 16" class="h-4 w-4" fill="currentColor">
                  <path d="M4.6 2.8c0-.8.9-1.3 1.6-.9l7 4.3c.7.4.7 1.4 0 1.8l-7 4.3c-.7.4-1.6-.1-1.6-.9V2.8Z" />
                </svg>
                {{ paused ? '播放' : '暂停' }}
              </span>
            </el-button>
            <el-button @click="restart">
              <span class="inline-flex items-center gap-1.5">
                <svg viewBox="0 0 16 16" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M13.5 8a5.5 5.5 0 1 1-1.6-3.9" />
                  <path d="M13.7 2.6v3.2h-3.2" />
                </svg>
                重新开始
              </span>
            </el-button>
          </el-button-group>
        </div>
      </header>

      <!-- 插画主体 -->
      <section class="overflow-hidden rounded-2xl bg-white shadow-xl shadow-sky-200/60 ring-1 ring-slate-200/70">
        <PelicanBikeScene :key="sceneKey" :paused="paused" />
      </section>

      <!-- 说明区 -->
      <footer class="mt-6 grid gap-3 sm:grid-cols-3">
        <div class="rounded-xl bg-white/70 p-4 ring-1 ring-slate-200/70 backdrop-blur">
          <p class="text-xs font-semibold uppercase tracking-wider text-sky-600">纯矢量</p>
          <p class="mt-1.5 text-sm leading-relaxed text-slate-600">鹈鹕和自行车全部由 SVG 路径手绘,没有一张位图,缩到多大都不糊。</p>
        </div>
        <div class="rounded-xl bg-white/70 p-4 ring-1 ring-slate-200/70 backdrop-blur">
          <p class="text-xs font-semibold uppercase tracking-wider text-sky-600">脚真的踩在踏板上</p>
          <p class="mt-1.5 text-sm leading-relaxed text-slate-600">曲柄每转 45° 用两连杆逆运动学解一次腿的角度,脚不会脱离踏板,左右腿错开半个周期交替蹬。</p>
        </div>
        <div class="rounded-xl bg-white/70 p-4 ring-1 ring-slate-200/70 backdrop-blur">
          <p class="text-xs font-semibold uppercase tracking-wider text-sky-600">一套主节奏</p>
          <p class="mt-1.5 text-sm leading-relaxed text-slate-600">轮子、曲柄、腿部、车身起伏和路面虚线共用 1.1 秒这个节拍,所以整幅画看起来是在动,而不是各自在抖。</p>
        </div>
      </footer>
    </div>
  </div>
</template>
