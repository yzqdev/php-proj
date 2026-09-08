import { fileURLToPath, url } from 'node:url'
import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import AutoImport from 'unplugin-auto-import/vite'
import Components from 'unplugin-vue-components/vite'
import { ElementPlusResolver } from 'unplugin-vue-components/resolvers'

/**
 * ============================================================
 * Vite 8 配置
 * ============================================================
 *
 * 关键插件：
 *   - @vitejs/plugin-vue        Vue 3 SFC 编译
 *   - unplugin-auto-import      自动导入 API（ref / computed / 等）
 *   - unplugin-vue-components   自动导入组件（ElButton / ElTable 等）
 *   - ElementPlusResolver       Element Plus 组件解析器
 *
 * 关键配置：
 *   - resolve.alias '@'  → src/（对应 tsconfig 里的 paths）
 *   - server.port 5173   默认端口，与后端 CORS 白名单对齐
 *   - 生产环境 build 时开启 code splitting
 *
 * 对应 Java / Spring Boot：
 *   - 类似 webpack.config.js 的 role
 *   - Vite 5+ 用 ESM 配置文件
 */
export default defineConfig(({ mode }) => {
  // loadEnv 按当前 mode（development / production）读 .env.* 文件
  const env = loadEnv(mode, process.cwd(), '')

  return {

    // 用 fileURLToPath 而不是 path.resolve（ESM 下的现代写法）
    resolve: {
      alias: {
        '@': fileURLToPath(new URL('./src', import.meta.url)),
      },
    },

    plugins: [
      vue(),
      tailwindcss(),
      // Element Plus 组件自动导入
      AutoImport({
        resolvers: [
          ElementPlusResolver({
            // 自动导入样式（css | less | scss）
            importStyle: 'css',
          }),
        ],
      }),
      Components({
        resolvers: [
          ElementPlusResolver({
            importStyle: 'css',
          }),
        ],
      }),
    ],

    server: {
      // 前端开发服务器监听端口
      // 后端 CORS 白名单里已经有 http://localhost:5173
      port: 5122,
      host: '127.0.0.1',
      open: false,

      // 不用 proxy：直接用 http://127.0.0.1:8000/api/v1 走 CORS
      // 好处：跨域逻辑与生产一致，出问题早暴露
    },

    preview: {
      // pnpm preview 时监听端口
      port: 4173,
      host: '127.0.0.1',
    },

    build: {
      // 生产构建输出
      outDir: 'dist',
      sourcemap: mode === 'development',
      // 手动分包：把大的第三方库单独打一份
      // Vite 8 (Rolldown) 要求 manualChunks 是函数形式，不是对象
      rollupOptions: {
        output: {
          manualChunks(id) {
            if (id.includes('node_modules')) {
              if (id.includes('vue') || id.includes('vue-router') || id.includes('pinia')) {
                return 'vue-vendor'
              }
              if (id.includes('axios')) {
                return 'axios-vendor'
              }
              if (id.includes('element')) {
                return 'element-vendor'
              }
              return 'vendor'
            }
          },
        },
      },
      // 大文件阈值提示
      chunkSizeWarningLimit: 800,
    },

    // 从 env 里读的 VITE_* 会注入到 import.meta.env
    define: {
      __API_BASE_URL__: JSON.stringify(env.VITE_API_BASE_URL || '/api/v1'),
    },
  }
})