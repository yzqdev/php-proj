/**
 * ============================================================
 * 文章表单 composable（新建 + 编辑共用）
 * ============================================================
 *
 * 使用方式：
 *   const { form, errors, submitting, submit, cancel, loadFromExisting } =
 *     useArticleForm({
 *       onSuccess: (id) => router.push(`/articles/${id}`),
 *     })
 *
 * 关键设计：
 *   - form 是 reactive，可以直接 v-model
 *   - errors 与后端返回的 FieldErrors 对齐
 *   - 前端预校验 + 后端二次校验（双重保险）
 *   - mode='create' | 'edit' 控制调用的 API
 */
import { reactive, ref } from 'vue'
import { articleApi } from '@/api/modules/article'
import { BusinessError } from '@/api/request'
import type { Article, ArticleCategory, ArticleStatus } from '@/types/models'

export interface UseArticleFormOptions {
  /** 提交成功后的回调（通常跳回列表或详情页） */
  onSuccess: (id: number) => void
}

export interface UseArticleFormResult {
  /** 表单数据（v-model 绑定用） */
  form: {
    title: string
    body: string
    category: ArticleCategory
    status: ArticleStatus
  }
  /** 字段级错误（与后端 FieldErrors 一致） */
  errors: Record<string, string[]>
  /** 是否提交中 */
  submitting: ReturnType<typeof ref<boolean>>
  /** 是否编辑模式 */
  isEdit: ReturnType<typeof ref<boolean>>
  /** 当前编辑的文章 ID */
  articleId: ReturnType<typeof ref<number | null>>
  /** 从已有文章填充表单（编辑模式） */
  loadFromExisting: (article: Article) => void
  /** 校验表单 */
  validate: () => boolean
  /** 提交表单 */
  submit: () => Promise<void>
  /** 重置表单 */
  reset: () => void
}

const EMPTY_FORM = {
  title: '',
  body: '',
  category: 'php' as ArticleCategory,
  status: 'published' as ArticleStatus,
}

export function useArticleForm(options: UseArticleFormOptions): UseArticleFormResult {
  const { onSuccess } = options

  const form = reactive({ ...EMPTY_FORM })
  const errors = ref<Record<string, string[]>>({})
  const submitting = ref(false)
  const isEdit = ref(false)
  const articleId = ref<number | null>(null)

  function loadFromExisting(article: Article): void {
    form.title = article.title
    form.body = article.body
    form.category = article.category
    form.status = article.status
    articleId.value = article.id
    isEdit.value = true
  }

  function validate(): boolean {
    const errs: Record<string, string[]> = {}

    // title: 2-80 字符
    const title = form.title.trim()
    if (!title) {
      errs.title = ['标题不能为空']
    } else if (title.length < 2) {
      errs.title = ['标题至少 2 个字符']
    } else if (title.length > 80) {
      errs.title = ['标题最多 80 个字符']
    }

    // body: 至少 5 字符
    const body = form.body.trim()
    if (!body) {
      errs.body = ['正文不能为空']
    } else if (body.length < 5) {
      errs.body = ['正文至少 5 个字符']
    }

    // category: 必须选（默认 php，永远不会为空，但保险起见）
    if (!form.category) {
      errs.category = ['请选择分类']
    }

    // status: 必须选
    if (!form.status) {
      errs.status = ['请选择状态']
    }

    errors.value = errs
    return Object.keys(errs).length === 0
  }

  async function submit(): Promise<void> {
    // 前端预校验
    if (!validate()) return

    submitting.value = true
    try {
      let savedId: number
      if (isEdit.value && articleId.value !== null) {
        // 更新
        const result = await articleApi.update(articleId.value, {
          title: form.title.trim(),
          body: form.body.trim(),
          category: form.category,
          status: form.status,
        })
        savedId = result.id
      } else {
        // 新建
        const result = await articleApi.create({
          title: form.title.trim(),
          body: form.body.trim(),
          category: form.category,
          status: form.status,
        })
        savedId = result.id
      }
      onSuccess(savedId)
    } catch (e) {
      // 后端返回 422：绑定字段错误
      if (e instanceof BusinessError && e.errors) {
        errors.value = e.errors
      }
    } finally {
      submitting.value = false
    }
  }

  function reset(): void {
    Object.assign(form, EMPTY_FORM)
    errors.value = {}
    isEdit.value = false
    articleId.value = null
  }

  return { form, errors, submitting, isEdit, articleId, loadFromExisting, validate, submit, reset }
}
