<script setup lang="ts">
/**
 * ============================================================
 * 登录页 — Element Plus 版
 * ============================================================
 *
 * 表单：
 *   - login:    登录名（用户名或邮箱，与后端 AuthController::login 一致）
 *   - password: 密码
 *
 * 提交流程：
 *   1. 前端简单校验（非空）
 *   2. 调 authStore.login() → POST /api/v1/auth/login
 *   3. 成功：跳转 redirect 参数指向的页面（默认 /）
 *   4. 失败：BusinessError.errors.login 绑定到输入框下方
 */
import { reactive, ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { BusinessError, REMEMBER_ME_KEY } from '@/api/request'
import { firstError } from '@/utils/format'
import type { FieldErrors } from '@/types/api'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

// 表单状态
const form = reactive({
  login: '',
  password: '',
})

const loading = ref(false)
const formErrors = ref<FieldErrors>({})

/** 记住密码开关（勾选后下次自动回填登录名与密码） */
const rememberMe = ref(false)

// ============================================================
// 初始化：如果之前勾选过"记住密码"，自动回填表单
// ============================================================
onMounted(() => {
  try {
    const raw = localStorage.getItem(REMEMBER_ME_KEY)
    if (!raw) return
    const saved = JSON.parse(raw) as { login?: string; password?: string }
    if (saved.login) form.login = saved.login
    if (saved.password) form.password = saved.password
    rememberMe.value = true
  } catch {
    // JSON 解析失败（脏数据）：静默忽略，避免影响登录页加载
  }
})

// 提交
async function handleSubmit(): Promise<void> {
  formErrors.value = {}

  // 前端预校验
  if (!form.login.trim()) {
    formErrors.value.login = ['登录名不能为空']
    return
  }
  if (!form.password) {
    formErrors.value.password = ['密码不能为空']
    return
  }

  loading.value = true
  try {
    await auth.login({ login: form.login.trim(), password: form.password })

    // 记住密码：勾选则持久化，未勾选则清除旧记录
    if (rememberMe.value) {
      localStorage.setItem(
        REMEMBER_ME_KEY,
        JSON.stringify({ login: form.login.trim(), password: form.password }),
      )
    } else {
      localStorage.removeItem(REMEMBER_ME_KEY)
    }

    // 登录成功：跳回原页面或首页
    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/'
    router.push(redirect)
  } catch (e) {
    if (e instanceof BusinessError) {
      if (e.errors) {
        formErrors.value = e.errors
      } else {
        // 全局错误（非字段级）
        formErrors.value.login = [e.message]
      }
    } else {
      formErrors.value.login = [e instanceof Error ? e.message : '未知错误']
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="login-page">
    <el-card class="login-card" shadow="never">
      <!-- Logo + 标题 -->
      <template #header>
        <div class="login-header">
          <div class="login-logo">
            <el-icon :size="40" color="var(--el-color-primary)">
              <Reading />
            </el-icon>
          </div>
          <h1 class="login-title">欢迎回来</h1>
          <p class="login-subtitle">登录到 php-learn 管理后台</p>
        </div>
      </template>

      <!-- 表单 -->
      <el-form
        :model="form"
        label-width="0"
        @submit.prevent="handleSubmit"
        :disabled="loading"
      >
        <!-- 登录名 -->
        <el-form-item prop="login" :error="firstError(formErrors, 'login')">
          <el-input
            v-model="form.login"
            type="text"
            autocomplete="username"
            placeholder="用户名或邮箱"
            size="large"
            :prefix-icon="User"
          />
        </el-form-item>

        <!-- 密码 -->
        <el-form-item prop="password" :error="firstError(formErrors, 'password')">
          <el-input
            v-model="form.password"
            type="password"
            autocomplete="current-password"
            placeholder="请输入密码"
            size="large"
            show-password
            :prefix-icon="Lock"
            @keyup.enter="handleSubmit"
          />
        </el-form-item>

        <!-- 记住密码 -->
        <el-form-item>
          <el-checkbox v-model="rememberMe" label="记住密码" />
        </el-form-item>

        <!-- 提交 -->
        <el-form-item>
          <el-button
            type="primary"
            size="large"
            @click="handleSubmit"
            :loading="loading"
            style="width: 100%"
          >
            <span v-if="loading" class="login-btn-text">
              登录中...
            </span>
            <span v-else class="login-btn-text">
              登 录
            </span>
          </el-button>
        </el-form-item>
      </el-form>

      <!-- 测试账号提示 -->
      <div class="login-tips">
        <div class="tips-header">
          <el-icon><InfoFilled /></el-icon>
          <span>测试账号</span>
        </div>
        <div class="tips-content">
          <span class="tips-label">用户名：</span>
          <span class="tips-value">admin</span>
          <span class="tips-label">密码：</span>
          <span class="tips-value">admin123</span>
        </div>
      </div>
    </el-card>

    <!-- 底部说明 -->
    <div class="login-footer">
      <p class="footer-text">
        前后端分离学习项目 · Vue 3 + TypeScript + Vite 8 + Element Plus
      </p>
    </div>
  </div>
</template>

<style scoped>
.login-page {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 24px;
  background: linear-gradient(135deg, var(--el-color-primary-light-9) 0%, var(--el-bg-color-page) 100%);
}

.login-card {
  width: 100%;
  max-width: 420px;
  border-radius: 12px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
}

.login-header {
  text-align: center;
}

.login-logo {
  margin-bottom: 12px;
}

.login-title {
  margin: 0 0 4px 0;
  font-size: 24px;
  font-weight: 700;
  color: var(--el-text-color-primary);
}

.login-subtitle {
  margin: 0;
  font-size: 14px;
  color: var(--el-text-color-secondary);
}

.login-btn-text {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.login-tips {
  margin-top: 20px;
  padding: 12px 16px;
  background-color: var(--el-fill-color-lighter);
  border-radius: 8px;
  border-left: 3px solid var(--el-color-primary);
}

.tips-header {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 500;
  color: var(--el-text-color-primary);
  margin-bottom: 6px;
}

.tips-content {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 13px;
  color: var(--el-text-color-secondary);
}

.tips-label {
  color: var(--el-text-color-secondary);
}

.tips-value {
  font-family: 'JetBrains Mono', 'Fira Code', Consolas, monospace;
  font-weight: 600;
  color: var(--el-text-color-primary);
}

.login-footer {
  margin-top: 24px;
  text-align: center;
}

.footer-text {
  margin: 0;
  font-size: 12px;
  color: var(--el-text-color-secondary);
}
</style>