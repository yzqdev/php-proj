export interface ApiResponse<T = unknown> {
  code: number
  message: string
  data: T
}

export interface AuthUser {
  id: number
  username: string
  email: string
  created_at: number
}

export interface LoginResult {
  token: string
  user: AuthUser
}

export interface UserInfoResult {
  user: AuthUser
}

export interface DoctrineStats {
  userCount: number
  productCount: number
}

export interface CloudDriveFile {
  name: string
  is_dir: boolean
  size: number
  size_text: string
  mtime: string
  ext: string
}

export interface CloudDriveDir {
  list: CloudDriveFile[]
  dirs: string[]
  current_folder: string
}

export interface CloudDriveShare {
  token: string
  url: string
  name: string
  parent_path: string
  is_dir: boolean
  pwd: string
  expire: number
  create_time: number
  is_expire: boolean
  expire_text: string
}

export interface SearchResult {
  full_rel: string
  name: string
  parent_dir: string
  is_dir: boolean
  size: number
  mtime: string
  ext: string
}

export type LogLevel =
  'DEBUG' | 'INFO' | 'NOTICE' | 'WARNING' | 'ERROR' | 'CRITICAL' | 'ALERT' | 'EMERGENCY'

/** 单行日志；原文无法解析时 timestamp/channel/level 为 null，message 为原始整行 */
export interface LogLine {
  no: number
  timestamp: string | null
  channel: string | null
  level: LogLevel | null
  message: string
  context: Record<string, unknown> | null
  raw: string
}

export interface LogQueryResult {
  date: string
  total: number
  offset: number
  limit: number
  truncated: boolean
  lines: LogLine[]
}

/** 可查日志日期条目（storage/logs/app-YYYY-MM-DD.log） */
export interface LogDay {
  date: string
  bytes: number
}

export interface LogDaysResult {
  days: LogDay[]
}
