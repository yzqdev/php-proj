<script setup lang="ts">
/**
 * ============================================================
 * 活动表单组件（只支持新建）— Element Plus 版
 * ============================================================
 *
 * Emits:
 *   - success: () => void   提交成功
 *   - cancel: () => void    取消
 */
import { useActivityForm } from '@/composables/useActivityForm'

const emit = defineEmits<{
  (e: 'success'): void
  (e: 'cancel'): void
}>()

const {
  form,
  errors,
  submitting,
  submit,
  reset,
} = useActivityForm({
  onSuccess: () => {
    reset()
    emit('success')
  },
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
      label="活动标题"
    >
      <template #label>
        <span>活动标题 <span class="required">*</span></span>
      </template>
      <el-input
        v-model="form.title"
        type="text"
        maxlength="100"
        show-word-limit
        placeholder="请输入活动标题"
        clearable
      />
    </el-form-item>

    <!-- 地点 -->
    <el-form-item
      prop="location"
      label="活动地点"
    >
      <el-input
        v-model="form.location"
        type="text"
        placeholder="例如：北京·中关村 / 线上（B 站直播）"
        clearable
      />
    </el-form-item>

    <!-- 时间 -->
    <el-row :gutter="16">
      <el-col :span="12">
        <el-form-item
          prop="startTime"
          :error="errors.startTime?.[0]"
          label="开始时间"
        >
          <template #label>
            <span>开始时间 <span class="required">*</span></span>
          </template>
          <el-date-picker
            v-model="form.startTimeInput"
            type="datetime"
            placeholder="选择开始时间"
            value-format="YYYY-MM-DDTHH:mm"
            style="width: 100%"
          />
        </el-form-item>
      </el-col>
      <el-col :span="12">
        <el-form-item
          prop="endTime"
          :error="errors.endTime?.[0]"
          label="结束时间"
        >
          <el-date-picker
            v-model="form.endTimeInput"
            type="datetime"
            placeholder="选择结束时间（可选）"
            value-format="YYYY-MM-DDTHH:mm"
            :disabled-time="() => form.startTimeInput ? new Date(form.startTimeInput) : null"
            style="width: 100%"
          />
        </el-form-item>
      </el-col>
    </el-row>

    <!-- 描述 -->
    <el-form-item
      prop="description"
      label="活动描述"
    >
      <el-input
        v-model="form.description"
        type="textarea"
        :rows="4"
        placeholder="简短描述活动内容..."
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
        创建活动
      </el-button>
    </el-form-item>
  </el-form>
</template>

<style scoped>
.required {
  color: var(--el-color-danger);
}
</style>