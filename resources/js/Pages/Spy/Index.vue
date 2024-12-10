<script setup>
import { ref, onMounted, computed, watch } from "vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import Pagination from "@/Components/Pagination.vue";
import Dropdown from "@/Components/Dropdown.vue";
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

// Props from the server-side
const props = defineProps({
  websiteSpyRequests: Object, // Paginated requests from the server
  project: Object, // Optional project data
  languages: Array, // List of available languages
  locations: Array, // List of available locations
  target: String, // Search query passed from the backend
});

// For debugging
console.log('Props received:', {
  languages: props.languages,
  locations: props.locations
});

// Initialize form data for Inertia.js
const form = useForm({
  target: props.target || "", // Initialize search query from server
  location_code: "", // For selected location code
  language_code: "", // For selected language
});

const selectedRequests = ref([]); // For tracking selected website spy requests

// Define breadcrumbs
const breadcrumbs = [
  { name: "Home", href: "/" },
  { name: "Projects", href: "/projects" },
  {
    name: "Website Spy",
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
      language_code: form.language_code, 
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

// Function to get status color
const getStatusColor = (status) => {
  switch (status?.toLowerCase()) {
    case 'completed':
      return 'bg-green-100 text-green-800';
    case 'pending':
      return 'bg-yellow-100 text-yellow-800';
    case 'failed':
      return 'bg-red-100 text-red-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};

// Search filters for dropdowns
const locationSearch = ref('');
const languageSearch = ref('');

// Computed properties for filtered dropdown options
const filteredLocations = computed(() => {
    if (!locationSearch.value) return props.locations;
    const search = locationSearch.value.toLowerCase();
    return props.locations.filter(location => 
        location.location_name.toLowerCase().includes(search) || 
        location.location_code.toLowerCase().includes(search)
    );
});

const availableLanguages = computed(() => {
    if (!form.location_code) return [];
    
    const selectedLocation = props.locations.find(
        location => location.location_code === form.location_code
    );
    
    if (!selectedLocation || !selectedLocation.available_languages) return [];
    
    let languages = selectedLocation.available_languages;
    
    if (languageSearch.value) {
        const search = languageSearch.value.toLowerCase();
        languages = languages.filter(lang => 
            lang.language_name.toLowerCase().includes(search) || 
            lang.language_code.toLowerCase().includes(search)
        );
    }
    
    return languages;
});

// Handle search input
const handleLocationSearch = (event) => {
    if (event.key === 'Backspace') {
        locationSearch.value = locationSearch.value.slice(0, -1);
    } else if (event.key.length === 1) {
        locationSearch.value += event.key;
    }
};

const handleLanguageSearch = (event) => {
    if (!form.location_code) return;
    if (event.key === 'Backspace') {
        languageSearch.value = languageSearch.value.slice(0, -1);
    } else if (event.key.length === 1) {
        languageSearch.value += event.key;
    }
};

// Reset search when selections change or dropdowns close
watch(() => form.location_code, () => {
    form.language_code = '';
    locationSearch.value = '';
    languageSearch.value = '';
});

const resetLocationSearch = () => {
    setTimeout(() => {
        locationSearch.value = '';
    }, 300);
};

const resetLanguageSearch = () => {
    setTimeout(() => {
        languageSearch.value = '';
    }, 300);
};

// Function to fetch technologies for a domain
const fetchTechnologies = (uuid) => {
  router.post(route('website-spy.technologies'), { uuid }, {
    preserveScroll: true,
    onSuccess: (response) => {
      console.log('Technologies fetched:', response);
    },
    onError: (errors) => {
      console.error('Error fetching technologies:', errors);
    }
  });
};

// Add button click handler
const handleFetchTechnologies = (uuid) => {
  console.log('Fetching technologies for:', uuid);
  fetchTechnologies(uuid);
};

</script>

<template>
  <Head title="Website Spy" />
  <AppLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          <!-- Breadcrumbs component showing navigation -->
          <Breadcrumbs :pages="breadcrumbs" />
        </h2>
      </div>
    </template>

    <div class="py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">

      <!-- Main Analysis Form -->
      <div class="p-6 mb-6 bg-white rounded-lg shadow-sm">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900">Website Spy</h3>
        </div>

        <form @submit.prevent="submitSearch" class="space-y-4">
          <!-- Grid layout for form fields -->
          <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <!-- URL Input -->
            <div>
              <label for="target" class="block text-sm font-medium text-gray-700">Website URL</label>
              <TextInput
                id="target"
                v-model="form.target"
                type="url"
                class="mt-1 block w-full"
                placeholder="https://example.com"
                required
              />
            </div>

            <!-- Location Dropdown with Search -->
            <div>
              <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
              <div class="relative mt-1">
                <select
                  id="location"
                  v-model="form.location_code"
                  class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                  required
                  @keydown="handleLocationSearch"
                  @blur="resetLocationSearch"
                >
                  <option value="">Select a location</option>
                  <option v-if="locationSearch" value="" disabled class="text-gray-500">
                    Search: {{ locationSearch }}
                  </option>
                  <option
                    v-for="location in filteredLocations"
                    :key="location.location_code"
                    :value="location.location_code"
                  >
                    {{ location.location_name }} ({{ location.location_code }})
                  </option>
                </select>
              </div>
            </div>

            <!-- Language Dropdown with Search -->
            <div>
              <label for="language" class="block text-sm font-medium text-gray-700">Language</label>
              <div class="relative mt-1">
                <select
                  id="language"
                  v-model="form.language_code"
                  class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                  required
                  :disabled="!form.location_code"
                  @keydown="handleLanguageSearch"
                  @blur="resetLanguageSearch"
                >
                  <option value="">Select a language</option>
                  <option v-if="languageSearch" value="" disabled class="text-gray-500">
                    Search: {{ languageSearch }}
                  </option>
                  <option
                    v-for="language in availableLanguages"
                    :key="language.language_code"
                    :value="language.language_code"
                  >
                    {{ language.language_name }} ({{ language.language_code }})
                  </option>
                </select>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end">
            <PrimaryButton
              type="submit"
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing"
            >
              Analyze Website
            </PrimaryButton>
          </div>
        </form>
      </div>

      <!-- Results Table -->
      <div v-if="props.websiteSpyRequests.data.length > 0" class="bg-white rounded-lg shadow-sm">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="w-4 p-4">
                  <input
                    type="checkbox"
                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                    @change="selectAll"
                  />
                </th>
                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                  Target URL
                </th>
                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                  Status
                </th>
                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                  Location
                </th>
                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                  Language
                </th>
                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                  Date
                </th>
                <th scope="col" class="relative px-6 py-3">
                  <span class="sr-only">Actions</span>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="request in props.websiteSpyRequests.data" :key="request.uuid" class="hover:bg-gray-50">
                <td class="w-4 p-4">
                  <input
                    type="checkbox"
                    :value="request.uuid"
                    v-model="selectedRequests"
                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                  />
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ request.url }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full"
                    :class="getStatusColor(request.status)"
                  >
                    {{ request.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ request.location_code || 'N/A' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ request.language_code || 'N/A' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-500">{{ formatDate(request.created_at) }}</div>
                </td>
                <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                  <div class="flex justify-end space-x-2">
                    <Link
                      :href="route('website-spy.show', request.uuid)"
                      class="text-indigo-600 hover:text-indigo-900"
                    >
                      View Results
                    </Link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="px-4 py-3 bg-gray-50 sm:px-6">
          <Pagination :links="props.websiteSpyRequests.links" />
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="p-12 text-center bg-white rounded-lg shadow-sm">
        <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No analysis results</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by analyzing a website above.</p>
      </div>
    </div>
  </AppLayout>
</template>
