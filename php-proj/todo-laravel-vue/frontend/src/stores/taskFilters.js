import { defineStore } from "pinia";

const defaultFilterState = {
    name: null,
    priority: null,
    due_date: null,
    status: null,
    archived: null,
    dateFilter: null,
    sortOrder: null,
    sortBy: null
}

export const useTaskFiltersStore = defineStore('taskFilters', {
    state: () => ({
        filter: {...defaultFilterState}
    }),
    actions: {
        resetFilters() {
            this.filter = {...defaultFilterState}
        }
    }
})