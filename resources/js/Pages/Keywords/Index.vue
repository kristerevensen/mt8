<script setup>
import { ref, computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import Pagination from "@/Components/Pagination.vue";
import Modal from "@/Components/Modal.vue"; // Assuming you have a Modal component

// Props from the server-side
const props = defineProps({
  project: Object,
  keywords: Object,
  keywordLists: Array, // List of keyword lists
});

// Initialize form data for Inertia.js
const form = useForm({});
const searchQuery = ref("");
const selectedKeywords = ref([]); // For tracking selected keywords
const showModal = ref(false); // Modal visibility
const newListName = ref(""); // New list name for modal
const selectedList = ref(null); // Selected existing list

// Define breadcrumbs
const breadcrumbs = [{ name: "Keywords", href: "/keywords", current: true }];

// Computed property to filter keywords based on search query
const filteredKeywords = computed(() => {
  return props.keywords.data.filter((keyword) =>
    keyword.keyword.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

// Format date function
const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};

// Toggle keyword selection
const toggleKeywordSelection = (keyword_uuid) => {
  if (selectedKeywords.value.includes(keyword_uuid)) {
    selectedKeywords.value = selectedKeywords.value.filter(
      (id) => id !== keyword_uuid
    );
  } else {
    selectedKeywords.value.push(keyword_uuid);
  }
};

// Get the list name from `list_uuid`, or return "Unlisted" if `list_uuid` is null
const getListName = (list_uuid) => {
  if (!list_uuid) return "Unlisted";
  const list = props.keywordLists.find((list) => list.list_uuid === list_uuid);
  return list ? list.name : "Unlisted";
};

// Register keywords in the selected or new list
const registerKeywordsInList = () => {
  if (!selectedList.value && newListName.value.trim() === "") {
    alert("Please select a list or enter a new list name.");
    return;
  }

  // Send data to server (use Inertia for form submission)
  form.post(route("keywords.register_in_list"), {
    preserveScroll: true,
    onSuccess: () => {
      selectedKeywords.value = [];
      showModal.value = false;
    },
    data: {
      keywords: selectedKeywords.value,
      list_name: newListName.value, // New list name
      selected_list_uuid: selectedList.value, // Existing list
      project_code: props.project.project_code,
    },
  });
};

// Delete selected keywords
const deleteSelectedKeywords = () => {
  if (confirm("Are you sure you want to delete the selected keywords?")) {
    form.delete(route("keywords.bulk_delete"), {
      data: { keywords: selectedKeywords.value },
      preserveScroll: true,
      onSuccess: () => {
        selectedKeywords.value = [];
        showModal.value = false;
      },
      onError: () => alert("Failed to delete keywords."),
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
      <!-- Search field -->
      <div class="mb-4">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search keywords..."
          class="w-full px-3 py-2 border border-gray-300 rounded-md"
        />
      </div>

      <!-- Keyword list with checkboxes -->
      <div class="overflow-hidden bg-white shadow sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                scope="col"
                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
              >
                Select
              </th>
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
                Registered
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
              >
                List
              </th>
              <th scope="col" class="relative px-6 py-3">
                <span class="sr-only">Actions</span>
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="keyword in filteredKeywords" :key="keyword.keyword_uuid">
              <td class="px-6 py-4 text-sm text-gray-900">
                <input
                  type="checkbox"
                  :value="keyword.keyword_uuid"
                  v-model="selectedKeywords"
                />
              </td>
              <td
                class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap"
              >
                {{ keyword.keyword }}
              </td>
              <!-- "Registered" column with formatted date -->
              <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                {{ formatDate(keyword.created_at) }}
              </td>
              <!-- "List" column showing list name or "Unlisted" -->
              <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                {{ getListName(keyword.list_uuid) }}
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

      <!-- Button to trigger modal for selected keywords -->
      <div class="mt-4">
        <button
          v-if="selectedKeywords.length > 0"
          @click="showModal = true"
          class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-500"
        >
          Manage Selected Keywords
        </button>
      </div>

      <!-- Pagination -->
      <Pagination :links="keywords.links" />
    </div>

    <!-- Modal for managing selected keywords -->
    <Modal v-if="showModal" @close="showModal = false">
      <template #title> Selected Keywords </template>
      <template #body>
        <div class="mb-4">
          <p class="mb-2">
            You have selected {{ selectedKeywords.length }} keywords.
          </p>

          <!-- Delete selected keywords -->
          <button
            @click="deleteSelectedKeywords"
            class="px-4 py-2 mb-4 text-white bg-red-600 rounded-md hover:bg-red-500"
          >
            Delete Selected Keywords
          </button>

          <!-- Dropdown to select an existing list -->
          <div class="mb-4">
            <label
              for="selectedList"
              class="block mb-2 text-sm font-medium text-gray-700"
              >Add to Existing List</label
            >
            <select
              id="selectedList"
              v-model="selectedList"
              class="w-full px-3 py-2 border border-gray-300 rounded-md"
            >
              <option value="">Select a list</option>
              <option
                v-for="list in props.keywordLists"
                :key="list.list_uuid"
                :value="list.list_uuid"
              >
                {{ list.name }}
              </option>
            </select>
          </div>

          <!-- Input for new list -->
          <div>
            <label
              for="newListName"
              class="block text-sm font-medium text-gray-700"
            >
              Or Create a New List
            </label>
            <input
              v-model="newListName"
              type="text"
              id="newListName"
              class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md"
              placeholder="Enter new list name..."
            />
          </div>
        </div>
      </template>
      <template #footer>
        <button
          @click="registerKeywordsInList"
          class="px-4 py-2 text-white bg-indigo-600 rounded-md hover:bg-indigo-500"
        >
          Save
        </button>
        <button
          @click="showModal = false"
          class="px-4 py-2 ml-4 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
        >
          Cancel
        </button>
      </template>
    </Modal>
  </AppLayout>
</template>
