<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import Pagination from "@/Components/Pagination.vue";

// Props from the server-side
const props = defineProps({
  project: Object,
  keywordLists: Object,
});

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
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                scope="col"
                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
              >
                Name
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
            <tr v-for="list in keywordLists.data" :key="list.list_uuid">
              <td
                class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap"
              >
                {{ list.name }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                {{ list.description || "No description provided" }}
              </td>
              <td
                class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap"
              >
                <Link
                  :href="route('keyword-lists.show', [list.list_uuid])"
                  class="text-indigo-600 hover:text-indigo-900"
                >
                  View
                </Link>
                <Link
                  :href="route('keyword-lists.edit', [list.list_uuid])"
                  class="ml-4 text-indigo-600 hover:text-indigo-900"
                >
                  Edit
                </Link>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <Pagination :links="keywordLists.links" />
      </div>
    </div>
  </AppLayout>
</template>
