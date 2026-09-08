<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ElMessage } from 'element-plus'
import { Lock, User } from '@element-plus/icons-vue'
import type { FormInstance, FormRules } from 'element-plus'
import { useUserStore } from '@/stores/user'

const REMEMBER_KEY = 'slim-lab-remembered-username'

const router = useRouter()
const route = useRoute()
const userStore = useUserStore()

const formRef = ref<FormInstance>()
const loading = ref(false)
const remember = ref(false)
const form = reactive({ username: '', password: '' })

/** 演示环境预置账号（storage/users.json），点击一键填入，便于快速体验 */
const DEMO_ACCOUNTS = [
  { username: 'demo_user', password: 'secret123' },
  { username: 'e2e_user', password: 'secret123' },
]

function fillDemoAccount(account: { username: string; password: string }) {
  form.username = account.username
  form.password = account.password
}

const rules: FormRules = {
  username: [{ required: true, message: '请输入账号', trigger: 'blur' }],
  password: [{ required: true, message: '请输入密码', trigger: 'blur' }],
}

onMounted(() => {
  // 记住密码：本地明文存储（演示项目可接受；生产环境应改为服务端长效 token）
  try {
    const saved = JSON.parse(localStorage.getItem(REMEMBER_KEY) ?? 'null') as {
      username: string
      password: string
    } | null
    if (saved?.username && saved?.password) {
      form.username = saved.username
      form.password = saved.password
      remember.value = true
    }
  } catch {
    // 历史数据损坏时忽略，按未记住处理
  }
})

async function handleLogin() {
  const valid = await formRef.value?.validate().catch(() => false)
  if (!valid) return

  loading.value = true
  try {
    await userStore.login(form.username, form.password)
    if (remember.value) {
      localStorage.setItem(
        REMEMBER_KEY,
        JSON.stringify({ username: form.username, password: form.password }),
      )
    } else {
      localStorage.removeItem(REMEMBER_KEY)
    }
    ElMessage.success('登录成功')
    // 守卫记录的来源路由优先，避免登录后总是落在首页
    const redirect = (route.query.redirect as string | undefined) ?? '/'
    await router.push(redirect)
  } catch {
    // 错误提示由拦截器统一弹出
  } finally {
    loading.value = false
  }
}

function handleForgot() {
  // 演示项目无邮件服务，给出指引即可
  ElMessage.info('演示项目：请联系管理员重置密码')
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-[#667eea] to-[#764ba2]">
    <div class="flex min-h-screen items-center justify-center px-5">
      <div class="w-full max-w-[400px] rounded-2xl bg-white/95 p-9 shadow-2xl backdrop-blur-md">
        <h2 class="mb-1 text-center text-2xl font-bold text-gray-800">slim-lab</h2>
        <p class="mb-8 text-center text-sm text-gray-400">PHP 教程演示平台 · 登录</p>

        <el-form ref="formRef" :model="form" :rules="rules" size="large" @keyup.enter="handleLogin">
          <el-form-item prop="username">
            <el-input v-model="form.username" placeholder="账号（3-20 位字母/数字/下划线）" clearable>
              <template #prefix>
                <el-icon><User /></el-icon>
              </template>
            </el-input>
          </el-form-item>
          <el-form-item prop="password">
            <el-input v-model="form.password" type="password" placeholder="密码" show-password>
              <template #prefix>
                <el-icon><Lock /></el-icon>
              </template>
            </el-input>
          </el-form-item>
          <el-form-item>
            <div class="flex w-full items-center justify-between">
              <el-checkbox v-model="remember">记住密码</el-checkbox>
              <el-link type="primary" :underline="false" @click="handleForgot">忘记密码？</el-link>
            </div>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" class="w-full" :loading="loading" @click="handleLogin">
              登 录
            </el-button>
          </el-form-item>
        </el-form>

        <div class="mb-5 rounded-lg border border-gray-100 bg-gray-50 px-3 py-2.5">
          <div class="mb-2 text-xs text-gray-400">演示环境测试账号，点击即填入</div>
          <div class="flex flex-wrap gap-2">
            <el-button
              v-for="account in DEMO_ACCOUNTS"
              :key="account.username"
              size="small"
              @click="fillDemoAccount(account)"
            >
              {{ account.username }} / {{ account.password }}
            </el-button>
          </div>
        </div>

        <p class="text-center text-sm text-gray-500">
          没有账号？
          <RouterLink to="/register" class="text-[#667eea] no-underline">去注册</RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>
