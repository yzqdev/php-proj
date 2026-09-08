import { defineStore } from "pinia";
import { useTasksStore } from "./tasks";
import { useAppStore } from "./app";

export const useTaskStore = defineStore('task', {
    state: () => ({
        task: {
            priority: 4,
            due_date: new Date(),
            tags: []
        },
        attachments: [],
        tags: []
    }),
    getters: {
        // computed methods
    },
    actions: {
        addTask() {
            const tasksStore = useTasksStore();
            const appStore = useAppStore();
            if (this.task.id == null ) {

                window.axios.post('/api/task', {...this.task,
                    status: 0,
                    is_archived: false
                })
                .then(()=> {
                    tasksStore.getTasks()
                    appStore.showTaskDialog = false;
                    appStore.snackbarText = 'Task is successfully created'
                    appStore.showSnackbar = true
                    this.task = {priority: 4}
                })
                .catch((errors) => {
                    console.log(errors);
                    if (errors) {}
                });

            } else {

                this.updateTask()

            }
        },
        updateTask() {
            const tasksStore = useTasksStore();
            const taskStore = useTaskStore();
            const appStore = useAppStore();

            window.axios.put('/api/task/'+this.task.id, this.task)
            .then(()=> {
                tasksStore.getTasks()
                appStore.showTaskDialog = false;
                appStore.snackbarText = 'Task is successfully updated'
                appStore.toggleSnackbar()
                this.task = {}
            })
            .catch((errors) => {
                console.log(errors);
                if (errors) {}
            });
        },
        resetTask() {
            this.task = {
                priority: 3,
                due_date: new Date()
            }

            useAppStore().toggleTaskDialog()
        },
        getTaskAttachments() {
            if (this.task?.id) {
                window.axios.get('/api/task/' + this.task.id + '/attachment' ).then((response) => {
                    this.attachments = response.data.data
                })
            }
        },
        deleteAttachment(attachment) {
            const appStore = useAppStore();
            appStore.snackbarText = 'Deleting file...'
            appStore.snackbarColor = 'info'
            appStore.toggleSnackbar()
            window.axios.delete('/api/task/' + this.task.id + '/attachment/'+attachment.id ).then((response) => {
                this.getTaskAttachments()
            })
        },
        getTags() {
            window.axios.get('/api/tag')
            .then((response)=> {
                this.tags = response.data
                console.log(response)
            })
        },
        async getTaskTags(taskId) {
            const { data } = await window.axios.get('/api/task/'+taskId+'/tag')
            return data;
        },
        async createTag(tagName) {
            const { data: response } = await window.axios.post('/api/tag', {'name': tagName})
            return response
        }

    }
})