import { createRouter, createWebHashHistory } from 'vue-router'
import TasksView from '../views/TasksView.vue'
import { useUserStore } from '@/stores/user'

const routes = [
  {
    path: '/',
    name: 'tasks',
    component: TasksView
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('../views/RegisterView.vue')
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/LoginView.vue')
  },
  {
    path: '/tags',
    name: 'tags',
    // route level code-splitting
    // this generates a separate chunk (tags.[hash].js) for this route
    // which is lazy-loaded when the route is visited.
    component: () => import(/* webpackChunkName: "tags" */ '../views/TagsView.vue')
  },
  {
    path: '/profile',
    name: 'profile',
    component: () => import('../views/ProfileView.vue')
  },
]

const router = createRouter({
  history: createWebHashHistory(),
  routes
})

router.beforeEach(async(to, from) => {
  const userStore = useUserStore();
  if(await userStore.getCurrentUser() === 401 && !['login', 'register'].includes(to.name)) {
     // redirect the user to the login page
     return { name: 'login' }
  } else {
    return true;
  }
})

export default router
