<script setup lang="ts">
/**
 * ============================================================
 * 文章详情页 — Element Plus 版
 * ============================================================
 *
 * 路由：/articles/:id
 *
 * 加载逻辑：
 *   - 挂载时从 route.params.id 拿 id
 *   - 调 articleApi.detail(id) 获取详情
 *   - 失败（404 等）→ 显示 EmptyState 兜底
 */
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { articleApi } from '@/api/modules/article'
import { useAuthStore } from '@/stores/auth'
import { useDeleteConfirm } from '@/composables/useDeleteConfirm'
import { formatDate } from '@/utils/format'
import CategoryBadge from '@/components/CategoryBadge.vue'
import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue'
import PageLoader from '@/components/PageLoader.vue'
import EmptyState from '@/components/EmptyState.vue'
import type { Article } from '@/types/models'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const id = Number(route.params.id)
const article = ref<Article | null>(null)
const loading = ref(true)
const notFound = ref(false)
const errorMsg = ref('')

async function load(): Promise<void> {
  loading.value = true
  notFound.value = false
  errorMsg.value = ''
  try {
    article.value = await articleApi.detail(id)
  } catch (e) {
    if (e instanceof Error && e.message.includes('不存在')) {
      notFound.value = true
    } else {
      errorMsg.value = e instanceof Error ? e.message : '加载失败'
    }
  } finally {
    loading.value = false
  }
}

const { visible, loading: deleteLoading, show, confirm, cancel } = useDeleteConfirm(
  async () => {
    await articleApi.remove(id)
    router.push('/articles')
  },
  `确认删除文章 #${id}？此操作不可撤销。`,
)

onMounted(load)
</script>

<template>
  <div class="article-detail-page">
    <!-- 返回 -->
    <el-button
      text
      @click="router.back()"
      class="back-btn"
    >
      <el-icon><ArrowLeft /></el-icon>
      返回列表
    </el-button>

    <!-- 加载中 -->
    <PageLoader v-if="loading" :rows="6" />

    <!-- 404 -->
    <EmptyState
      v-else-if="notFound"
      title="文章不存在"
      description="文章可能已被删除，或链接有误。"
      action-label="返回列表"
      @action="router.push('/articles')"
    />

    <!-- 错误 -->
    <el-alert
      v-else-if="errorMsg"
      :title="errorMsg"
      type="error"
      show-icon
      class="error-alert"
    />

    <!-- 文章 -->
    <article v-else-if="article" class="article-detail">
      <!-- 元信息 -->
      <div class="article-meta-row">
        <CategoryBadge :category="article.category" />
        <el-tag
          :type="article.status === 'published' ? 'success' : 'warning'"
          size="small"
          effect="plain"
        >
          {{ article.status === 'published' ? '已发布' : '草稿' }}
        </el-tag>
        <span class="article-id-text">#{{ article.id }}</span>
      </div>

      <!-- 标题 -->
      <h1 class="article-detail-title">{{ article.title }}</h1>

      <!-- 时间 -->
      <div class="article-time-row">
        <el-icon><Clock /></el-icon>
        <span>创建：{{ formatDate(article.createdAt) }}</span>
        <span>更新：{{ formatDate(article.updatedAt) }}</span>
      </div>

      <!-- 正文 -->
      <div class="article-body-card">
        <div class="article-body-text">
          {{ article.body }}
        </div>
      </div>

      <!-- 操作按钮（仅管理员） -->
      <div v-if="auth.isAdmin" class="article-actions">
        <router-link :to="`/articles/${article.id}/edit`">
          <el-button type="primary" :icon="Edit">
            编辑
          </el-button>
        </router-link>
        <el-button
          type="danger"
          :icon="Delete"
          @click="show()"
        >
          删除
        </el-button>
      </div>
    </article>

    <!-- 删除确认弹窗 -->
    <DeleteConfirmDialog
      v-model="visible"
      :message="'确认要删除这篇文章吗？此操作不可撤销。'"
      :loading="deleteLoading"
      @confirm="confirm"
      @cancel="cancel"
    />
  </div>
</template>

<style scoped>
.article-detail-page {
  max-width: 800px;
  margin: 0 auto;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.back-btn {
  align-self: flex-start;
  color: var(--el-text-color-secondary);
  font-size: 14px;
  padding: 4px 8px;
  margin: 0 0 8px 0;
}

.back-btn:hover {
  color: var(--el-color-primary);
}

/* 元信息 */
.article-meta-row {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.article-id-text {
  font-family: 'JetBrains Mono', 'Fira Code', Consolas, monospace;
  font-size: 13px;
  color: var(--el-text-color-secondary);
}

/* 标题 */
.article-detail-title {
  margin: 16px 0;
  font-size: 32px;
  font-weight: 700;
  color: var(--el-text-color-primary);
  line-height: 1.3;
}

/* 时间 */
.article-time-row {
  display: flex;
  align-items: center;
  gap: 16px;
  font-size: 14px;
  color: var(--el-text-color-secondary);
  padding-bottom: 20px;
  border-bottom: 1px solid var(--el-border-color-lighter);
}

.article-time-row .el-icon {
  color: var(--el-color-primary);
}

/* 正文 */
.article-body-card {
  background-color: var(--el-bg-color);
  border: 1px solid var(--el-border-color-lighter);
  border-radius: 12px;
  padding: 28px 32px;
}

.article-body-text {
  font-size: 16px;
  line-height: 1.9;
  color: var(--el-text-color-primary);
  white-space: pre-wrap;
  word-break: break-word;
}

/* 操作按钮 */
.article-actions {
  display: flex;
  gap: 12px;
  padding-top: 20px;
  border-top: 1px solid var(--el-border-color-lighter);
}

.error-alert {
  border-radius: 8px;
}
</style>