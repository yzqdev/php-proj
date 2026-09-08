<template>
  <Tasks />
</template>

<script>
import { defineComponent } from 'vue';

// Components
import Tasks from '../components/Tasks.vue';

export default defineComponent({
  name: 'TasksView',

  components: {
    Tasks
  },
});
</script>
