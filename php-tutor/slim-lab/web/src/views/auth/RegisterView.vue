<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { Lock, User } from '@element-plus/icons-vue'
import type { FormInstance, FormRules } from 'element-plus'
import { useUserStore } from '@/stores/user'

const router = useRouter()
const userStore = useUserStore()

const formRef = ref<FormInstance>()
const loading = ref(false)
const form = reactive({ username: '', password: '', confirmPassword: '' })

const rules: FormRules = {
  username: [
    { required: true, message: '请输入账号', trigger: 'blur' },
    {
      pattern: /^[A-Za-z0-9_]{3,20}$/,
      message: '3-20 位字母、数字或下划线',
      trigger: 'blur',
    },
  ],
  password: [
    { required: true, message: '请输入密码', trigger: 'blur' },
    { min: 6, max: 64, message: '密码长度为 6-64 位', trigger: 'blur' },
  ],
  confirmPassword: [
    { required: true, message: '请再次输入密码', trigger: 'blur' },
    {
      validator: (_rule, value: string, callback) => {
        if (value !== form.password) {
          callback(new Error('两次输入的密码不一致'))
        } else {
          callback()
        }
      },
      trigger: 'blur',
    },
  ],
}

async function handleRegister() {
  const valid = await formRef.value?.validate().catch(() => false)
  if (!valid) return

  loading.value = true
  try {
    await userStore.register(form.username, form.password)
    ElMessage.success('注册成功，请登录')
    await router.push({ name: 'login' })
  } catch {
    // 错误提示由拦截器统一弹出
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-[#667eea] to-[#764ba2]">
    <div class="flex min-h-screen items-center justify-center px-5">
      <div class="w-full max-w-[400px] rounded-2xl bg-white/95 p-9 shadow-2xl backdrop-blur-md">
        <h2 class="mb-1 text-center text-2xl font-bold text-gray-800">slim-lab</h2>
        <p class="mb-8 text-center text-sm text-gray-400">PHP 教程演示平台 · 注册</p>

        <el-form ref="formRef" :model="form" :rules="rules" size="large" @keyup.enter="handleRegister">
          <el-form-item prop="username">
            <el-input v-model="form.username" placeholder="账号（3-20 位字母/数字/下划线）" clearable>
              <template #prefix>
                <el-icon><User /></el-icon>
              </template>
            </el-input>
          </el-form-item>
          <el-form-item prop="password">
            <el-input v-model="form.password" type="password" placeholder="密码（6-64 位）" show-password>
              <template #prefix>
                <el-icon><Lock /></el-icon>
              </template>
            </el-input>
          </el-form-item>
          <el-form-item prop="confirmPassword">
            <el-input v-model="form.confirmPassword" type="password" placeholder="确认密码" show-password>
              <template #prefix>
                <el-icon><Lock /></el-icon>
              </template>
            </el-input>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" class="w-full" :loading="loading" @click="handleRegister">
              注 册
            </el-button>
          </el-form-item>
        </el-form>

        <p class="text-center text-sm text-gray-500">
          已有账号？
          <RouterLink to="/login" class="text-[#667eea] no-underline">去登录</RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>
