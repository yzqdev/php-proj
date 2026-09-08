import { defineStore } from 'pinia'
import { ref } from 'vue'
import * as userApi from '@/api/user'
import type { User } from '@/types'

export const useUserStore = defineStore('user', () => {
  const users = ref<User[]>([])
  const total = ref(0)
  const loading = ref(false)

  async function fetchUsers(page = 1, pageSize = 20) {
    loading.value = true
    try {
      const res = await userApi.getUsers({ page, pageSize })
      // API response: { code, message, data: { data: [...], total, page, pageSize, lastPage } }
      const pagination = res.data
      users.value = pagination.data
      total.value = pagination.total
    } finally {
      loading.value = false
    }
  }

  async function createUser(data: { name: string; email: string }) {
    return userApi.createUser(data)
  }

  async function updateUser(id: number, data: { name?: string; email?: string }) {
    return userApi.updateUser(id, data)
  }

  async function deleteUser(id: number) {
    return userApi.deleteUser(id)
  }

  return { users, total, loading, fetchUsers, createUser, updateUser, deleteUser }
})
