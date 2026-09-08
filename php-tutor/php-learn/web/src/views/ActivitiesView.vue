<script setup lang="ts">
/**
 * ============================================================
 * 活动列表页 — Element Plus 版
 * ============================================================
 *
 * 功能：
 *   - 只看即将开始的活动筛选
 *   - 分页
 *   - 管理员可新建活动
 */
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { activityApi } from '@/api/modules/activity'
import { useApiResource } from '@/composables/useApiResource'
import { useAuthStore } from '@/stores/auth'
import { formatDate } from '@/utils/format'
import PaginationBar from '@/components/PaginationBar.vue'
import EmptyState from '@/components/EmptyState.vue'
import PageLoader from '@/components/PageLoader.vue'

const router = useRouter()
const auth = useAuthStore()

const upcomingOnly = ref(false)

const {
  data, total, page, pageSize, totalPage,
  loading, error, fetch, toPage,
} = useApiResource({
  fetcher: activityApi.list,
  defaultQuery: { pageSize: 10 },
})

async function toggleUpcoming(): Promise<void> {
  await fetch({ upcoming: upcomingOnly.value ? 'true' : undefined } as never)
}
</script>

<template>
  <div class="activities-page">
    <!-- 顶部 -->
    <div class="page-header">
      <div class="header-left">
        <h1 class="page-title">活动列表</h1>
        <p class="page-subtitle">共 {{ total }} 个活动</p>
      </div>
      <div class="header-actions">
        <el-button
          @click="fetch()"
          :disabled="loading"
        >
          <el-icon><Refresh /></el-icon>
          刷新
        </el-button>
        <router-link v-if="auth.isAdmin" to="/activities/new">
          <el-button type="primary">
            <el-icon><Plus /></el-icon>
            创建活动
          </el-button>
        </router-link>
      </div>
    </div>

    <!-- 筛选 -->
    <el-card class="filter-card" shadow="never">
      <el-checkbox
        v-model="upcomingOnly"
        @change="toggleUpcoming"
        label="只看即将开始的活动"
      />
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
      title="暂无活动"
      description="当前没有符合条件的活动。"
      :action-label="auth.isAdmin ? '创建第一个活动' : undefined"
      @action="router.push('/activities/new')"
    />

    <!-- 列表 -->
    <div v-else class="activity-list">
      <el-card
        v-for="item in data"
        :key="item.id"
        class="activity-card"
        shadow="hover"
      >
        <div class="activity-card-header">
          <h2 class="activity-title">{{ item.title }}</h2>
          <span class="activity-id">#{{ item.id }}</span>
        </div>

        <div class="activity-info">
          <div class="info-item">
            <el-icon class="info-icon"><Location /></el-icon>
            <span>{{ item.location || '待定' }}</span>
          </div>
          <div class="info-item">
            <el-icon class="info-icon"><Timer /></el-icon>
            <span>{{ formatDate(item.startTime) }} - {{ formatDate(item.endTime) }}</span>
          </div>
        </div>

        <p v-if="item.description" class="activity-desc">
          {{ item.description }}
        </p>

        <div class="activity-footer">
          <div class="activity-meta">
            <span>创建：{{ formatDate(item.createdAt) }}</span>
            <span>更新：{{ formatDate(item.updatedAt) }}</span>
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
  </div>
</template>

<style scoped>
.activities-page {
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
  padding: 12px 20px;
}

/* 错误 */
.error-alert {
  border-radius: 8px;
}

/* 活动列表 */
.activity-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.activity-card {
  border-radius: 12px;
  border: 1px solid var(--el-border-color-lighter);
  transition: all 0.2s ease;
}

.activity-card:hover {
  border-color: var(--el-color-success-light-5);
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
}

.activity-card-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 16px;
}

.activity-title {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  color: var(--el-text-color-primary);
  flex: 1;
}

.activity-id {
  font-family: 'JetBrains Mono', 'Fira Code', Consolas, monospace;
  font-size: 12px;
  color: var(--el-text-color-secondary);
  flex-shrink: 0;
  margin-left: 12px;
}

.activity-info {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  margin-bottom: 12px;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  color: var(--el-text-color-secondary);
}

.info-icon {
  color: var(--el-color-primary);
  flex-shrink: 0;
}

.activity-desc {
  margin: 0 0 16px 0;
  font-size: 14px;
  color: var(--el-text-color-secondary);
  line-height: 1.7;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.activity-footer {
  border-top: 1px solid var(--el-border-color-lighter);
  padding-top: 12px;
}

.activity-meta {
  display: flex;
  gap: 16px;
  font-size: 12px;
  color: var(--el-text-color-secondary);
}

/* 分页 */
.pagination-wrap {
  display: flex;
  justify-content: center;
  margin-top: 8px;
}
</style>