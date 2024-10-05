<script setup>
import { ref, computed } from "vue";
import { Link, Head } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";

// Define the props received from the server-side
const props = defineProps({
  categories: Array, // Array of optimization categories
});

// Breadcrumb navigation
const breadcrumbs = [
  { name: "Growth", href: "/growth", current: false },
  { name: "Optimization Criteria", current: true },
];
</script>

<template>
  <Head title="Optimization Criteria" />
  <AppLayout title="Optimization Criteria">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          <Breadcrumbs :pages="breadcrumbs" />
        </h2>
        <Link
          href="/optimization/create"
          class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-500"
        >
          + New Category
        </Link>
      </div>
    </template>

    <div class="py-10 mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white rounded-lg shadow-md">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                scope="col"
                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
              >
                Category Name
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
              >
                Project Code
              </th>
              <th scope="col" class="relative px-6 py-3">
                <span class="sr-only">Actions</span>
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="category in props.categories" :key="category.category_uuid">
              <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                {{ category.category_name }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                {{ category.project_code }}
              </td>
              <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                <Link
                  :href="`/optimization/${category.category_uuid}/edit`"
                  class="text-indigo-600 hover:text-indigo-900"
                >
                  Edit
                </Link>
                <Link
                  :href="`/optimization/${category.category_uuid}`"
                  class="ml-4 text-indigo-600 hover:text-indigo-900"
                >
                  View
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
