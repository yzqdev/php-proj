import request from '@/utils/request'
import type { ApiResponse } from '@/types/api'

export function phpDemoAction(action: string) {
  return request.get<ApiResponse<{ message: string }>>(`/php-demo`, {
    params: { action },
  })
}
