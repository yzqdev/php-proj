/**
 * ============================================================
 * 应用入口
 * ============================================================
 *
 * 挂载顺序：
 *   1. Element Plus（组件库 + 中文包）
 *   2. Pinia（状态管理）
 *   3. Router（路由 + 守卫）
 *   4. App.vue（根组件）
 *
 * 对应 Java：类似 main() 方法里的 SpringApplication.run()
 */
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import ElementPlus from 'element-plus'
import zhCn from 'element-plus/dist/locale/zh-cn'
import * as ElementPlusIconsVue from '@element-plus/icons-vue'

import App from './App.vue'
import router from './router'

import './styles/index.css'

// 创建 Vue 应用实例
const app = createApp(App)

// Element Plus：组件库 + 中文语言包
app.use(ElementPlus, { locale: zhCn })

// 注册所有 Element Plus 图标组件（<ElIcon><Edit /></ElIcon>）
for (const [key, component] of Object.entries(ElementPlusIconsVue)) {
  app.component(key, component)
}

// Pinia：状态管理（对应 Redux / Vuex 的现代替代）
app.use(createPinia())

// Router：Vue Router 5（对应 React Router / Angular Router）
app.use(router)

// 挂载到 index.html 的 <div id="app"></div>
app.mount('#app')