<template v-slot:prepend>
  <v-container fluid class="fill-height login bg-primary">
     <v-row>
        <v-col class="d-flex justify-center">
          <v-card
            class="mx-auto px-6 py-8"
            min-width="344"
          >
            <v-img
              :src="require('../assets/task-master-4.png')"
              class="mb-2"
              contain
            />

            <p v-if="error" class="text-caption text-red text-center pb-6">
              {{ error }}
            </p>

            <v-form
              v-model="form"
              @submit.prevent="onSubmit"
            >
              <v-text-field
                v-model="email"
                :readonly="loading"
                :rules="validEmailRegex"
                class="mb-2"
                clearable
                label="Email"
              ></v-text-field>

              <v-text-field
                v-model="password"
                :readonly="loading"
                :rules="[required]"
                clearable
                label="Password"
                placeholder="Enter your password"
                type="password"
              ></v-text-field>

              <br>

              <v-btn
                :disabled="!form"
                :loading="loading"
                block
                color="secondary"
                size="large"
                type="submit"
                variant="elevated"
              >
                Log In
              </v-btn>

              <v-card-text class="text-center">
                No account yet?
                <a
                  class="text-blue text-decoration-none"
                  href="#/register"
                >
                  Register now
                </a>
              </v-card-text>
            </v-form>
          </v-card>
        </v-col>
     </v-row>
  </v-container>
</template>

<script>
import { useTasksStore } from '@/stores/tasks';
import { useUserStore } from '@/stores/user';

  const userStore = useUserStore();
  const taskStore = useTasksStore();

  export default {
    data: () => ({
      form: false,
      email: null,
      validEmailRegex: [
        v => !!v || 'Email is required',
        v => /^[a-z.-]+@[a-z.-]+\.[a-z]+$/i.test(v) || 'Email is not valid'
      ],
      password: null,
      loading: false,
      error: null,

    }),

    methods: {
      onSubmit () {
        if (!this.form) return

        this.loading = true

        window.axios.get('/sanctum/csrf-cookie').then(response => {
           axios.post('api/auth/login', {
            'email' : this.email,
            'password' : this.password
          }).then(data => {
            localStorage.setItem('access_token', data.data.token)
            userStore.getCurrentUser();
            taskStore.reset();
            this.$router.push({name: 'tasks'})
          }).catch((error) => {
            if(error.response.status == 401) {
              this.error = "Login failed. Please check your credentials.";
            }
            this.loading = false;
          })
        })
        // setTimeout(() => (this.loading = false), 2000)
      },
      required (v) {
        return !!v || 'Field is required'
      },
    },
  }
</script>
