<script setup>
import { ref, onMounted } from "vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import Pagination from "@/Components/Pagination.vue";
import Dropdown from "@/Components/Dropdown.vue";

// Props from the server-side
const props = defineProps({
  websiteSpyRequests: Object, // Paginated requests from the server
  project: Object, // Optional project data
  languages: Array, // List of available languages
  locations: Array, // List of available locations
  target: String, // Search query passed from the backend
});

// Initialize form data for Inertia.js
const form = useForm({
  target: props.target || "", // Initialize search query from server
  location_code: "", // For selected location code
});

const selectedRequests = ref([]); // For tracking selected website spy requests

// Define breadcrumbs
const breadcrumbs = [
  { name: "Home", href: "/" },
  { name: "Projects", href: "/projects" },
  {
    name: "Website Spy Requests",
    href: route("website-spy.index"),
    current: true,
  },
];

// Function to handle form submission
const submitSearch = () => {
  form.post(route("website-spy.spy"), {
    // Send data to the backend
    data: {
      target: form.target,
      language_name: form.language_name, // Oppdatert til language_name
      location_code: form.location_code,
    },
    preserveScroll: true, // Preserve scroll position on the page
    onSuccess: () => {
      // Log the successful form submission
      console.log("Form submitted successfully.");
    },
    onError: (errors) => {
      console.error("Error during form submission:", errors);
    },
  });
  console.log("Search form submitted:", form);
};

// Format date function
const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};

// Function to select or deselect all checkboxes
const selectAll = (event) => {
  if (event.target.checked) {
    selectedRequests.value = props.websiteSpyRequests.data.map(
      (request) => request.id
    );
  } else {
    selectedRequests.value = [];
  }
};

// Function to handle bulk actions, such as deleting selected requests
const confirmDelete = () => {
  if (selectedRequests.value.length === 0) {
    alert("No request selected.");
    return;
  }

  if (confirm("Are you sure you want to delete the selected requests?")) {
    form.post(route("website-spy.bulk_delete"), {
      data: { requests: selectedRequests.value },
      onSuccess: () => {
        selectedRequests.value = [];
        router.reload(); // Reload the list
      },
      onError: (errors) => {
        console.log(errors);
      },
    });
  }
};
</script>

<template>
  <Head title="Website Spy Requests" />
  <AppLayout title="Website Spy">
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <!-- Breadcrumbs component showing navigation -->
            <Breadcrumbs :pages="breadcrumbs" />
          </h2>
        </div>
      </div>
    </template>

    <div class="py-10 mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="flex items-center justify-between">
        <!-- Dropdown Component -->
        <div>
          <Dropdown>
            <template #trigger>
              <button
                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-500"
              >
                Actions
              </button>
            </template>

            <template #content>
              <div class="py-2 text-sm text-gray-700 bg-white">
                <div class="mt-2 border-t">
                  <a
                    href="#"
                    class="block px-4 py-2 text-red-600 hover:bg-gray-100"
                    @click="confirmDelete"
                  >
                    Delete Selected Requests
                  </a>
                </div>
              </div>
            </template>
          </Dropdown>
        </div>

        <!-- Search form with selectors for language and location -->
        <div>
          <form @submit.prevent="submitSearch" class="mb-4 px-2">
            <div class="flex space-x-2">
              <!-- Search field -->
              <input
                v-model="form.target"
                type="text"
                placeholder="Search target URL..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md"
              />

              <!-- Location Selector -->
              <select
                v-model="form.location_code"
                class="px-3 py-2 border border-gray-300 rounded-md"
              >
                <option value="">Select Location</option>
                <option
                  v-for="location in props.locations"
                  :key="location.location_code"
                  :value="location.location_code"
                >
                  {{ location.location_name }}
                </option>
              </select>

              <!-- Submit Button -->
              <button
                type="submit"
                class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-500"
              >
                Search
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Website Spy Request list with checkboxes -->
      <div v-if="props.websiteSpyRequests && props.websiteSpyRequests.data">
        <div class="overflow-hidden bg-white shadow sm:rounded-lg">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th
                  scope="col"
                  class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                >
                  <!--Legge til en checkbox som vil markere samtlige i listen i tabellen -->
                  <input type="checkbox" @click="selectAll" />
                </th>

                <th
                  scope="col"
                  class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                >
                  Target URL
                </th>
                <th
                  scope="col"
                  class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                >
                  Language
                </th>
                <th
                  scope="col"
                  class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                >
                  Location
                </th>
                <th
                  scope="col"
                  class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                >
                  Rank
                </th>
                <th scope="col" class="relative px-6 py-3">
                  <span class="sr-only">Actions</span>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="request in props.websiteSpyRequests.data"
                :key="request.uuid"
              >
                <td class="px-6 py-4 text-sm text-gray-900">
                  <input
                    type="checkbox"
                    :value="request.uuid"
                    v-model="selectedRequests"
                  />
                </td>
                <td
                  class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap"
                >
                  {{ request.domain }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                  {{ request.language_name }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                  {{ request.country_iso_code }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                  {{ request.domain_rank }}
                </td>
                <td
                  class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap"
                >
                  <!-- Sørg for at uuid brukes her -->
                  <Link
                    :href="route('website-spy.show', request.uuid)"
                    class="text-indigo-600 hover:text-indigo-900"
                  >
                    View
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <Pagination :links="props.websiteSpyRequests.links" />
      </div>

      <div v-else>
        <p>No data available or failed to load data.</p>
      </div>
    </div>
  </AppLayout>
</template>
