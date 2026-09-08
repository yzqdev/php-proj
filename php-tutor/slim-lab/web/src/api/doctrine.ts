import request from '@/utils/request'
import type { ApiResponse, DoctrineStats } from '@/types/api'

export function doctrineAction(action: string) {
  return request.get<ApiResponse<{ message: string }>>(`/doctrine`, {
    params: { action },
  })
}

export function doctrineStats() {
  return request.get<ApiResponse<DoctrineStats>>(`/doctrine`, {
    params: { action: 'stats' },
  })
}
