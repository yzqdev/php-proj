<script setup lang="ts">
/**
 * ============================================================
 * 文章列表页 — Element Plus 版
 * ============================================================
 *
 * 功能：
 *   - 分类/状态筛选
 *   - 分页
 *   - 卡片点击跳详情
 *   - 管理员可编辑/删除
 *   - 顶部"新建文章"按钮
 */
import { computed, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { articleApi } from '@/api/modules/article'
import { useApiResource } from '@/composables/useApiResource'
import { useDeleteConfirm } from '@/composables/useDeleteConfirm'
import { useAuthStore } from '@/stores/auth'
import { formatDate } from '@/utils/format'
import PaginationBar from '@/components/PaginationBar.vue'
import CategoryBadge from '@/components/CategoryBadge.vue'
import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue'
import PageLoader from '@/components/PageLoader.vue'
import type { ArticleCategory, ArticleStatus } from '@/types/models'

const router = useRouter()
const auth = useAuthStore()

// 筛选状态
const filters = reactive<{ category: ArticleCategory | ''; status: ArticleStatus | '' }>({
  category: '',
  status: '',
})

const {
  data, total, page, pageSize, totalPage,
  loading, error, fetch, toPage,
} = useApiResource({
  fetcher: articleApi.list,
  defaultQuery: { pageSize: 10 },
})

async function applyFilters(): Promise<void> {
  await fetch({ category: filters.category, status: filters.status } as never)
}

// 待删除的文章 id
const pendingDeleteId = ref<number | null>(null)

// 删除二次确认
const {
  visible, loading: deleteLoading, message, show, confirm, cancel,
} = useDeleteConfirm(
  async () => {
    if (pendingDeleteId.value === null) return
    await articleApi.remove(pendingDeleteId.value)
    await fetch()
  },
)

function confirmDelete(id: number, title: string): void {
  pendingDeleteId.value = id
  show(`确认删除文章《${title}》？此操作不可撤销。`)
}

// 分类选项
const categoryOptions = computed(() => [
  { value: '' as ArticleCategory | '', label: '全部分类' },
  { value: 'php' as ArticleCategory, label: 'PHP' },
  { value: 'java' as ArticleCategory, label: 'Java' },
  { value: 'db' as ArticleCategory, label: 'Database' },
  { value: 'other' as ArticleCategory, label: '其他' },
])

const statusOptions = computed(() => [
  { value: '' as ArticleStatus | '', label: '全部状态' },
  { value: 'published' as ArticleStatus, label: '已发布' },
  { value: 'draft' as ArticleStatus, label: '草稿' },
])
</script>

<template>
  <div class="articles-page">
    <!-- 顶部：标题 + 操作 -->
    <div class="page-header">
      <div class="header-left">
        <h1 class="page-title">文章列表</h1>
        <p class="page-subtitle">共 {{ total }} 篇文章</p>
      </div>
      <div class="header-actions">
        <el-button
          @click="fetch()"
          :disabled="loading"
        >
          <el-icon><Refresh /></el-icon>
          刷新
        </el-button>
        <router-link v-if="auth.isAdmin" to="/articles/new">
          <el-button type="primary">
            <el-icon><Plus /></el-icon>
            新建文章
          </el-button>
        </router-link>
      </div>
    </div>

    <!-- 筛选栏 -->
    <el-card class="filter-card" shadow="never">
      <div class="filter-row">
        <div class="filter-item">
          <span class="filter-label">分类</span>
          <el-select
            v-model="filters.category"
            placeholder="全部分类"
            style="width: 160px"
            @change="applyFilters"
          >
            <el-option
              v-for="opt in categoryOptions"
              :key="opt.value"
              :label="opt.label"
              :value="opt.value"
            />
          </el-select>
        </div>

        <div class="filter-item">
          <span class="filter-label">状态</span>
          <el-select
            v-model="filters.status"
            placeholder="全部状态"
            style="width: 160px"
            @change="applyFilters"
          >
            <el-option
              v-for="opt in statusOptions"
              :key="opt.value"
              :label="opt.label"
              :value="opt.value"
            />
          </el-select>
        </div>
      </div>
    </el-card>

    <!-- 错误提示 -->
    <el-alert
      v-if="error"
      :title="error"
      type="error"
      show-icon
      class="error-alert"
    />

    <!-- 加载中 -->
    <PageLoader v-if="loading && data.length === 0" :rows="5" />

    <!-- 空状态 -->
    <EmptyState
      v-else-if="data.length === 0"
      title="暂无文章"
      description="当前筛选条件下没有文章。"
      :action-label="auth.isAdmin ? '新建第一篇文章' : undefined"
      @action="router.push('/articles/new')"
    />

    <!-- 列表 -->
    <div v-else class="article-list">
      <el-card
        v-for="item in data"
        :key="item.id"
        class="article-card"
        shadow="hover"
      >
        <div class="article-card-header">
          <div class="article-card-title-row">
            <CategoryBadge :category="item.category" />
            <el-tag
              :type="item.status === 'published' ? 'success' : 'warning'"
              size="small"
              effect="plain"
            >
              {{ item.status === 'published' ? '已发布' : '草稿' }}
            </el-tag>
          </div>
          <span class="article-id">#{{ item.id }}</span>
        </div>

        <router-link
          :to="`/articles/${item.id}`"
          class="article-title"
        >
          {{ item.title }}
        </router-link>

        <p class="article-body">{{ item.body }}</p>

        <div class="article-footer">
          <div class="article-meta">
            <span>创建：{{ formatDate(item.createdAt) }}</span>
            <span>更新：{{ formatDate(item.updatedAt) }}</span>
          </div>
          <div v-if="auth.isAdmin" class="article-actions">
            <router-link :to="`/articles/${item.id}/edit`">
              <el-button size="small" type="primary" text>
                <el-icon><Edit /></el-icon>
                编辑
              </el-button>
            </router-link>
            <el-button
              size="small"
              type="danger"
              text
              @click="confirmDelete(item.id, item.title)"
            >
              <el-icon><Delete /></el-icon>
              删除
            </el-button>
          </div>
        </div>
      </el-card>
    </div>

    <!-- 分页 -->
    <div v-if="totalPage > 1" class="pagination-wrap">
      <PaginationBar
        :total="total"
        :page="page"
        :page-size="pageSize"
        :total-page="totalPage"
        :loading="loading"
        @change="toPage"
      />
    </div>

    <!-- 删除确认弹窗 -->
    <DeleteConfirmDialog
      v-model="visible"
      :message="message"
      :loading="deleteLoading"
      @confirm="confirm"
      @cancel="cancel"
    />
  </div>
</template>

<style scoped>
.articles-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* 顶部 */
.page-header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}

