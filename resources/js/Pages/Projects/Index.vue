<template>
  <Head title="Projects" />
  <AppLayout title="Projects">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Projects
      </h2>
    </template>
    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-0">
        <Projects />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps, computed } from "vue";
import Projects from "@/Pages/Projects/components/Projects.vue";
const props = defineProps({
  projects: Array,
});

const user = computed(() => page.props.auth.user);
const projects = props.projects;
</script>
