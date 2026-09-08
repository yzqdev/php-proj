<template>
  <v-dialog
        v-model="appState.showTaskDialog"
        persistent
        width="1024"
      >
        <template v-slot:activator="{ props }">
          <v-layout-item
            model-value position="bottom"
            class="text-end"
            size="88"
          >
          <div class="ma-4">
            <v-btn
              icon="mdi-plus"
              size="large"
              color="secondary"
              elevation="8"
              @click="appState.toggleTaskDialog()"
            />
          </div>
        </v-layout-item>
        </template>
        <v-card class="pa-4">
          <v-card-text>
            <v-card-title>
              <span class="text-h5">{{taskState.task.id ? 'Edit Task' : 'Add Task'}}</span>
            </v-card-title>
            <v-container>
                <v-form v-model="form" @submit.prevent="onSubmit">
                    <p v-if="error" class="text-caption text-red text-center pb-6">{{ error }}</p>
                    <v-row>
                        <v-col
                        cols="12"
                        sm="6"
                        md="6"
                        >
                            <v-text-field
                                label="Title*"
                                variant="outlined"
                                required
                                v-model="taskState.task.title"
                                :rules="[required]"
                            ></v-text-field>

                            <v-textarea
                                clearable
                                label="Description*"
                                variant="outlined"
                                required
                                v-model="taskState.task.description"
                                :rules="[required]"

                            ></v-textarea>

                            <v-radio-group
                                inline
                                label="Priorty*"
                                v-model="taskState.task.priority"
                            >
                                <v-radio
                                    v-for="{value, label} in priorityLevelOptions"
                                    :key="value"
                                    :value="value"
                                    :label="label"
                                />
                            </v-radio-group>
                        </v-col>

                        <v-col
                        cols="12"
                        sm="6"
                        md="6"
                        >
                            <VueDatePicker
                                class="mb-6"
                                clearable
                                placeholder="Select Due Date"
                                ignore-time-validation
                                teleport-center
                                hide-input-icon
                                :hide-navigation="['time', 'minute', 'hours', 'seconds']"
                                :enable-time-picker="false"
                                format="yyyy-MM-dd"
                                :teleport="true"
                                utc="true"
                                v-model="taskState.task.due_date"
                            />

                            <v-combobox
                                chips
                                multiple
                                label="Tags"
                                :items="taskState.tags"
                                item-title="name"
                                item-value="id"
                                v-model="taskState.task.tags"
                                @update:model-value="updateTags"
                            ></v-combobox>

                            <FileUpload v-if="taskState.task.id"/>
                        </v-col>
                    </v-row>
                </v-form>
            </v-container>
            <small class="text-red">*indicates required field</small>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
              variant="text"
              @click="taskState.resetTask"
            >
              Close
            </v-btn>
            <v-btn
              color="primary"
              variant="flat"
              @click="taskState.addTask"
              :disabled="!form"
            >
              Save
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

</template>

<script setup>
import { useAppStore } from '@/stores/app';
import { useTaskStore } from '@/stores/task';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'
import FileUpload from '@/components/FileUpload.vue'

const appState = useAppStore()
const taskState = useTaskStore()

onMounted(()=> {
    taskState.getTags()
})
</script>

<script>
import { constants } from '@/helpers'
import { onMounted } from 'vue';
    export default {
        components: {
            FileUpload
        },
        data: () => ({
            form: false,
            priorityLevelOptions: constants.PRIORITY_LEVEL_OPTIONS
        }),

        methods: {
            onSubmit () {
                if (!this.form) return
            },
            required (v) {
                return !!v || 'Field is required'
            },
            async updateTags(e) {
                const taskState = useTaskStore()
                for(let i=0; i<taskState.task.tags.length; i++) {
                    const tag = taskState.task.tags[i]
                    if (typeof tag === "string") {
                        // create a tag
                        const tagNew = await taskState.createTag(tag)
                        taskState.task.tags[i] = tagNew
                    }
                }
            }
        },
    }
</script>