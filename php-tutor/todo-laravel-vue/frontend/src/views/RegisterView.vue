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

            <v-form
              v-model="form"
              @submit.prevent="onSubmit"
            >
              <v-text-field
                v-model="name"
                :readonly="loading"
                :rules="[required]"
                class="mb-2"
                clearable
                label="Name"
              ></v-text-field>

              <v-text-field
                v-model="email"
                :readonly="loading"
                :rules="[required]"
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

              <v-text-field
                v-model="password_confirmation"
                :readonly="loading"
                :rules="[required]"
                clearable
                label="Password Confirm"
                placeholder="Confirm your password"
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
                Register
              </v-btn>


              <v-card-text class="text-center">
                Already have an account?
                <a
                  class="text-blue text-decoration-none"
                  href="#/login"
                >
                  Log in now
                </a>
              </v-card-text>
            </v-form>
          </v-card>
        </v-col>
     </v-row>
  </v-container>
</template>

<script>

  export default {
    data: () => ({
      form: false,
      username: null,
      email: null,
      password: null,
      password_confirmation: null,
      loading: false,
    }),

    methods: {
      onSubmit () {
        if (!this.form) return

        this.loading = true
        window.axios.get('/sanctum/csrf-cookie').then(response => {
          window.axios.post('/api/auth/register', {
            'name': this.name,
            'email' : this.email,
            'password' : this.password,
            'password_confirmation': this.password_confirmation
          }).then(data => {
            localStorage.setItem('access_token', data.data.token)
            this.$router.push({name: 'tasks'})
          })
        });

        setTimeout(() => (this.loading = false), 2000)
      },
      required (v) {
        return !!v || 'Field is required'
      },
    },
  }
</script>
