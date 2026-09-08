/// <reference types="vite/client" />

interface ImportMetaEnv {
  /** PHP API 的基础地址,直连后端,不经过任何代理 */
  readonly VITE_API_BASE_URL: string
}

interface ImportMeta {
  readonly env: ImportMetaEnv
}
