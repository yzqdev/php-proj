<script setup lang="ts">
/**
 * ============================================================
 * 文章编辑/新建视图 — Element Plus 版
 * ============================================================
 *
 * 路由：
 *   - /articles/new       → 新建模式（无 id 参数）
 *   - /articles/:id/edit  → 编辑模式（有 id 参数）
 *
 * 关键设计：
 *   - 通过 route.params.id 判断模式
 *   - 编辑模式：加载文章数据后传给 ArticleForm
 *   - 提交成功：跳回详情页 /articles/:id
 */
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { articleApi } from '@/api/modules/article'
import ArticleForm from '@/components/ArticleForm.vue'
import PageLoader from '@/components/PageLoader.vue'
import EmptyState from '@/components/EmptyState.vue'
import type { Article } from '@/types/models'

const route = useRoute()
const router = useRouter()

// 判断模式：/articles/new 是独立静态路由（无 :id 参数），
// 用路由名判断；/articles/:id/edit 才带 id 参数
const isNew = computed(() => route.name === 'article-new')
const id = computed(() => (isNew.value ? null : Number(route.params.id)))

const existingArticle = ref<Article | null>(null)
const loading = ref(false)
const errorMsg = ref('')

onMounted(async () => {
  if (isNew.value) return
  loading.value = true
  try {
    existingArticle.value = await articleApi.detail(id.value!)
  } catch (e) {
    errorMsg.value = e instanceof Error ? e.message : '加载失败'
  } finally {
    loading.value = false
  }
})

function onFormSuccess(savedId: number): void {
  router.push(`/articles/${savedId}`)
}

function onCancel(): void {
  if (isNew.value) {
    router.push('/articles')
  } else {
    router.push(`/articles/${id.value}`)
  }
}
</script>

<template>
  <div class="article-edit-page">
    <!-- 返回 -->
    <el-button
      text
      @click="onCancel"
      class="back-btn"
    >
      <el-icon><ArrowLeft /></el-icon>
      返回
    </el-button>

    <!-- 标题 -->
    <h1 class="edit-title">
      {{ isNew ? '新建文章' : `编辑文章 #${id}` }}
    </h1>

    <!-- 加载中（编辑模式） -->
    <PageLoader v-if="!isNew && loading" :rows="8" />

    <!-- 加载失败（编辑模式） -->
    <EmptyState
      v-else-if="errorMsg"
      title="文章加载失败"
      :description="errorMsg"
      action-label="返回列表"
      @action="router.push('/articles')"
    />

    <!-- 表单 -->
    <div v-else class="edit-form-wrap">
      <ArticleForm
        :article="existingArticle"
        @success="onFormSuccess"
        @cancel="onCancel"
      />
    </div>
  </div>
</template>

<style scoped>
.article-edit-page {
  max-width: 800px;
  margin: 0 auto;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
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

.edit-title {
  margin: 0;
  font-size: 26px;
  font-weight: 700;
  color: var(--el-text-color-primary);
}

.edit-form-wrap {
  background-color: var(--el-bg-color);
  border: 1px solid var(--el-border-color-lighter);
  border-radius: 12px;
  padding: 24px;
}
</style>