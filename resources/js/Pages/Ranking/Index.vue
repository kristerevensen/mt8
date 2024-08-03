<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps } from "vue";
</script>

<template>
  <Head title="Ranking" />
  <AppLayout title="Ranking">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">Ranking</h2>
    </template>
    <div>
      <div class="py-10 mx-auto max-w-7xl sm:px-6 lg:px-8"></div>
    </div>
  </AppLayout>
</template>
