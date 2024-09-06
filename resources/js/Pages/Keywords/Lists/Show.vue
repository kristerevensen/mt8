<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";


// Define breadcrumbs
const breadcrumbs = [
  { name: "Growth", href: "/growth", current: false },
  { name: "Keyword Lists", current: true },
];
</script>

<template>
  <Head title="Keyword Lists" />
  <AppLayout title="Keyword Lists">
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <Breadcrumbs :pages="breadcrumbs" />
          </h2>
        </div>
        <div class="flex space-x-4">
          <Link
            :href="route('keyword-lists.create')"
            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-500"
          >
            + New Keyword List
          </Link>
        </div>
      </div>
    </template>

    <div class="py-10 mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white shadow sm:rounded-lg">

      </div>
    </div>
  </AppLayout>
</template>
