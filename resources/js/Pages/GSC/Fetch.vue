<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps, ref } from "vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import FetchDataForm from "./components/FetchDataForm.vue";

// Define props to accept projects from the parent component
const props = defineProps({
  projects: Array,
});

const breadcrumbs = [
  { name: "Google Search Console", href: "/gsc/index", current: false },
  { name: "Fetch Data", current: true },
];

</script>

<template>
  <Head title="Google Search Console" />
  <AppLayout title="Google Search Console">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        <Breadcrumbs :pages="breadcrumbs" />
      </h2>
    </template>
    <div>
      <div class="py-10 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Pass the projects prop to the FetchDataForm -->
        <FetchDataForm :projects="projects" />
      </div>
    </div>
  </AppLayout>
</template>
