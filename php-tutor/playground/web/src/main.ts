import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import Root from './Root.vue'
import './style.css'

// 路由只承担两件事:承载根视图,以及把 /pelican-bike 挂成独立页面。
// 图库+日志仍在 App 内用原有的 radio 切换,App.vue 没有任何改动。
// 两个页面都懒加载,不占首屏体积。
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', component: () => import('@/App.vue') },
    { path: '/pelican-bike', name: 'pelican-bike', component: () => import('@/views/PelicanBikeView.vue') },
    // 兜底回到首页,避免直接访问未知路径时白屏
    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
})

createApp(Root).use(router).mount('#app')
