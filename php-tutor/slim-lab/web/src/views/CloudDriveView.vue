<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  clouddriveLogin,
  clouddriveLogout,
  clouddriveCheck,
  clouddriveListDir,
  clouddriveMkdir,
  clouddriveUpload,
  clouddriveDelete,
  clouddriveBatchDelete,
  clouddriveRename,
  clouddriveMove,
  clouddriveSearch,
  clouddriveCreateShare,
  clouddriveGetShareList,
  clouddriveDeleteShare,
  clouddriveChangePwd,
  getStreamUrl,
  getDownloadUrl,
  getZipUrl,
  buildShareUrl,
} from '@/api/clouddrive'
import type { CloudDriveFile, CloudDriveShare, SearchResult } from '@/types/api'

const isLoggedIn = ref(false)
const loading = ref(false)
const loginPwd = ref('')
const currentFolder = ref('')
const fileList = ref<CloudDriveFile[]>([])
const allDirs = ref<string[]>([])
const searchResults = ref<SearchResult[]>([])
const searchKeyword = ref('')
const showSearch = ref(false)

const selectedFiles = ref<string[]>([])
const sortBy = ref('')
const sortOrder = ref('')

const showShareDialog = ref(false)
const shareFile = ref('')
const shareIsDir = ref(false)
const sharePwd = ref('')
const shareExpire = ref('0')
const shareUrl = ref('')

const showShareListDialog = ref(false)
const shareList = ref<CloudDriveShare[]>([])

const showRenameDialog = ref(false)
const renameOld = ref('')
const renameNew = ref('')

const showMoveDialog = ref(false)
const moveSrc = ref('')
const moveDst = ref('')

const showMkdirDialog = ref(false)
const mkdirName = ref('')

const showChangePwdDialog = ref(false)
const newPwd1 = ref('')
const newPwd2 = ref('')

const showPreviewDialog = ref(false)
const previewUrl = ref('')
const previewType = ref<'image' | 'video' | 'pdf'>('image')

async function checkLogin() {
  try {
    const { data: res } = await clouddriveCheck()
    isLoggedIn.value = res.data?.login ?? false
  } catch {
    isLoggedIn.value = false
  }
}

async function handleLogin() {
  if (!loginPwd.value) {
    ElMessage.warning('请输入密码')
    return
  }
  try {
    await clouddriveLogin(loginPwd.value)
    isLoggedIn.value = true
    loginPwd.value = ''
    loadDir()
  } catch {
    // error handled by interceptor
  }
}

async function handleLogout() {
  await clouddriveLogout()
  isLoggedIn.value = false
}

async function loadDir() {
  loading.value = true
  try {
    const { data: res } = await clouddriveListDir(
      currentFolder.value,
      sortBy.value,
      sortOrder.value,
    )
    if (res.data) {
      fileList.value = res.data.list
      allDirs.value = res.data.dirs
    }
  } finally {
    loading.value = false
  }
}

function enterDir(name: string) {
  currentFolder.value = currentFolder.value
    ? `${currentFolder.value}/${name}`
    : name
  loadDir()
}

function goToRoot() {
  currentFolder.value = ''
  loadDir()
}

function navigateTo(index: number) {
  const parts = currentFolder.value.split('/')
  currentFolder.value = parts.slice(0, index + 1).join('/')
  loadDir()
}

function toggleSort(field: string) {
  if (sortBy.value === field) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortBy.value = field
    sortOrder.value = 'asc'
  }
  loadDir()
}

function toggleSelectAll(val: string | number | boolean) {
  const checked = Boolean(val)
  selectedFiles.value = checked ? fileList.value.map((f) => f.name) : []
}

function toggleSelect(name: string) {
  const idx = selectedFiles.value.indexOf(name)
  if (idx === -1) {
    selectedFiles.value.push(name)
  } else {
    selectedFiles.value.splice(idx, 1)
  }
}

async function handleMkdir() {
  if (!mkdirName.value.trim()) {
    ElMessage.warning('目录名不能为空')
    return
  }
  await clouddriveMkdir(currentFolder.value, mkdirName.value)
  ElMessage.success('创建成功')
  showMkdirDialog.value = false
  mkdirName.value = ''
  loadDir()
}

