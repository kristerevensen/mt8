<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  projects: Array,
  minDate: String,
  maxDate: String,
});

const form = useForm({
  project_code: '',
  start_date: '',
  end_date: '',
});

const unavailableDates = ref([]);

const isDateRangeUnavailable = computed(() =>
  unavailableDates.value.some(dateRange =>
    form.start_date >= dateRange.start && form.end_date <= dateRange.end
  )
);

function submitForm() {
  form.post('/gsc/fetch-data');
}

</script>

<template>
  <Layout>
    <template #header>
      <h1>Google Search Console - Hent Data</h1>
    </template>

    <template #default>
      <form @submit.prevent="submitForm">
        <div class="form-group">
          <label for="project_code">Velg Prosjekt:</label>
          <select v-model="form.project_code" required>
            <option v-for="project in projects" :key="project.code" :value="project.code">
              {{ project.name }}
            </option>
          </select>
        </div>
        <div class="form-group">
          <label for="start_date">Start Dato:</label>
          <input type="date" v-model="form.start_date" :min="minDate" :max="form.end_date" required />
        </div>
        <div class="form-group">
          <label for="end_date">Slutt Dato:</label>
          <input type="date" v-model="form.end_date" :min="form.start_date" :max="maxDate" required />
        </div>
        <button type="submit" :disabled="isDateRangeUnavailable" class="btn btn-primary">Hent Data</button>
      </form>
    </template>
  </Layout>
</template>

<style scoped>
.form-group {
  margin-bottom: 15px;
}

.btn {
  background-color: #007bff;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
}

.btn:disabled {
  background-color: #6c757d;
}

</style>
