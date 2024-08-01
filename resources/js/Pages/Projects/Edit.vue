<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps, ref } from "vue";
import Projects from "@/Pages/Projects/components/Projects.vue";
import EditProjectForm from "@/Pages/Projects/components/EditProjectForm.vue";

import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
  project: Object,
  teams: Array,
  locations: Array,
  languages: Array,
});
</script>
<template>
  <Head title="Projects" />
  <AppLayout title="Projects">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Edit Project
      </h2>
    </template>
    <div>
      <div class="py-10 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <EditProjectForm
          :project="project"
          :teams="teams"
          :locations="locations"
          :languages="languages"
        />
      </div>
    </div>
  </AppLayout>
</template

