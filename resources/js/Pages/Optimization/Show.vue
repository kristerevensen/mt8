<script setup>
import { ref, computed } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";

// Define the props received from the server-side
const props = defineProps({
  category: Object, // Single category object
  criteria: Array, // Array of criteria associated with this category
});

// Breadcrumb navigation
const breadcrumbs = [
  { name: "Growth", href: "/growth", current: false },
  { name: "Optimization Criteria", href: "/optimization", current: false },
  { name: props.category.category_name, current: true },
];
</script>

<template>
  <Head title="Category Details" />
  <AppLayout title="Category Details">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        <Breadcrumbs :pages="breadcrumbs" />
      </h2>
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
                Criteria Name
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
              >
                Description
              </th>
              <th scope="col" class="relative px-6 py-3">
                <span class="sr-only">Actions</span>
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="criterion in props.criteria" :key="criterion.criteria_uuid">
              <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                {{ criterion.criteria_name }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                {{ criterion.criteria_description }}
              </td>
              <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                <Link
                  :href="`/optimization/criteria/${criterion.criteria_uuid}/edit`"
                  class="text-indigo-600 hover:text-indigo-900"
                >
                  Edit
                </Link>
                <Link
                  :href="`/optimization/criteria/${criterion.criteria_uuid}`"
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
