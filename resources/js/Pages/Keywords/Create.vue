<script setup>
import { ref } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";

// Props from the server-side (project and keyword lists passed as props)
const props = defineProps({
  project: Object, // The selected project details
  keywordLists: Array, // List of available keyword lists
});

// Initialize form data using Inertia's useForm
const form = useForm({
  keywords: "", // Bulk keywords input (one per line)
  list_uuid: "", // The selected keyword list
});

// Breadcrumbs for navigation
const breadcrumbs = [
  { name: "Keywords", href: "/keywords", current: false },
  { name: "Create Keywords", current: true },
];

// Submit form function
const submit = () => {
  form.post(route("keywords.store"), {
    onSuccess: () => {
      form.reset(); // Reset the form after successful submission
    },
    onError: (errors) => {
      console.error(errors); // Log any validation errors
    },
  });
};
</script>

<template>
  <Head title="Create Keywords" />
  <AppLayout title="Create Keywords">
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <Breadcrumbs :pages="breadcrumbs" />
          </h2>
        </div>
      </div>
    </template>

    <div class="py-10 mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white shadow sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
          <!-- Form for creating multiple keywords -->
          <form @submit.prevent="submit">
            <!-- Bulk Keywords input field -->
            <div>
              <label
                for="keywords"
                class="block text-sm font-medium text-gray-700"
                >Keywords (one per line)</label
              >
              <textarea
                v-model="form.keywords"
                id="keywords"
                name="keywords"
                rows="10"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm sm:text-sm"
                placeholder="Enter one keyword per line"
              ></textarea>
              <div
                v-if="form.errors.keywords"
                class="mt-2 text-sm text-red-600"
              >
                {{ form.errors.keywords }}
              </div>
            </div>

            <!-- Keyword List Dropdown -->
            <div class="mt-4">
              <label
                for="list_uuid"
                class="block text-sm font-medium text-gray-700"
                >Keyword List</label
              >
              <select
                v-model="form.list_uuid"
                id="list_uuid"
                name="list_uuid"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm sm:text-sm"
              >
                <option value="" disabled>Select a list</option>
                <option
                  v-for="list in keywordLists"
                  :key="list.list_uuid"
                  :value="list.list_uuid"
                >
                  {{ list.name }}
                </option>
              </select>
              <div
                v-if="form.errors.list_uuid"
                class="mt-2 text-sm text-red-600"
              >
                {{ form.errors.list_uuid }}
              </div>
            </div>

            <!-- Submit button -->
            <div class="mt-6">
              <button
                type="submit"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-500"
              >
                Create Keywords
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
