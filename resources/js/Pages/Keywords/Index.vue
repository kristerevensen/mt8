<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps } from "vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";

const breadcrumbs = [
  { name: "All Keywords", href: "/keywords", current: true },
];
</script>

<template>
  <Head title="Keywords" />
  <AppLayout title="Keywords">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        <Breadcrumbs :pages="breadcrumbs" />
      </h2>
    </template>
    <div>
      <div class="py-10 mx-auto max-w-7xl sm:px-6 lg:px-8"></div>
    </div>
  </AppLayout>
</template>
