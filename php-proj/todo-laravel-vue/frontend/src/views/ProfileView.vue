<template v-slot:prepend>
  <v-container fluid class="fill-height profile bg-primary">
     <v-row>
        <v-col class="d-flex justify-center">
          <v-card 
            class="mx-auto px-6 py-8" 
            min-width="400"
            title="Account Details"
          >
            <v-form
              v-model="form"
              @submit.prevent="onSubmit"
            >
              <v-text-field
                v-model="username"
                :readonly="loading"
                :rules="[required]"
                class="mb-2"
                clearable
                label="Username"
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
              ></v-text-field>

              <v-text-field
                v-model="confirmPassword"
                :readonly="loading"
                :rules="[required]"
                clearable
                label="Confirm Password"
                placeholder="Confirm your password"
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
                UPDATE
              </v-btn>
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
      confirmPassword: null,
      loading: false,
    }),

    methods: {
      onSubmit () {
        if (!this.form) return

        this.loading = true

        setTimeout(() => (this.loading = false), 2000)
      },
      required (v) {
        return !!v || 'Field is required'
      },
    },
  }
</script>
