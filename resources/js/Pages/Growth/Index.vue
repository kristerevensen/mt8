<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps } from "vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";

const breadcrumbs = [
  // set path to growth
  { name: "Growth", href: "/growth", current: true },
  // set pa
];
</script>

<template>
  <Head title="Growth" />
  <AppLayout title="Growth">
    <template #header>
      <div class="flex justify-between">
        <div>
          <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <Breadcrumbs :pages="breadcrumbs" />
          </h2>
        </div>
        <div>
          <Link
            :href="route('goals.create')"
            class="px-4 py-3 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-500"
          >
            + New Goal
          </Link>
        </div>
      </div>
    </template>
    <div>
      <div class="py-10 mx-auto max-w-7xl sm:px-6 lg:px-8"></div>
    </div>
  </AppLayout>
</template>
