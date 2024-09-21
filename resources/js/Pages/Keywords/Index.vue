<script setup>
import { ref, computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import Pagination from "@/Components/Pagination.vue";

// Props from the server-side
const props = defineProps({
  project: Object,
  keywords: Object,
});

// Initialize form data for Inertia.js
const form = useForm({});

// Define breadcrumbs
const breadcrumbs = [{ name: "Keywords", href: "/keywords", current: true }];

// Delete keyword function
const deleteKeyword = (keyword_uuid) => {
  if (confirm("Are you sure you want to delete this keyword?")) {
    form.delete(route("keywords.destroy", keyword_uuid), {
      onError: () => alert("Failed to delete keyword."),
    });
  }
};
</script>

<template>
  <Head title="Keywords" />
  <AppLayout title="Keywords">
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <Breadcrumbs :pages="breadcrumbs" />
          </h2>
        </div>
        <div class="flex space-x-4">
          <Link
            :href="route('keywords.create')"
            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-500"
          >
            + New Keyword
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
                Keyword
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
              >
                Created At
              </th>
              <th scope="col" class="relative px-6 py-3">
                <span class="sr-only">Actions</span>
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="keyword in keywords.data" :key="keyword.keyword_uuid">
              <td
                class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap"
              >
                {{ keyword.name }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                {{ keyword.created_at }}
              </td>
              <td
                class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap"
              >
                <Link
                  :href="route('keywords.show', [keyword.keyword_uuid])"
                  class="text-indigo-600 hover:text-indigo-900"
                >
                  View
                </Link>
                <Link
                  :href="route('keywords.edit', [keyword.keyword_uuid])"
                  class="ml-4 text-indigo-600 hover:text-indigo-900"
                >
                  Edit
                </Link>
                <button
                  @click="deleteKeyword(keyword.keyword_uuid)"
                  class="ml-4 text-red-600 hover:text-red-900"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- Pagination -->
      <Pagination :links="keywords.links" />
    </div>
  </AppLayout>
</template>
