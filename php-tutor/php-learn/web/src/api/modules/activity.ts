/**
 * ============================================================
 * 活动 API 模块
 * ============================================================
 *
 * 对应后端：
 *   GET    /api/v1/activities          → list()
 *   GET    /api/v1/activities/{id}     → detail()
 *   POST   /api/v1/activities          → create()
 *   PUT    /api/v1/activities/{id}     → update()
 *   DELETE /api/v1/activities/{id}     → remove()
 */
import { del, get, post, put } from '../request'
import type { PageResult } from '@/types/api'
import type {
  Activity,
  ActivityListQuery,
  CreateActivityPayload,
  UpdateActivityPayload,
} from '@/types/models'

export const activityApi = {
  /** 列表（upcoming=true 只返回即将开始的活动） */
  list: (query?: ActivityListQuery) => get<PageResult<Activity>>('/activities', { params: query }),

  /** 详情 */
  detail: (id: number) => get<Activity>(`/activities/${id}`),

  /** 新建 */
  create: (payload: CreateActivityPayload) => post<Activity>('/activities', payload),

  /** 更新 */
  update: (id: number, payload: UpdateActivityPayload) => put<Activity>(`/activities/${id}`, payload),

  /** 删除 */
  remove: (id: number) => del<null>(`/activities/${id}`),
}
