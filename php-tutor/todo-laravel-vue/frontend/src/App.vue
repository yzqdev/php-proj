<script setup>
import { useRoute } from 'vue-router'
import { useUserStore } from './stores/user';
import { onMounted } from 'vue';
import { useTasksStore } from './stores/tasks';

const taskStore = useTasksStore();
const userStore = useUserStore();


onMounted(() => {
  userStore.getCurrentUser();
})

const route = useRoute()
</script>

<script>


export default {
  data: () => ({
    drawer: true,
    rail: true,
    items: [
        {
          title: 'Tasks',
          key: 2,
          icon: 'mdi-format-list-checks',
          to: '/',
        },
        {
          title: 'Tags',
          key: 3,
          icon: 'mdi-tag-outline',
          to: '/tags',
          color: 'secondary'
        },
        {
          title: 'My Account',
          key: 1,
          icon: 'mdi-account',
          to: "/profile",
        },
      ]
  }),
  methods: {
    logout() {
      window.axios.get('http://localhost:80/sanctum/csrf-cookie').then(response => {
          window.axios.post('/api/auth/logout').then(response => {
          this.$router.push({name: 'login'});
        });
      })
    }
  }
}
</script>
<template>
  <v-app>
    <v-navigation-drawer
      app
      permanent
      v-if="!['/login', '/register'].includes(route.path)"
      v-model="drawer"
      :rail="rail"
      @click="rail = false"
      :dark="false"
    >
      <v-list-item>
        <v-img
          :src="!rail ? require('@/assets/task-master-4.png') : require('@/assets/task-master-icon.png')"
          contain
          min-width="40px"
          max-height="80px"
          @click.stop="rail = !rail"
        />
      </v-list-item>

      <v-list-item
        prepend-avatar="https://randomuser.me/api/portraits/men/85.jpg"
        :title="userStore.user.name"
        nav
      >
        <template v-slot:append>
          <v-btn
            variant="text"
            icon="mdi-chevron-left"
            @click.stop="rail = !rail"
          ></v-btn>
        </template>
      </v-list-item>

      <v-divider></v-divider>

      <v-list density="compact" nav>
        <v-list-item
          v-for="(item, index) in items"
          :key="item.key"
          :prepend-icon="item.icon"
          :title="item.title"
          :to="item.to"
          link
        ></v-list-item>
      </v-list>

      <template v-slot:append>
        <div class="pa-2">
          <v-btn
            block
            color="primary"
            v-if="!rail"
            @click="logout"
          >Logout</v-btn>
        </div>
      </template>
    </v-navigation-drawer>

    <v-main>
      <router-view></router-view>
    </v-main>
  </v-app>
</template>


