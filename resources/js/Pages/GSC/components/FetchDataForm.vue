<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";

// Define props to accept projects from the parent component
const props = defineProps({
  projects: Array,
});

const form = useForm({
  project_id: '',
  start_date: '',
  end_date: '',
});

const submitForm = () => {
  form.post('/gsc/fetch-data');
};
</script>

<template>
  <form @submit.prevent="submitForm">
    <div>
      <label for="project_id">Velg Prosjekt:</label>
      <select v-model="form.project_id" required>
        <option v-for="project in projects" :key="project.id" :value="project.id">
          {{ project.name }}
        </option>
      </select>
    </div>

    <div class="mt-4">
      <label for="start_date">Start Dato:</label>
      <input type="date" v-model="form.start_date" required />
    </div>

    <div class="mt-4">
      <label for="end_date">Slutt Dato:</label>
      <input type="date" v-model="form.end_date" required />
    </div>

    <div class="mt-6">
      <button type="submit" class="px-4 py-2 text-white bg-blue-500 rounded-md">Hent Data</button>
    </div>
  </form>
</template>
