export interface ApiResponse<T = unknown> {
  code: number
  message: string
  data: T
}

export interface PaginatedResponse<T = unknown> {
  data: T[]
  total: number
  page: number
  pageSize: number
  lastPage: number
}

export interface User {
  id: number | null
  name: string
  email: string
  created_at: string
  updated_at: string
}

export interface UserCreate {
  name: string
  email: string
}

export interface UserUpdate {
  name?: string
  email?: string
}

export interface FileRecord {
  id: number | null
  original_name: string
  extension: string
  mime: string
  size: number
  created_at: string
}

export interface LogEntry {
  datetime: string
  level: string
  channel: string
  message: string
}

export interface LogResponse {
  total: number
  entries: LogEntry[]
}
