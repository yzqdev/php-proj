<template>
    <v-snackbar
    v-model="appStore.showSnackbar"
    :timeout="3000"
    :color="appStore.snackbarColor"
  >
    {{ appStore.snackbarText }}
    <template v-slot:actions>
      <v-btn
        color="blue"
        variant="text"
        @click="appStore.toggleSnackbar()"
      >
        Close
      </v-btn>
    </template>
  </v-snackbar>
</template>
<script setup>
import { useAppStore } from '@/stores/app';
const appStore = useAppStore()
</script>