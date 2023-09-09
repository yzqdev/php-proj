import { defineStore } from "pinia";

export const useAppStore = defineStore('app', {
    state: () => ({
        showTaskDialog: false,
        showSnackbar: false,
        snackbarText: '',
        snackbarColor: 'success'
    }),
    getters: {
    },
    actions: {
        reset () {
            this.showTaskDialog = false;
        },
        toggleTaskDialog() {
            this.showTaskDialog = !this.showTaskDialog
        },
        toggleSnackbar() {
            this.showSnackbar = !this.showSnackbar
        }
    }
})