async function handleUploadFiles(files: FileList | File[] | null) {
  if (!files || files.length === 0) return
  for (let i = 0; i < files.length; i++) {
    const file = files[i]
    if (!file) continue
    try {
      await clouddriveUpload(currentFolder.value, file)
      ElMessage.success(`上传成功: ${file.name}`)
    } catch {
      ElMessage.error(`上传失败: ${file.name}`)
    }
  }
  loadDir()
}

async function handleDrop(e: DragEvent) {
  e.preventDefault()
  const items = e.dataTransfer?.items
  if (items && items.length > 0) {
    for (let i = 0; i < items.length; i++) {
      const item = items[i]
      if (!item) continue
      const entry = item.webkitGetAsEntry?.()
      if (entry) {
        await processEntry(entry, currentFolder.value)
      }
    }
    loadDir()
  } else if (e.dataTransfer?.files) {
    handleUploadFiles(e.dataTransfer.files)
  }
}

async function processEntry(entry: FileSystemEntry, parentPath: string) {
  if (entry.isFile) {
    const fileEntry = entry as FileSystemFileEntry
    const file = await new Promise<File>((resolve) => fileEntry.file(resolve))
    await clouddriveUpload(parentPath, file)
  } else if (entry.isDirectory) {
    const dirEntry = entry as FileSystemDirectoryEntry
    const dirName = dirEntry.name
    const dirPath = parentPath ? `${parentPath}/${dirName}` : dirName
    await clouddriveMkdir(currentFolder.value, dirPath)
    const reader = dirEntry.createReader()
    const entries = await new Promise<FileSystemEntry[]>((resolve) => {
      const allEntries: FileSystemEntry[] = []
      function readBatch() {
        reader.readEntries((batch) => {
          if (batch.length === 0) {
            resolve(allEntries)
          } else {
            allEntries.push(...batch)
            readBatch()
          }
        })
      }
      readBatch()
    })
    for (const e of entries) {
      await processEntry(e, dirPath)
    }
  }
}

async function handleDelete(name: string) {
  try {
    await ElMessageBox.confirm(`确定删除 "${name}"？`, '确认删除', {
      type: 'warning',
    })
    await clouddriveDelete(currentFolder.value, name)
    ElMessage.success('删除成功')
    loadDir()
  } catch {
    // cancelled
  }
}

async function handleBatchDelete() {
  if (selectedFiles.value.length === 0) {
    ElMessage.warning('请先选择文件')
    return
  }
  try {
    await ElMessageBox.confirm(
      `确定删除选中的 ${selectedFiles.value.length} 个文件？`,
      '批量删除',
      { type: 'warning' },
    )
    await clouddriveBatchDelete(currentFolder.value, selectedFiles.value)
    ElMessage.success('批量删除成功')
    selectedFiles.value = []
    loadDir()
  } catch {
    // cancelled
  }
}

function openRename(name: string) {
  renameOld.value = name
  renameNew.value = name
  showRenameDialog.value = true
}

async function handleRename() {
  if (!renameNew.value.trim()) {
    ElMessage.warning('名称不能为空')
    return
  }
  await clouddriveRename(currentFolder.value, renameOld.value, renameNew.value)
  ElMessage.success('重命名成功')
  showRenameDialog.value = false
  loadDir()
}

function openMove(name: string) {
  moveSrc.value = name
  moveDst.value = ''
  showMoveDialog.value = true
}

async function handleMove() {
  await clouddriveMove(currentFolder.value, moveSrc.value, moveDst.value)
  ElMessage.success('移动成功')
  showMoveDialog.value = false
  loadDir()
}

async function handleSearch() {
  if (!searchKeyword.value.trim()) {
    showSearch.value = false
    return
  }
  try {
    const { data: res } = await clouddriveSearch(searchKeyword.value)
    searchResults.value = res.data?.list ?? []
    showSearch.value = true
  } catch {
    // handled
  }
}

