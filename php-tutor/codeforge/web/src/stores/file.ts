import { defineStore } from 'pinia'
import { ref } from 'vue'
import * as fileApi from '@/api/file'
import type { FileRecord } from '@/types'

export const useFileStore = defineStore('file', () => {
  const files = ref<FileRecord[]>([])
  const loading = ref(false)

  async function fetchFiles(limit = 100) {
    loading.value = true
    try {
      const res = await fileApi.getFiles({ limit })
      // API response: { code, message, data: FileRecord[] }
      files.value = res.data
    } finally {
      loading.value = false
    }
  }

  async function uploadFile(file: File) {
    return fileApi.uploadFile(file)
  }

  async function deleteFile(id: number) {
    return fileApi.deleteFile(id)
  }

  return { files, loading, fetchFiles, uploadFile, deleteFile }
})
