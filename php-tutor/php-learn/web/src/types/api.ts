/**
 * ============================================================
 * 通用 API 类型
 * ============================================================
 *
 * 后端统一响应壳：{ code, message, data, errors? }
 *
 * 业务 code 约定（与后端保持一致）：
 *   - 0       成功
 *   - 40101   未提供访问令牌
 *   - 40102   令牌无效或已过期
 *   - 40103   用户不存在
 *   - 40301   权限不足
 *   - 40401   资源不存在
 *   - 42201   参数校验失败
 *   - 50000   内部错误
 *
 * 对应 Java：类似 Result<T> / PageResult<T> / ErrorResponse<T>
 */

/** 统一响应壳（拦截器会拆成只返回 data，但类型定义保留完整结构） */
export interface ApiEnvelope<T> {
  /** 业务 code（0 = 成功） */
  code: number
  /** 中文描述 */
  message: string
  /** 数据体（失败时为 null） */
  data: T | null
  /** 字段级错误（仅 422 时存在） */
  errors?: FieldErrors
}

/** 字段级错误：键名与前端表单字段名一致 */
export type FieldErrors = Record<string, string[]>

/** 分页响应结构 */
export interface PageResult<T> {
  list: T[]
  total: number
  page: number
  pageSize: number
  totalPage: number
}

/** 分页查询参数 */
export interface PageQuery {
  page?: number
  pageSize?: number
}

/** 登录响应 */
export interface LoginResponse {
  accessToken: string
  refreshToken: string
  tokenType: 'Bearer'
  expiresIn: number
  user: UserSummary
}

/** 刷新令牌响应 */
export interface RefreshResponse {
  accessToken: string
  refreshToken: string
  tokenType: 'Bearer'
  expiresIn: number
}

/** 业务异常（拦截器抛出） */
export interface BusinessError {
  code: number
  message: string
  errors?: FieldErrors
  httpStatus: number
}

/** 登录请求参数 */
export interface LoginPayload {
  login: string
  password: string
}

/** 刷新请求参数 */
export interface RefreshPayload {
  refreshToken: string
}