function closeSearch() {
  showSearch.value = false
  searchResults.value = []
  searchKeyword.value = ''
}

function copyToClipboard(text: string) {
  navigator.clipboard.writeText(text)
  ElMessage.success('已复制')
}

function openShare(name: string, isDir: boolean) {
  shareFile.value = name
  shareIsDir.value = isDir
  sharePwd.value = Math.random().toString(36).substring(2, 8)
  shareExpire.value = '0'
  shareUrl.value = ''
  showShareDialog.value = true
}

async function handleCreateShare() {
  try {
    const { data: res } = await clouddriveCreateShare({
      current_folder: currentFolder.value,
      share_file: shareFile.value,
      is_dir_share: shareIsDir.value ? '1' : '0',
      share_pwd: sharePwd.value,
      share_expire: shareExpire.value,
    })
    // 后端返回的站点根 URL 指向 API 主机；改写为前端访客页路由（未登录可打开）
    const token = res.data?.token ?? ''
    shareUrl.value = token === '' ? '' : buildShareUrl(token)
    ElMessage.success('分享创建成功')
  } catch {
    // handled
  }
}

async function openShareList() {
  showShareListDialog.value = true
  try {
    const { data: res } = await clouddriveGetShareList()
    shareList.value = res.data?.list ?? []
  } catch {
    // handled
  }
}

async function handleDeleteShare(token: string) {
  try {
    await ElMessageBox.confirm('确定删除该分享链接？', '确认', { type: 'warning' })
    await clouddriveDeleteShare(token)
    ElMessage.success('删除成功')
    openShareList()
  } catch {
    // cancelled
  }
}

function handleOpenChangePwd() {
  newPwd1.value = ''
  newPwd2.value = ''
  showChangePwdDialog.value = true
}

async function handleChangePwd() {
  if (newPwd1.value !== newPwd2.value) {
    ElMessage.error('两次密码不一致')
    return
  }
  try {
    await clouddriveChangePwd(newPwd1.value, newPwd2.value)
    ElMessage.success('密码修改成功，请重新登录')
    showChangePwdDialog.value = false
    isLoggedIn.value = false
  } catch {
    // handled
  }
}

function openPreview(name: string) {
  const filePath = currentFolder.value
    ? `${currentFolder.value}/${name}`
    : name
  previewUrl.value = getStreamUrl(filePath)
  const ext = name.split('.').pop()?.toLowerCase() || ''
  if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'].includes(ext)) {
    previewType.value = 'image'
  } else if (['mp4', 'mov', 'webm'].includes(ext)) {
    previewType.value = 'video'
  } else if (ext === 'pdf') {
    previewType.value = 'pdf'
  }
  showPreviewDialog.value = true
}

function handleDownload(name: string) {
  const url = getDownloadUrl(currentFolder.value, name)
  window.open(url, '_blank')
}

function handleZipDownload(name: string) {
  const url = getZipUrl(currentFolder.value, name)
  window.open(url, '_blank')
}

onMounted(async () => {
  await checkLogin()
  // 会话仍有效时（如登录过、Cookie 未过期），进入页面即自动加载文件列表，
  // 修复"已登录但默认不显示任何文件"的问题（此前 loadDir 仅在手动登录后触发）
  if (isLoggedIn.value) {
    loadDir()
  }
})
</script>

