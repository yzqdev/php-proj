<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { ElMessage } from 'element-plus'
import { Download, Lock } from '@element-plus/icons-vue'
import {
  getShareDownloadUrl,
  getShareStreamUrl,
  getShareZipUrl,
  shareInfo,
} from '@/api/clouddrive'
import type { ShareInfo } from '@/api/clouddrive'

/**
 * 分享访客页（免登录）：/share/:token
 * 展示分享文件信息；设置了提取密码的分享先输密码，然后可下载/打包/预览。
 */
const route = useRoute()

const token = computed(() => String(route.params.token ?? ''))
const loading = ref(true)
const needPwd = ref(false)
const pwdInput = ref('')
const info = ref<ShareInfo | null>(null)

const isPreviewable = computed(() => {
  const name = info.value?.name ?? ''
  const ext = name.split('.').pop()?.toLowerCase() ?? ''
  return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'mp4', 'webm', 'pdf'].includes(ext)
})

function download() {
  if (!info.value) return
  const url = info.value.is_dir
    ? getShareZipUrl(token.value, pwdInput.value)
    : getShareDownloadUrl(token.value, pwdInput.value)
  window.open(url, '_blank')
}

function preview() {
  window.open(getShareStreamUrl(token.value, pwdInput.value), '_blank')
}

async function load(pwd: string) {
  loading.value = true
  try {
    const { data: res } = await shareInfo(token.value, pwd)
    if (res.data.need_pwd) {
      needPwd.value = true
      if (pwd !== '') {
        ElMessage.error('提取密码错误')
      }
    } else {
      info.value = res.data
      needPwd.value = false
    }
  } finally {
    loading.value = false
  }
}

function submitPwd() {
  load(pwdInput.value)
}

onMounted(() => load(''))
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-[#667eea] to-[#764ba2]">
    <div class="flex min-h-screen items-center justify-center px-5">
      <div class="w-full max-w-[440px] rounded-2xl bg-white/95 p-8 shadow-2xl backdrop-blur-md">
        <!-- 密码输入 -->
        <div v-if="needPwd">
          <div class="mb-4 text-center">
            <span class="mx-auto mb-3 grid size-12 place-items-center rounded-full bg-[#667eea]/10 text-[#667eea]">
              <el-icon :size="22"><Lock /></el-icon>
            </span>
            <p class="text-base font-semibold text-gray-800">该分享已加密</p>
            <p class="mt-1 text-sm text-gray-400">请输入提取密码查看文件</p>
          </div>
          <el-input
            v-model="pwdInput"
            size="large"
            placeholder="提取密码"
            @keyup.enter="submitPwd"
          />
          <el-button
            type="primary"
            size="large"
            class="mt-4 w-full"
            :loading="loading"
            @click="submitPwd"
          >
            解 锁
          </el-button>
        </div>

        <!-- 文件信息 -->
        <div v-else-if="info">
          <p class="text-center text-xs text-gray-400">slim-lab · 文件分享</p>
          <div class="mt-3 rounded-xl bg-gray-50 p-4">
            <p class="break-all text-base font-semibold text-gray-800">
              {{ info.is_dir ? '📁' : '📄' }} {{ info.name }}
            </p>
            <p class="mt-2 text-sm text-gray-500">
              类型：{{ info.is_dir ? '文件夹（打包为 zip 下载）' : '文件' }}
            </p>
            <p class="mt-1 text-sm text-gray-500">有效期：{{ info.expire_text }}</p>
          </div>
          <div class="mt-5 flex gap-3">
            <el-button type="primary" size="large" class="flex-1" :icon="Download" @click="download">
              {{ info.is_dir ? '打包下载' : '下载文件' }}
            </el-button>
            <el-button v-if="isPreviewable" size="large" @click="preview">在线预览</el-button>
          </div>
        </div>

        <!-- 加载中 -->
        <p v-else class="py-8 text-center text-sm text-gray-400">
          {{ loading ? '加载中…' : '分享信息不可用' }}
        </p>
      </div>
    </div>
  </div>
</template>
