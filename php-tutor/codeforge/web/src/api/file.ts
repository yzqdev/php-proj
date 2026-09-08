import api from '@/utils/request'
import type { ApiResponse, FileRecord, LogResponse } from '@/types'

export function getFiles(params?: { limit?: number }): Promise<ApiResponse<FileRecord[]>> {
  return api.get('/files', { params }).then((res) => res.data)
}

export function getFile(id: number): Promise<ApiResponse<FileRecord>> {
  return api.get(`/files/${id}`).then((res) => res.data)
}

export function uploadFile(file: File): Promise<ApiResponse<FileRecord>> {
  const formData = new FormData()
  formData.append('file', file)
  return api.post('/files', formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  }).then((res) => res.data)
}

export function deleteFile(id: number): Promise<ApiResponse<null>> {
  return api.delete(`/files/${id}`).then((res) => res.data)
}

export function getDownloadUrl(id: number): string {
  return `${import.meta.env.VITE_API_BASE_URL ?? '/api'}/files/${id}/download`
}

export function getLogs(params?: { level?: string; limit?: number }): Promise<ApiResponse<LogResponse>> {
  return api.get('/logs', { params }).then((res) => res.data)
}
