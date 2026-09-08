/**
 * ============================================================
 * 文章 API 模块
 * ============================================================
 *
 * 对应后端：
 *   GET    /api/v1/articles          → list()
 *   GET    /api/v1/articles/{id}     → detail()
 *   POST   /api/v1/articles          → create()
 *   PUT    /api/v1/articles/{id}     → update()
 *   DELETE /api/v1/articles/{id}     → remove()
 *
 * 对应 Java：类似 ArticleController 客户端
 */
import { del, get, post, put } from '../request'
import type { PageResult } from '@/types/api'
import type { Article, ArticleListQuery, CreateArticlePayload, UpdateArticlePayload } from '@/types/models'

export const articleApi = {
  /** 列表（分页 + 分类/状态筛选） */
  list: (query?: ArticleListQuery) => get<PageResult<Article>>('/articles', { params: query }),

  /** 详情 */
  detail: (id: number) => get<Article>(`/articles/${id}`),

  /** 新建 */
  create: (payload: CreateArticlePayload) => post<Article>('/articles', payload),

  /** 更新（PUT 语义：全量替换） */
  update: (id: number, payload: UpdateArticlePayload) => put<Article>(`/articles/${id}`, payload),

  /** 删除（返回 null，HTTP 204） */
  remove: (id: number) => del<null>(`/articles/${id}`),
}
