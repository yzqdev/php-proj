<template>
  <div class="max-w-6xl mx-auto py-8 px-4">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">用户管理</h1>
      <el-button type="primary" @click="showCreateDialog = true">
        <el-icon><Plus /></el-icon>
        新增用户
      </el-button>
    </div>

    <el-card>
      <el-table :data="userStore.users" v-loading="userStore.loading" stripe>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="姓名" />
        <el-table-column prop="email" label="邮箱" />
        <el-table-column prop="created_at" label="创建时间" width="180" />
        <el-table-column label="操作" width="200">
          <template #default="{ row }">
            <el-button size="small" @click="showEditDialog(row)">编辑</el-button>
            <el-button size="small" type="danger" @click="handleDelete(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>

      <el-pagination
        v-if="userStore.total > 0"
        class="mt-4"
        background
        layout="prev, pager, next"
        :total="userStore.total"
        :page-size="20"
        v-model:current-page="currentPage"
        @current-change="handlePageChange"
      />
    </el-card>

    <!-- 创建/编辑弹窗 -->
    <el-dialog
      v-model="showCreateDialog"
      :title="editingUser ? '编辑用户' : '新增用户'"
      width="480px"
      destroy-on-close
    >
      <el-form ref="formRef" :model="form" :rules="rules" label-width="80px">
        <el-form-item label="姓名" prop="name">
          <el-input v-model="form.name" placeholder="请输入姓名" />
        </el-form-item>
        <el-form-item label="邮箱" prop="email">
          <el-input v-model="form.email" placeholder="请输入邮箱" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showCreateDialog = false">取消</el-button>
        <el-button type="primary" :loading="submitting" @click="handleSubmit">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox, type FormInstance, type FormRules } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import { useUserStore } from '@/stores/user'

const userStore = useUserStore()
const currentPage = ref(1)

const showCreateDialog = ref(false)
const editingUser = ref<{ id: number } | null>(null)
const submitting = ref(false)
const formRef = ref<FormInstance>()

const form = reactive({
  name: '',
  email: '',
})

const rules = reactive<FormRules>({
  name: [{ required: true, message: '请输入姓名', trigger: 'blur' }],
  email: [
    { required: true, message: '请输入邮箱', trigger: 'blur' },
    { type: 'email', message: '请输入正确的邮箱格式', trigger: 'blur' },
  ],
})

onMounted(() => {
  userStore.fetchUsers(currentPage.value)
})

async function handlePageChange(page: number) {
  await userStore.fetchUsers(page)
}

function showEditDialog(user: { id: number; name: string; email: string }) {
  editingUser.value = { id: user.id }
  form.name = user.name
  form.email = user.email
  showCreateDialog.value = true
}

async function handleSubmit() {
  if (!formRef.value) return
  await formRef.value.validate(async (valid) => {
    if (!valid) return
    submitting.value = true
    try {
      if (editingUser.value) {
        await userStore.updateUser(editingUser.value.id, { name: form.name, email: form.email })
        ElMessage.success('更新成功')
      } else {
        await userStore.createUser({ name: form.name, email: form.email })
        ElMessage.success('创建成功')
      }
      showCreateDialog.value = false
      resetForm()
      await userStore.fetchUsers(currentPage.value)
    } finally {
      submitting.value = false
    }
  })
}

function resetForm() {
  editingUser.value = null
  form.name = ''
  form.email = ''
  formRef.value?.resetFields()
}

async function handleDelete(user: { id: number; name: string }) {
  await ElMessageBox.confirm(`确定要删除用户 "${user.name}" 吗？`, '确认删除', {
    type: 'warning',
  })
  await userStore.deleteUser(user.id)
  ElMessage.success('删除成功')
  await userStore.fetchUsers(currentPage.value)
}
</script>