<template>
  <div>
    <div v-if="!isLoggedIn" class="mx-auto max-w-85">
      <div
        class="rounded-xl bg-white/92 p-9 shadow-lg backdrop-blur-md"
      >
        <h2 class="mb-6 text-center text-xl font-bold text-gray-800">集团内部网盘</h2>
        <el-input
          v-model="loginPwd"
          type="password"
          placeholder="访问密码"
          show-password
          @keyup.enter="handleLogin"
        />
        <el-button type="primary" class="mt-4 w-full" @click="handleLogin">
          登录
        </el-button>
      </div>
    </div>

    <div v-else class="space-y-4">
      <div
        class="flex items-center justify-between rounded-xl bg-linear-to-r from-[#667eea] to-[#764ba2] p-4 text-white"
      >
        <h1 class="text-xl font-bold">集团内部网盘</h1>
        <div class="space-x-2">
          <el-button size="small" @click="openShareList">分享管理</el-button>
          <el-button size="small" @click="handleOpenChangePwd">修改密码</el-button>
          <el-button size="small" type="danger" @click="handleLogout">退出登录</el-button>
        </div>
      </div>

      <div class="flex gap-3 rounded-xl bg-white p-3.5 shadow-sm">
        <el-input
          v-model="searchKeyword"
          placeholder="全局搜索所有目录文件名/文件夹名"
          @keyup.enter="handleSearch"
        />
        <el-button type="primary" @click="handleSearch">搜索</el-button>
        <el-button v-if="showSearch" @click="closeSearch">关闭搜索</el-button>
      </div>

      <div class="rounded-lg bg-white px-4 py-3 text-sm shadow-sm">
        <el-breadcrumb separator="/">
          <el-breadcrumb-item>
            <a href="javascript:void(0)" @click="goToRoot">根目录</a>
          </el-breadcrumb-item>
          <el-breadcrumb-item
            v-for="(part, index) in currentFolder.split('/').filter(Boolean)"
            :key="index"
          >
            <a href="javascript:void(0)" @click="navigateTo(index)">{{ part }}</a>
          </el-breadcrumb-item>
        </el-breadcrumb>
      </div>

      <div
        class="rounded-xl bg-white p-5 shadow-sm"
        @dragover.prevent
        @drop="handleDrop"
      >
        <h3 class="mb-3 text-base font-semibold text-gray-800">文件/文件夹上传管理</h3>
        <p class="mb-2 text-xs text-gray-400">
          拖拽多个文件夹/文件批量上传
        </p>
        <div
          class="mb-3 cursor-pointer rounded-lg border-2 border-dashed border-[#667eea] p-5 text-center text-[#667eea] transition hover:bg-[#667eea]/5"
        >
          拖拽文件到此处上传
        </div>
        <div class="flex flex-wrap items-center gap-3">
          <el-upload
            :show-file-list="false"
            multiple
            :auto-upload="false"
            @change="(file: any) => { if (file?.raw) handleUploadFiles([file.raw]) }"
          >
            <el-button>选择多个文件</el-button>
          </el-upload>
          <el-button @click="showMkdirDialog = true">新建文件夹</el-button>
          <el-button
            v-if="selectedFiles.length > 0"
            type="danger"
            @click="handleBatchDelete"
          >
            批量删除 ({{ selectedFiles.length }})
          </el-button>
        </div>
      </div>

      <el-table
        v-if="!showSearch"
        :data="fileList"
        v-loading="loading"
        class="rounded-xl"
        row-key="name"
      >
        <el-table-column width="50">
          <template #header>
            <el-checkbox
              :model-value="selectedFiles.length === fileList.length && fileList.length > 0"
              @change="toggleSelectAll"
            />
          </template>
          <template #default="{ row }">
            <el-checkbox
              :model-value="selectedFiles.includes(row.name)"
              @change="() => toggleSelect(row.name)"
            />
          </template>
        </el-table-column>
        <el-table-column label="名称" min-width="300">
          <template #header>
            <a
              href="javascript:void(0)"
              class="text-gray-800 no-underline"
              @click="toggleSort('name')"
            >
              名称
              <span v-if="sortBy === 'name'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
            </a>
          </template>
          <template #default="{ row }">
            <div v-if="row.is_dir">
              <el-link type="primary" @click="enterDir(row.name)">
                📁 {{ row.name }}
              </el-link>
            </div>
            <div v-else>📄 {{ row.name }}</div>
          </template>
        </el-table-column>
        <el-table-column label="大小" width="120">
          <template #default="{ row }">{{ row.size_text }}</template>
        </el-table-column>
        <el-table-column label="修改时间" width="160">
          <template #header>
            <a
              href="javascript:void(0)"
              class="text-gray-800 no-underline"
              @click="toggleSort('mtime')"
            >
              修改时间
              <span v-if="sortBy === 'mtime'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
            </a>
          </template>
          <template #default="{ row }">{{ row.mtime }}</template>
        </el-table-column>
        <el-table-column label="操作" width="420">
          <template #default="{ row }">
            <div class="flex flex-wrap gap-1">
              <template v-if="row.is_dir">
                <el-button size="small" type="success" @click="enterDir(row.name)">进入</el-button>
                <el-button size="small" @click="handleZipDownload(row.name)">打包</el-button>
                <el-button size="small" type="warning" @click="openShare(row.name, true)">分享</el-button>
              </template>
              <template v-else>
                <el-button size="small" type="primary" @click="handleDownload(row.name)">下载</el-button>
                <el-button
                  v-if="['jpg','jpeg','png','gif','webp','bmp'].includes(row.ext)"
                  size="small"
                  type="info"
                  @click="openPreview(row.name)"
                >
                  看图
                </el-button>
                <el-button
                  v-if="['mp4','mov','webm'].includes(row.ext)"
                  size="small"
                  type="warning"
                  @click="openPreview(row.name)"
                >
                  播放
                </el-button>
                <el-button
                  v-if="row.ext === 'pdf'"
                  size="small"
                  type="danger"
                  @click="openPreview(row.name)"
                >
                  PDF
                </el-button>
                <el-button size="small" @click="openShare(row.name, false)">分享</el-button>
              </template>
              <el-button size="small" @click="openRename(row.name)">重命名</el-button>
              <el-button size="small" @click="openMove(row.name)">移动</el-button>
              <el-button size="small" type="danger" @click="handleDelete(row.name)">删除</el-button>
            </div>
          </template>
        </el-table-column>
      </el-table>

      <el-table
        v-if="showSearch"
        :data="searchResults"
        class="rounded-xl"
        row-key="full_rel"
      >
        <el-table-column label="名称" min-width="300">
          <template #default="{ row }">
            <div>{{ row.is_dir ? '📁' : '📄' }} {{ row.name }}</div>
            <div class="text-xs text-gray-400">完整路径：{{ row.full_rel }}</div>
          </template>
        </el-table-column>
        <el-table-column label="大小" width="120">
          <template #default="{ row }">
            {{ row.is_dir ? '文件夹' : row.size + ' B' }}
          </template>
        </el-table-column>
        <el-table-column label="所在目录" width="160">
          <template #default="{ row }">{{ row.parent_dir || '根目录' }}</template>
        </el-table-column>
        <el-table-column label="操作" width="200">
          <template #default="{ row }">
            <el-button
              v-if="row.is_dir"
              size="small"
              type="success"
              @click="showSearch = false; enterDir(row.full_rel)"
            >
              进入目录
            </el-button>
            <el-button
              v-else
              size="small"
              type="primary"
              @click="handleDownload(row.full_rel)"
            >
              下载
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <el-dialog v-model="showMkdirDialog" title="新建文件夹" width="400px">
        <el-input v-model="mkdirName" placeholder="文件夹名称" @keyup.enter="handleMkdir" />
        <template #footer>
          <el-button @click="showMkdirDialog = false">取消</el-button>
          <el-button type="primary" @click="handleMkdir">创建</el-button>
        </template>
      </el-dialog>

      <el-dialog v-model="showRenameDialog" title="重命名" width="400px">
        <el-input v-model="renameNew" placeholder="新名称" @keyup.enter="handleRename" />
        <template #footer>
          <el-button @click="showRenameDialog = false">取消</el-button>
          <el-button type="primary" @click="handleRename">确认</el-button>
        </template>
      </el-dialog>

      <el-dialog v-model="showMoveDialog" title="移动文件" width="400px">
        <div class="mb-2 text-sm text-gray-500">移动: {{ moveSrc }}</div>
        <el-select v-model="moveDst" placeholder="选择目标目录" class="w-full">
          <el-option label="根目录" value="" />
          <el-option v-for="d in allDirs" :key="d" :label="d" :value="d" />
        </el-select>
        <template #footer>
          <el-button @click="showMoveDialog = false">取消</el-button>
          <el-button type="primary" @click="handleMove">确认移动</el-button>
        </template>
      </el-dialog>

      <el-dialog v-model="showShareDialog" title="生成带密码分享" width="500px">
        <el-form label-width="80px">
          <el-form-item label="文件">{{ shareFile }}</el-form-item>
          <el-form-item label="提取密码">
            <el-input v-model="sharePwd" />
          </el-form-item>
          <el-form-item label="有效期">
            <el-select v-model="shareExpire" class="w-full">
              <el-option label="永久有效" value="0" />
              <el-option label="1小时" value="1" />
              <el-option label="6小时" value="6" />
              <el-option label="24小时" value="24" />
              <el-option label="3天" value="72" />
              <el-option label="7天" value="168" />
            </el-select>
          </el-form-item>
        </el-form>
        <div v-if="shareUrl" class="mt-4 rounded-lg bg-gray-50 p-3 text-sm">
          <div class="mb-1">分享链接：{{ shareUrl }}</div>
          <div>提取密码：{{ sharePwd }}</div>
        </div>
        <template #footer>
          <el-button v-if="!shareUrl" @click="showShareDialog = false">取消</el-button>
          <el-button v-if="!shareUrl" type="primary" @click="handleCreateShare">生成链接</el-button>
          <el-button v-else @click="showShareDialog = false">关闭</el-button>
        </template>
      </el-dialog>

      <el-dialog v-model="showShareListDialog" title="全部分享管理" width="700px">
        <el-table :data="shareList" class="w-full">
          <el-table-column label="分享对象" prop="name" />
          <el-table-column label="类型" width="80">
            <template #default="{ row }">{{ row.is_dir ? '文件夹' : '文件' }}</template>
          </el-table-column>
          <el-table-column label="链接" min-width="200">
            <template #default="{ row }">
              <div class="flex items-center gap-2">
                <!-- 后端拼的是 API 主机旧链接，统一改写为前端访客页路由 -->
                <span class="truncate text-xs">{{ buildShareUrl(row.token) }}</span>
                <el-button size="small" @click="copyToClipboard(buildShareUrl(row.token))">复制</el-button>
              </div>
            </template>
          </el-table-column>
          <el-table-column label="密码" width="80" prop="pwd" />
          <el-table-column label="有效期" width="140">
            <template #default="{ row }">
              <span :class="row.is_expire ? 'text-red-500 font-bold' : 'text-green-600'">
                {{ row.expire_text }} {{ row.is_expire ? '(已过期)' : '' }}
              </span>
            </template>
          </el-table-column>
          <el-table-column label="操作" width="80">
            <template #default="{ row }">
              <el-button size="small" type="danger" @click="handleDeleteShare(row.token)">
                删除
              </el-button>
            </template>
          </el-table-column>
        </el-table>
      </el-dialog>

      <el-dialog v-model="showChangePwdDialog" title="修改登录密码" width="400px">
        <el-form label-width="80px">
          <el-form-item label="新密码">
            <el-input v-model="newPwd1" type="password" show-password />
          </el-form-item>
          <el-form-item label="确认密码">
            <el-input v-model="newPwd2" type="password" show-password @keyup.enter="handleChangePwd" />
          </el-form-item>
        </el-form>
        <template #footer>
          <el-button @click="showChangePwdDialog = false">取消</el-button>
          <el-button type="primary" @click="handleChangePwd">确认修改</el-button>
        </template>
      </el-dialog>

      <el-dialog
        v-model="showPreviewDialog"
        title="预览"
        width="80%"
        class="preview-dialog"
        destroy-on-close
      >
        <div class="flex justify-center">
          <img
            v-if="previewType === 'image'"
            :src="previewUrl"
            class="max-h-[80vh] max-w-full"
          />
          <video
            v-else-if="previewType === 'video'"
            :src="previewUrl"
            controls
            class="max-h-[80vh] max-w-full"
          />
          <iframe
            v-else-if="previewType === 'pdf'"
            :src="previewUrl"
            class="h-[80vh] w-full border-0"
          />
        </div>
      </el-dialog>
    </div>
  </div>
</template>
