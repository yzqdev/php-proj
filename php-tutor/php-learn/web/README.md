# php-learn 前端 SPA

前后端分离的 Vue 3 + TypeScript + Vite 8 + TailwindCSS v4 应用。

## 快速开始

```bash
pnpm install
pnpm dev
# 打开 http://localhost:5173
```

## 技术栈

| 组件 | 版本 | 用途 |
|---|---|---|
| Vue | 3.5+ | SPA 框架 |
| TypeScript | 5.9+ | 严格类型 |
| Vite | 8+ | 构建工具 |
| TailwindCSS | 4+ | 原子化 CSS（`@tailwindcss/vite` 插件，无 `tailwind.config.js`） |
| Pinia | 4+ | 状态管理 |
| Vue Router | 5+ | 路由 |
| Axios | 1.7+ | HTTP 客户端 |

## 目录结构

```
client/
├── src/
│   ├── api/           # axios 封装 + 各模块 API（auth/article/activity）
│   ├── components/    # 通用组件
│   ├── composables/   # 组合式函数（useApiResource 等）
│   ├── router/        # 路由 + 守卫
│   ├── stores/        # Pinia stores
│   ├── styles/        # 全局样式（Tailwind v4 @theme）
│   ├── types/         # 类型定义
│   ├── utils/         # 工具函数
│   └── views/         # 页面
├── public/            # 静态资源
├── .env.*             # 环境变量
└── docs/              # 参考配置（如 nginx.conf.example）
```

## 关键约定

- **API 响应统一 `{code, message, data}`**：`src/api/request.ts` 拦截器自动拆壳，调用方只拿到 `data`
- **鉴权：Bearer Token**：Token 存 `localStorage`，`Authorization: Bearer xxx` 请求头
- **401 自动 refresh**：Access Token 过期时，拦截器自动用 Refresh Token 换新，成功后重试原请求
- **TS 严格模式**：禁 `any`，所有 API 函数带泛型
- **加载状态由调用方管**：`useApiResource<T>()` composable 统一处理

## 环境变量

| 变量 | 开发 | 生产 |
|---|---|---|
| `VITE_API_BASE_URL` | `http://127.0.0.1:8000/api/v1` | `/api/v1` |

## 生产部署

见 [`docs/nginx.conf.example`](./docs/nginx.conf.example)：
- `/` 与 `/static/*` 服务前端静态文件
- `/api/v1/*` 反向代理到 PHP 后端
- `/uploads/*` 服务上传文件
