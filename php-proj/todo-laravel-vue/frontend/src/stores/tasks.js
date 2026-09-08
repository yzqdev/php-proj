import { defineStore } from "pinia";
import { useTaskStore } from "./task";
import { useTaskFiltersStore } from "./taskFilters";

export const useTasksStore = defineStore('tasks', {
    state: () => ({
        tasks: [],
        isLoading: false,
        currentPage: null,
        lastPage: null,
    }),
    getters: {
        // computed methods
    },
    actions: {
        reset () {
            this.tasks = []
        },
        async getTasks(){
            this.isLoading = true;
            const taskStore = useTaskStore();
            const taskFiltersStore = useTaskFiltersStore()
            const {data, response} = await window.axios.get('/api/task', { params : {
                page : this.currentPage,
                searchKey: taskFiltersStore.filter.name,
                status: taskFiltersStore.filter.status,
                archived: taskFiltersStore.filter.archived,
                priority: taskFiltersStore.filter.priority,
                dateFilter: taskFiltersStore.filter.dateFilter,
                sortBy: taskFiltersStore.filter.sortBy,
                sortOrder: taskFiltersStore.filter.sortOrder
            }})

            this.tasks = data.data.map((task) => {
                let prioLabel = 'low';
                switch (task.priority) {
                    case 1:
                        prioLabel = 'urgent'
                        break;
                    case 2:
                        prioLabel = 'high'
                        break;
                    case 3:
                        prioLabel = 'normal'
                        break;
                    case 4:
                        prioLabel = 'low'
                        break;
                }
                const pl = {
                    key: prioLabel,
                    value: task.priority
                }

                return {
                    ...task,
                    priorityLevel: pl
                }
            });
            this.currentPage = data.current_page;
            this.lastPage = data.last_page;
            this.isLoading = false;
        },
        async delete(id) {
            await window.axios.delete('/api/task/' + id).then(result => {
                this.getTasks()
            })
        }
    }
})