import request from '@/utils/request'
import type { ApiResponse, LogDaysResult, LogQueryResult } from '@/types/api'

/** 运行日志 API：/api/logs（Bearer Token，服务端按天切分 storage/logs/app-YYYY-MM-DD.log） */

export function logDays(limit = 30) {
  return request.get<ApiResponse<LogDaysResult>>('/logs/days', { params: { limit } })
}

export function logQuery(date: string, offset = 0, limit = 500) {
  return request.get<ApiResponse<LogQueryResult>>('/logs', { params: { date, offset, limit } })
}
