<script setup lang="ts">
import { imageApi, resolveUrl } from '@/api/images'
import type { ImageItem } from '@/types/image'
import { copyText, formatDateTime, formatSize } from '@/utils/format'

const images = ref<ImageItem[]>([])
const loading = ref(false)

async function loadList(): Promise<void> {
  loading.value = true
  try {
    images.value = await imageApi.list()
  } catch (error) {
    ElMessage.error(error instanceof Error ? error.message : '列表加载失败')
  } finally {
    loading.value = false
  }
}

/** el-upload 的自定义上传:走统一 API 层,成功后插入到列表头部 */
async function handleUpload(options: { file: File }): Promise<void> {
  try {
    const image = await imageApi.upload(options.file)
    images.value.unshift(image)
    ElMessage.success(`上传成功:${image.original}`)
  } catch (error) {
    ElMessage.error(error instanceof Error ? error.message : '上传失败')
  }
}

/** Ctrl+V 粘贴截图:剪贴板里的图片直接上传 */
async function handlePaste(event: ClipboardEvent): Promise<void> {
  const files = Array.from(event.clipboardData?.items ?? [])
    .filter((item) => item.kind === 'file' && item.type.startsWith('image/'))
    .map((item) => item.getAsFile())
    .filter((file): file is File => file !== null)
  for (const file of files) {
    await handleUpload({ file })
  }
}

async function handleCopy(image: ImageItem): Promise<void> {
  const ok = await copyText(resolveUrl(image.url))
  if (ok) {
    ElMessage.success('外链已复制')
  } else {
    ElMessage.error('复制失败,请手动复制')
  }
}

async function handleDelete(image: ImageItem): Promise<void> {
  const confirmed = await ElMessageBox.confirm(
    `确定删除「${image.original}」吗?此操作不可恢复。`,
    '删除图片',
    { type: 'warning', confirmButtonText: '删除', cancelButtonText: '取消' },
  ).then(() => true, () => false)
  if (!confirmed) {
    return
  }
  try {
    await imageApi.remove(image.name)
    images.value = images.value.filter((item) => item.name !== image.name)
    ElMessage.success('已删除')
  } catch (error) {
    ElMessage.error(error instanceof Error ? error.message : '删除失败')
  }
}

onMounted(() => {
  loadList()
  document.addEventListener('paste', handlePaste)
})

onUnmounted(() => {
  document.removeEventListener('paste', handlePaste)
})
</script>

<template>
  <!-- 上传区:拖拽 / 点选,分多张并发走自定义上传 -->
  <el-upload
    drag
    multiple
    :show-file-list="false"
    :http-request="handleUpload"
    accept="image/*"
    class="mb-6"
  >
    <div class="py-6">
      <p class="text-base font-semibold text-gray-700">拖拽图片到这里,或点击选择文件</p>
      <p class="mt-1 text-sm text-gray-400">也可以直接 Ctrl+V 粘贴截图,上传后自动生成外链</p>
    </div>
  </el-upload>

  <!-- 画廊:缩略图网格,点击可大图预览 -->
  <div v-loading="loading" class="min-h-40">
    <el-empty v-if="!loading && images.length === 0" description="还没有图片,上传一张吧" />

    <div v-else class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
      <div
        v-for="image in images"
        :key="image.name"
        class="overflow-hidden rounded-lg border border-gray-200 bg-white transition hover:shadow-md"
      >
        <el-image
          :src="resolveUrl(image.url)"
          :preview-src-list="images.map((item) => resolveUrl(item.url))"
          :initial-index="images.indexOf(image)"
          fit="cover"
          loading="lazy"
          class="block h-36 w-full cursor-zoom-in bg-gray-100"
        />
        <div class="space-y-2 p-3">
          <el-tooltip :content="image.original" placement="top" :show-after="300">
            <p class="truncate text-sm font-medium">{{ image.original }}</p>
          </el-tooltip>
          <p class="text-xs text-gray-400">
            {{ formatSize(image.size) }} · {{ formatDateTime(image.time) }}
          </p>
          <div class="flex flex-wrap gap-2">
            <el-button size="small" @click="handleCopy(image)">复制外链</el-button>
            <a :href="resolveUrl(`/download/${encodeURIComponent(image.name)}`)">
              <el-button size="small">下载</el-button>
            </a>
            <el-button size="small" type="danger" plain @click="handleDelete(image)">删除</el-button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
