<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps, ref } from "vue";
import Projects from "@/Pages/Projects/components/Projects.vue";
import CreateProjectForm from "@/Pages/Projects/components/CreateProjectForm.vue";

import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";

// Initialiserer formdata ved hjelp av useForm hook fra Inertia
const form = useForm({
  project_name: "",
  project_domain: "",
  project_language: "",
  project_country: "",
  project_category: "",
});

const props = defineProps({
  teams: Array,
  locations: Array,
  languages: Array,
});

const submitForm = () => {
  console.log(form.data);
  form.post(route("projects.store"), {
    onSuccess: () => form.reset(), // Tilbakestiller skjemaet etter suksess
    onError: () => alert("There was an error creating the project"), // Viser feilmelding ved feil
  });
};
</script>
<template>
  <Head title="Projects" />
  <AppLayout title="Projects">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Create Project
      </h2>
    </template>
    <div>
      <div class="py-10 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <CreateProjectForm />
      </div>
    </div>
  </AppLayout>
</template

