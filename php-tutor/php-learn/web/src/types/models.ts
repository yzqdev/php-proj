/**
 * ============================================================
 * 业务模型类型（camelCase，与后端 Resource 序列化输出对齐）
 * ============================================================
 *
 * 关键点：
 *   - 字段全部小驼峰（createdAt / updatedAt / startTime）
 *   - 与后端 App\Resources\*Resource::make() 输出严格一致
 *   - 时间字段用 'YYYY-MM-DD HH:MM:SS' 字符串（不用 ISO8601）
 *
 * 对应 Java：类似 DTO / VO（View Object）
 */

/** 用户简要信息（登录后返回、JWT 载荷、/me 响应都用它） */
export interface UserSummary {
  id: number
  username: string
  email: string
  role: UserRole
}

/** 用户角色（后端目前只支持这两个） */
export type UserRole = 'admin' | 'user'

/** 用户完整信息（/me 响应） */
export interface User extends UserSummary {
  createdAt: string
}

/** 文章分类 */
export type ArticleCategory = 'php' | 'java' | 'db' | 'other'

/** 文章状态 */
export type ArticleStatus = 'published' | 'draft'

/** 文章实体 */
export interface Article {
  id: number
  title: string
  body: string
  category: ArticleCategory
  /** 分类中文标签（后端 Resource 计算字段） */
  categoryLabel: string
  status: ArticleStatus
  createdAt: string
  updatedAt: string
}

/** 新建文章请求 */
export interface CreateArticlePayload {
  title: string
  body: string
  category: ArticleCategory
  status?: ArticleStatus
}

/** 更新文章请求（部分字段可选） */
export interface UpdateArticlePayload {
  title?: string
  body?: string
  category?: ArticleCategory
  status?: ArticleStatus
}

/** 文章列表查询参数 */
export interface ArticleListQuery {
  page?: number
  pageSize?: number
  category?: ArticleCategory | ''
  status?: ArticleStatus | ''
}

/** 活动实体 */
export interface Activity {
  id: number
  title: string
  location: string | null
  startTime: string
  endTime: string | null
  description: string | null
  createdAt: string
  updatedAt: string
}

/** 新建活动请求 */
export interface CreateActivityPayload {
  title: string
  location?: string
  startTime: string
  endTime?: string
  description?: string
}

/** 更新活动请求 */
export interface UpdateActivityPayload {
  title?: string
  location?: string
  startTime?: string
  endTime?: string
  description?: string
}

/** 活动列表查询参数 */
export interface ActivityListQuery {
  page?: number
  pageSize?: number
  upcoming?: 'true' | 'false'
}
