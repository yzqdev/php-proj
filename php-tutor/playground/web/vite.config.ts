import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import tailwindcss from '@tailwindcss/vite'
import AutoImport from 'unplugin-auto-import/vite'
import Components from 'unplugin-vue-components/vite'
import { ElementPlusResolver } from 'unplugin-vue-components/resolvers'

// 注意:这里刻意不配置 server.proxy —— 前后端分离,跨域由 PHP 后端的 CORS 中间件处理,
// 前端通过 VITE_API_BASE_URL 直连 API。
export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
    tailwindcss(),
    // 自动导入 Vue API(ref/computed 等)与 Element Plus 的 ElMessage/ElMessageBox
    AutoImport({
      imports: ['vue'],
      resolvers: [ElementPlusResolver()],
      dts: 'src/auto-imports.d.ts',
    }),
    // 按模板中的组件用法自动按需引入 Element Plus 组件及其样式
    Components({
      resolvers: [ElementPlusResolver()],
      dts: 'src/components.d.ts',
    }),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
})
