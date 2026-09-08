
  const ARCHIVE_STATUS_OPTIONS = [
    { label: "Active", value: "active"},
    { label: "Archived", value: "archived"},
  ]

  const COMPLETION_STATUS_OPTIONS = [
    { label: "To Do", value: "toDo" },
    { label: "Completed", value: "completed" },
  ]

  const PRIORITY_LEVEL_OPTIONS = [
    { label: "Urgent", value: 1},
    { label: "High", value: 2},
    { label: "Normal", value: 3},
    { label: "Low", value: 4},
  ]

  const SORT_BY_OPTIONS = [
    { label: "Created at", value: "createdAt"},
    { label: "Completed at", value: "completedAt"},
    { label: "Priority level", value: "priorityLevel"},
    { label: "Due date", value: "dueDate"},
  ]


  const SORT_ORDER_OPTIONS = [
    { label: "Ascending", value: "asc"},
    { label: "Descending", value: "desc"},
  ]

  export default {
    ARCHIVE_STATUS_OPTIONS,
    COMPLETION_STATUS_OPTIONS,
    PRIORITY_LEVEL_OPTIONS,
  }