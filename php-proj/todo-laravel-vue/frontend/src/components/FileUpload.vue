<template>
    <v-file-input
        chips
        multiple
        :clearable="false"
        label="Files"
        variant="outlined"
        prepend-inner-icon="mdi-paperclip"
        :prepend-icon="null"
        @change="upload"
    >
    </v-file-input>
    <small v-if="errorText" class="text-red">{{ errorText }}</small>
    <v-container v-if="taskStore.attachments.length">
        <v-row no-gutters>
            <v-col>
                <label for="attachments">Attachments:</label>
                <ul>
                    <li
                        v-for="file in taskStore.attachments"
                    >
                        <v-chip class="mt-2" closable @click:close="deleteAttachment(file)">
                            <a :href="'http://localhost/api/task/'+file.task_id+'/attachment/'+file.id+'/download'" target="_blank">{{ file.filename }}</a>
                        </v-chip>
                    </li>
                </ul>
            </v-col>
        </v-row>
    </v-container>
  </template>

<script setup>
import { useAppStore } from '@/stores/app';
import { useTaskStore } from '@/stores/task';
import { ref } from 'vue';
import { onMounted } from 'vue';

const taskStore = useTaskStore()
const appState = useAppStore()
const errorText = ref('')

onMounted(() => {
  taskStore.getTaskAttachments()
})

function upload(e) {
    const taskState = useTaskStore()
    errorText.value = ''
    var files = e.target.files
    if (files.length > 0) {
        for(let i = 0; i<files.length; i++) {
            const fd = new FormData()
            fd.append('file', files[i])
            appState.snackbarText = 'Uploading files'
            appState.snackbarColor = 'info'
            appState.toggleSnackbar()
            window.axios.post('/api/task/'+taskState.task.id+'/attachment', fd)
            .then((response) => {
                taskStore.getTaskAttachments()
            }).catch((error) => {
                if (error.response.status == 422) {
                    errorText.value = error.response.data.message
                }
            });
        }
    }
}

function deleteAttachment(attachment) {
    taskStore.deleteAttachment(attachment)
}

</script>
<style>
ul {
    list-style: none;
}
</style>