<script setup lang="ts">
/**
 * ============================================================
 * 文章表单组件（新建 + 编辑共用）— Element Plus 版
 * ============================================================
 *
 * 通过 `article` prop 判断模式：
 *   - article 未传或为 null → 新建模式
 *   - article 传入 → 编辑模式（表单预填 article 的数据）
 *
 * 使用方式：
 *   <!-- 新建 -->
 *   <ArticleForm @success="onCreated" />
 *
 *   <!-- 编辑 -->
 *   <ArticleForm :article="existingArticle" @success="onUpdated" />
 *
 * Emits:
 *   - success: (id: number) => void   提交成功（返回新文章 id）
 *   - cancel: () => void              取消按钮点击
 */
import { onMounted, watch } from 'vue'
import { useArticleForm } from '@/composables/useArticleForm'
import type { Article } from '@/types/models'

const props = defineProps<{
  article?: Article | null
  onSuccess?: (id: number) => void
}>()

const emit = defineEmits<{
  (e: 'success', id: number): void
  (e: 'cancel'): void
}>()

const {
  form,
  errors,
  submitting,
  isEdit,
  loadFromExisting,
  submit,
  reset,
} = useArticleForm({
  onSuccess: (id) => {
    emit('success', id)
    props.onSuccess?.(id)
  },
})

// 编辑模式：挂载时填充表单
watch(
  () => props.article,
  (article) => {
    if (article) loadFromExisting(article)
    else reset()
  },
  { immediate: true },
)

onMounted(() => {
  if (props.article) loadFromExisting(props.article)
})
</script>

<template>
  <el-form
    :model="form"
    label-width="120px"
    @submit.prevent="submit"
    :disabled="submitting"
  >
    <!-- 标题 -->
    <el-form-item
      prop="title"
      :error="errors.title?.[0]"
      label="标题"
    >
      <template #label>
        <span>标题 <span class="required">*</span></span>
      </template>
      <el-input
        v-model="form.title"
        type="text"
        maxlength="80"
        show-word-limit
        placeholder="请输入文章标题"
        clearable
      />
    </el-form-item>

    <!-- 分类 + 状态 -->
    <el-row :gutter="16">
      <el-col :span="12">
        <el-form-item
          prop="category"
          :error="errors.category?.[0]"
          label="分类"
        >
          <el-select v-model="form.category" placeholder="请选择分类" style="width: 100%">
            <el-option label="PHP" value="php" />
            <el-option label="Java" value="java" />
            <el-option label="Database" value="db" />
            <el-option label="其他" value="other" />
          </el-select>
        </el-form-item>
      </el-col>
      <el-col :span="12">
        <el-form-item
          prop="status"
          :error="errors.status?.[0]"
          label="状态"
        >
          <el-select v-model="form.status" placeholder="请选择状态" style="width: 100%">
            <el-option label="已发布" value="published" />
            <el-option label="草稿" value="draft" />
          </el-select>
        </el-form-item>
      </el-col>
    </el-row>

    <!-- 正文 -->
    <el-form-item
      prop="body"
      :error="errors.body?.[0]"
      label="正文"
    >
      <template #label>
        <span>正文 <span class="required">*</span></span>
      </template>
      <el-input
        v-model="form.body"
        type="textarea"
        :rows="10"
        placeholder="请输入文章正文..."
        resize="vertical"
      />
    </el-form-item>

    <!-- 操作按钮 -->
    <el-form-item>
      <el-button
        type="default"
        @click="emit('cancel')"
        :disabled="submitting"
      >
        取消
      </el-button>
      <el-button
        type="primary"
        @click="submit"
        :loading="submitting"
      >
        {{ isEdit ? '保存修改' : '创建文章' }}
      </el-button>
    </el-form-item>
  </el-form>
</template>

<style scoped>
.required {
  color: var(--el-color-danger);
}
</style>