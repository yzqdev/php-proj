import { defineStore } from 'pinia'
import { ref } from 'vue'
import * as fileApi from '@/api/file'
import type { LogEntry } from '@/types'

export const useLogStore = defineStore('log', () => {
  const entries = ref<LogEntry[]>([])
  const total = ref(0)
  const loading = ref(false)

  async function fetchLogs(level?: string, limit = 100) {
    loading.value = true
    try {
      const res = await fileApi.getLogs({ level, limit })
      // API response: { code, message, data: { total, entries } }
      total.value = res.data.total
      entries.value = res.data.entries
    } finally {
      loading.value = false
    }
  }

  return { entries, total, loading, fetchLogs }
})
