<script setup>
import { ref, computed } from "vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import Pagination from "@/Components/Pagination.vue";
import Dropdown from "@/Components/Dropdown.vue";

// Props from the server-side
const props = defineProps({
  project: Object,
  keywords: Object,
  keywordLists: Array, // List of keyword lists
  search: String, // Search query passed from the backend
});

// Initialize form data for Inertia.js
const form = useForm({ search: props.search || "" }); // Initialize search query from server
const selectedKeywords = ref([]); // For tracking selected keywords
const newListName = ref(""); // New list name for modal
const selectedList = ref(null); // For tracking the selected list UUID

// Define breadcrumbs
const breadcrumbs = [
  { name: "Home", href: "/" },
  { name: "Projects", href: "/projects" },
  {
    name: props.project.project_name,
    href: `/projects/${props.project.project_code}`,
  },
  { name: "Keywords", href: route("keywords.index"), current: true },
];

// Function to handle form submission
const submitSearch = () => {
  // Perform form submission using Inertia router
  router.get(route("keywords.index"), { search: form.search });
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

// Get the list name from `list_uuid`, or return "Unlisted" if `list_uuid` is null
const getListName = (list_uuid) => {
  if (!list_uuid || !props.keywordLists || props.keywordLists.length === 0) {
    return "Unlisted"; // Return "Unlisted" if no list_uuid or keywordLists is undefined/empty
  }
  const list = props.keywordLists.find((list) => list.list_uuid === list_uuid);
  return list ? list.name : "Unlisted";
};

const formAddToList = useForm({ keywords: [], list_uuid: "" });

const addToList = (list_uuid) => {
  // Sjekk om noen søkeord er valgt før du sender forespørselen
  if (selectedKeywords.value.length === 0) {
    alert("No keyword is selected.");
    return; // Avslutt funksjonen hvis ingen søkeord er valgt
  }

  // Oppdater `formAddToList` med de valgte søkeordene og listen
  formAddToList.keywords = selectedKeywords.value;
  formAddToList.list_uuid = list_uuid;

  // Send forespørsel til backend ved å bruke formAddToList.post
  formAddToList.post(
    route("keywords.add_to_list"), // Rutenavn definert i backenden
    {
      preserveScroll: true, // Behold scroll-posisjon etter forespørselen
      onSuccess: () => {
        selectedKeywords.value = []; // Tøm valgte søkeord etter en vellykket forespørsel
        console.log("Keywords added to list."); // Logg en suksessmelding til konsollen
        //alert("Keywords added to list."); // Vis en suksessmelding
      },
      onError: (error) => {
        console.error("Error:", error); // Logg feilmelding til konsollen
        alert("Failed to add keywords to list."); // Vis en feilmelding til brukeren
      },
    }
  );
};

const getSearchVolume = () => {
  if (selectedKeywords.value.length === 0) {
    alert("No keyword selected.");
    return;
  }

  form.post(route("keywords.get_search_volume"), {
    data: { keywords: selectedKeywords.value },
    onSuccess: () => {
      selectedKeywords.value = [];
      alert("Search volume data fetched.");
    },
    onError: (errors) => {
      console.log(errors);
    },
  });
};

// function to select all keywords, including the checkboxes on paginated pages
const selectAll = (event) => {
  if (event.target.checked) {
    selectedKeywords.value = props.keywords.data.map(
      (keyword) => keyword.keyword_uuid
    );
  } else {
    selectedKeywords.value = [];
  }
  // console.log("number of selected keywords: " + selectedKeywords.value.length);
  //console.log("Selected Keywords:", selectedKeywords.value);
};

const confirmDelete = () => {
  if (selectedKeywords.value.length === 0) {
    alert("No keyword selected.");
    return;
  }

  if (confirm("Are you sure you want to delete??")) {
    form.post(route("keywords.bulk_delete"), {
      data: { keywords: selectedKeywords.value },
      onSuccess: () => {
        selectedKeywords.value = [];
        router.reload(); // Laster listen på nytt
      },
      onError: (errors) => {
        console.log(errors);
      },
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
            <!-- Breadcrumbs component showing navigation -->
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
                <span class="block px-4 py-2 font-semibold">Add to List</span>
                <div class="pl-4">
                  <!-- Dynamically render lists fetched from the database -->
                  <a
                    v-for="list in props.keywordLists"
                    :key="list.list_uuid"
                    href="#"
                    class="block px-4 py-2 hover:bg-gray-100"
                    @click.prevent="addToList(list.list_uuid)"
                  >
                    {{ list.name }}
                  </a>
                </div>

                <!-- Legge til nytt menypunkt: Get Search Volume -->
                <span class="block px-4 py-2 font-semibold">Keyword data</span>
                <div class="pl-4">
                  <a
                    href="#"
                    class="block px-4 py-2 hover:bg-gray-100"
                    @click.prevent="getSearchVolume"
                  >
                    Get Search Volume
                  </a>
                </div>

                <!-- Delete Option with Confirmation -->
                <div class="mt-2 border-t">
                  <a
                    href="#"
                    class="block px-4 py-2 text-red-600 hover:bg-gray-100"
                    @click="confirmDelete"
                  >
                    Delete
                  </a>
                </div>
              </div>
            </template>
          </Dropdown>
        </div>

        <!-- Search form -->
        <div>
          <form @submit.prevent="submitSearch" class="mb-4">
            <div class="flex space-x-2">
              <input
                v-model="form.search"
                type="text"
                placeholder="Search keywords..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md"
              />
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

      <!-- Keyword list with checkboxes -->
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
            <tr
              v-for="keyword in props.keywords.data"
              :key="keyword.keyword_uuid"
            >
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

      <!-- Pagination -->
      <Pagination :links="props.keywords.links" />
    </div>
  </AppLayout>
</template>
