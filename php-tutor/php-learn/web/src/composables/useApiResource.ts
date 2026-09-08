/**
 * ============================================================
 * 通用 API 列表 composable
 * ============================================================
 *
 * 使用方式：
 *   const { data, total, page, pageSize, totalPage, loading, error, fetch, next, prev, toPage } =
 *     useApiResource({
 *       fetcher: articleApi.list,
 *       defaultQuery: { pageSize: 10 },
 *     })
 *
 * 特点：
 *   - 统一 loading / error 状态
 *   - 提供 next/prev/toPage 便捷翻页
 *   - 组件挂载时自动加载
 *
 * 对应 VueUse：
 *   - useFetch + usePagination 的组合版
 *   - 但本项目的 envelope 拆壳逻辑已在 request.ts 拦截器里
 */
import { computed, ref, type Ref } from 'vue'
import type { PageQuery, PageResult } from '@/types/api'
import type { AxiosRequestConfig } from 'axios'
import { BusinessError } from '@/api/request'

/** 类型：fetcher 函数签名 */
type Fetcher<T, Q extends PageQuery> = (query: Q, config?: AxiosRequestConfig) => Promise<PageResult<T>>

export interface UseApiResourceOptions<T, Q extends PageQuery> {
  /** 获取数据的 API 函数（如 articleApi.list） */
  fetcher: Fetcher<T, Q>
  /** 默认查询参数（会与外部传入的 query 合并） */
  defaultQuery?: Partial<Q>
  /** 是否在组件挂载时自动加载 */
  autoLoad?: boolean
  /** 组件是否可见（ref，false 时不加载） */
  enabled?: Ref<boolean>
}

export interface UseApiResourceResult<T, Q extends PageQuery> {
  /** 当前页数据列表 */
  data: Ref<T[]>
  /** 总条数 */
  total: Ref<number>
  /** 当前页码 */
  page: Ref<number>
  /** 每页条数 */
  pageSize: Ref<number>
  /** 总页数 */
  totalPage: Ref<number>
  /** 是否加载中 */
  loading: Ref<boolean>
  /** 错误消息（null 表示无错误） */
  error: Ref<string | null>
  /** 字段错误（422 时非空） */
  fieldErrors: Ref<Record<string, string[]> | null>
  /** 执行请求 */
  fetch: (queryOverride?: Partial<Q>) => Promise<void>
  /** 下一页 */
  next: () => Promise<void>
  /** 上一页 */
  prev: () => Promise<void>
  /** 跳到指定页 */
  toPage: (p: number) => Promise<void>
  /** 是否可下一页 */
  canNext: Ref<boolean>
  /** 是否可上一页 */
  canPrev: Ref<boolean>
}

export function useApiResource<T, Q extends PageQuery = PageQuery>(
  options: UseApiResourceOptions<T, Q>,
): UseApiResourceResult<T, Q> {
  const { fetcher, defaultQuery, autoLoad = true, enabled } = options

  const data = ref<T[]>([]) as Ref<T[]>
  const total = ref(0)
  const page = ref(1)
  const pageSize = ref((defaultQuery?.pageSize as number | undefined) ?? 8)
  const totalPage = ref(0)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const fieldErrors = ref<Record<string, string[]> | null>(null)

  const canNext = computed(() => page.value < totalPage.value)
  const canPrev = computed(() => page.value > 1)

  async function fetch(queryOverride?: Partial<Q>): Promise<void> {
    loading.value = true
    error.value = null
    fieldErrors.value = null

    try {
      // 合并默认查询与外部查询
      const merged: Q = {
        ...(defaultQuery as Q),
        ...(queryOverride ?? {}),
      } as Q
      // 保证 page 和 pageSize 有值
      if (!merged.page) merged.page = page.value as never
      if (!merged.pageSize) merged.pageSize = pageSize.value as never

      const result = await fetcher(merged)

      // 更新 state
      data.value = result.list ?? []
      total.value = result.total
      page.value = result.page
      pageSize.value = result.pageSize
      totalPage.value = result.totalPage
    } catch (e) {
      if (e instanceof BusinessError) {
        error.value = e.message
        fieldErrors.value = e.errors ?? null
      } else {
        error.value = e instanceof Error ? e.message : '未知错误'
      }
    } finally {
      loading.value = false
    }
  }

  async function next(): Promise<void> {
    if (canNext.value) {
      page.value += 1
      await fetch({ page: page.value } as Partial<Q>)
    }
  }

  async function prev(): Promise<void> {
    if (canPrev.value) {
      page.value -= 1
      await fetch({ page: page.value } as Partial<Q>)
    }
  }

  async function toPage(p: number): Promise<void> {
    if (p >= 1 && p <= totalPage.value) {
      page.value = p
      await fetch({ page: p } as Partial<Q>)
    }
  }

  // 自动加载：组件挂载时执行
  if (autoLoad) {
    if (enabled && !enabled.value) {
      // 未启用时不加载
    } else {
      void fetch()
    }
  }

  return {
    data,
    total,
    page,
    pageSize,
    totalPage,
    loading,
    error,
    fieldErrors,
    fetch,
    next,
    prev,
    toPage,
    canNext,
    canPrev,
  }
}