.page-title {
  margin: 0 0 4px 0;
  font-size: 26px;
  font-weight: 700;
  color: var(--el-text-color-primary);
}

.page-subtitle {
  margin: 0;
  font-size: 14px;
  color: var(--el-text-color-secondary);
}

.header-actions {
  display: flex;
  gap: 10px;
}

/* 筛选 */
.filter-card {
  border-radius: 12px;
  border: 1px solid var(--el-border-color-lighter);
}

.filter-row {
  display: flex;
  align-items: center;
  gap: 32px;
  flex-wrap: wrap;
}

.filter-item {
  display: flex;
  align-items: center;
  gap: 10px;
}

.filter-label {
  font-size: 14px;
  color: var(--el-text-color-secondary);
  white-space: nowrap;
}

/* 错误 */
.error-alert {
  border-radius: 8px;
}

/* 文章列表 */
.article-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.article-card {
  border-radius: 12px;
  border: 1px solid var(--el-border-color-lighter);
  transition: all 0.2s ease;
}

.article-card:hover {
  border-color: var(--el-color-primary-light-5);
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
}

.article-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}

.article-card-title-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

.article-id {
  font-family: 'JetBrains Mono', 'Fira Code', Consolas, monospace;
  font-size: 12px;
  color: var(--el-text-color-secondary);
}

.article-title {
  display: block;
  font-size: 18px;
  font-weight: 600;
  color: var(--el-text-color-primary);
  text-decoration: none;
  margin-bottom: 8px;
  transition: color 0.2s ease;
}

.article-title:hover {
  color: var(--el-color-primary);
}

.article-body {
  margin: 0 0 16px 0;
  font-size: 14px;
  color: var(--el-text-color-secondary);
  line-height: 1.7;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.article-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px solid var(--el-border-color-lighter);
  padding-top: 12px;
}

.article-meta {
  display: flex;
  gap: 16px;
  font-size: 12px;
  color: var(--el-text-color-secondary);
}

.article-actions {
  display: flex;
  gap: 4px;
}

/* 分页 */
.pagination-wrap {
  display: flex;
  justify-content: center;
  margin-top: 8px;
}
</style>