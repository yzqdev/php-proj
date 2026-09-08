/** 后端统一响应结构的壳:{ success, message?, data? } */
export interface ApiEnvelope<T = unknown> {
  success: boolean
  message?: string
  data?: T
}

/** 图床中的一张图片(对应 PHP 侧 Image DTO) */
export interface ImageItem {
  /** 随机生成的存储名,删除时作为标识 */
  name: string
  /** 用户上传时的原始文件名 */
  original: string
  /** 字节数 */
  size: number
  /** 上传时间(Unix 秒) */
  time: number
  /** 站内相对外链,如 /i/xxx.png;拼上 API 基础地址后可直接引用 */
  url: string
}
