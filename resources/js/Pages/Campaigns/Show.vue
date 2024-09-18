<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps, ref, computed, onMounted } from "vue";
import { Line, Bar } from "vue-chartjs";
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  CategoryScale,
  LinearScale,
  PointElement,
  BarElement,
} from "chart.js";
import Pagination from "@/Components/Pagination.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import { useForm } from "@inertiajs/vue3";

const form = useForm({});

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  CategoryScale,
  LinearScale,
  PointElement,
  BarElement
);

const props = defineProps({
  campaign: Object,
  links: Object,
  clicks: Array,
});

const searchQuery = ref("");
const startDate = ref(""); // Start date
const endDate = ref(""); // End date

// Sorting state
const sortColumn = ref("landing_page");
const sortDirection = ref("asc");

// Function to toggle sorting
const toggleSort = (column) => {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
  } else {
    sortColumn.value = column;
    sortDirection.value = "asc";
  }
};

// Function to generate an array of dates between start and end date
const generateDateRange = (start, end) => {
  const dateArray = [];
  let currentDate = new Date(start);

  while (currentDate <= new Date(end)) {
    dateArray.push(currentDate.toISOString().split("T")[0]);
    currentDate.setDate(currentDate.getDate() + 1);
  }

  return dateArray;
};

// Calculate the last whole 28 days for default dates
const calculateDefaultDates = () => {
  const today = new Date();
  const end = new Date(today.getFullYear(), today.getMonth(), today.getDate()); // End date is today
  const start = new Date(end);
  start.setDate(start.getDate() - 28); // Start date is 28 days ago

  // Format dates as YYYY-MM-DD
  const formatDate = (date) => date.toISOString().split("T")[0];

  startDate.value = formatDate(start);
  endDate.value = formatDate(end);
};

// Set default dates only on component mount
onMounted(() => {
  if (!startDate.value || !endDate.value) {
    calculateDefaultDates();
  }
});

const filteredLinks = computed(() => {
  if (!props.links || !props.links.data) return [];

  const filtered = props.links.data.filter((link) => {
    return link.landing_page
      .toLowerCase()
      .includes(searchQuery.value.toLowerCase());
  });

  return filtered.sort((a, b) => {
    const valueA = a[sortColumn.value] || "";
    const valueB = b[sortColumn.value] || "";

    if (sortDirection.value === "asc") {
      return valueA > valueB ? 1 : valueA < valueB ? -1 : 0;
    } else {
      return valueA < valueB ? 1 : valueA > valueB ? -1 : 0;
    }
  });
});

const clickData = computed(() => {
  const dateRange = generateDateRange(startDate.value, endDate.value);
  const clicksByDate = {};

  // Populate clicksByDate with actual data
  props.clicks.forEach((click) => {
    clicksByDate[click.date] = click.count;
  });

  // Populate data array, filling in 0 for missing dates
  const data = dateRange.map((date) => clicksByDate[date] || 0);

  return {
    labels: dateRange,
    datasets: [
      {
        label: "Link Clicks",
        data: data,
        borderColor: "#4f46e5", // Indigo
        backgroundColor: "rgba(79, 70, 229, 0.1)",
        fill: true,
        tension: 0.1,
      },
    ],
  };
});

const clickOptions = {
  responsive: true,
  plugins: {
    legend: {
      position: "top",
    },
    title: {
      display: true,
      text: `Link Clicks for ${props.campaign.campaign_name}`,
    },
  },
};

// Calculate source data for bar chart
const sourceCounts = computed(() => {
  const counts = {};
  props.links.data.forEach((link) => {
    const source = link.source;
    if (counts[source]) {
      counts[source] += link.clicks_count;
    } else {
      counts[source] = link.clicks_count;
    }
  });
  return counts;
});

const sourceData = {
  labels: Object.keys(sourceCounts.value),
  datasets: [
    {
      label: "Source Counts",
      data: Object.values(sourceCounts.value),
      backgroundColor: "rgba(54, 162, 235, 0.5)",
      borderColor: "rgba(54, 162, 235, 1)",
      borderWidth: 1,
    },
  ],
};

const sourceOptions = {
  responsive: true,
  plugins: {
    legend: {
      position: "top",
    },
    title: {
      display: true,
      text: "Source Distribution",
    },
  },
};

const breadcrumbs = [
  { name: "All Campaigns", href: "/campaigns", current: false },
  { name: props.campaign.campaign_name, current: true },
];

// Function to handle link deletion
const deleteLink = (id) => {
  if (confirm("Are you sure you want to delete this link?")) {
    form.delete(route("campaign-links.destroy", id), {
      onSuccess: () => {
        alert("Link deleted successfully.");
      },
      onError: (error) => {
        console.error("Error deleting link:", error);
      },
    });
  }
};

// Function to fetch filtered data based on selected dates
const fetchFilteredData = () => {
  form.get(route("campaigns.show", props.campaign.campaign_token), {
    params: {
      start: startDate.value,
      end: endDate.value,
    },
    onSuccess: () => {
      alert("Data updated successfully.");
    },
    onError: (error) => {
      console.error("Error fetching data:", error);
    },
  });
};

const truncateUrl = (url, maxLength = 50) => {
  if (url.length > maxLength) {
    return url.substring(0, maxLength) + "...";
  }
  return url;
};
// New ref for copy message
const copyMessage = ref("");

// Function to copy URL to clipboard and show message
const copyToClipboard = (url) => {
  const textArea = document.createElement("textarea");
  textArea.value = url;
  document.body.appendChild(textArea);
  textArea.select();

  try {
    document.execCommand("copy");
    copyMessage.value = "Link copied to clipboard!";
  } catch (err) {
    copyMessage.value = "Failed to copy link. Please try again.";
    console.error("Failed to copy: ", err);
  }

  document.body.removeChild(textArea);

  setTimeout(() => {
    copyMessage.value = "";
  }, 3000); // Message disappears after 3 seconds
};
</script>
<template>
  <Head title="Campaign Details" />
  <AppLayout title="Campaign Details">
    <template #header>
      <div class="flex justify-between">
        <div>
          <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <Breadcrumbs :pages="breadcrumbs" />
          </h2>
        </div>
        <div>
          <!-- Date Selectors -->
          <div class="flex justify-end space-x-4">
            <input
              type="date"
              v-model="startDate"
              class="border-gray-300 rounded-md"
              placeholder="Start Date"
            />
            <input
              type="date"
              v-model="endDate"
              class="border-gray-300 rounded-md"
              placeholder="End Date"
            />
            <button
              @click="fetchFilteredData"
              class="px-4 py-2 text-white bg-blue-500 rounded-md"
            >
              Apply
            </button>
          </div>
        </div>
      </div>
    </template>
    <div class="">
      <div class="py-5 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row lg:space-x-4">
          <!-- Link Clicks Graph -->
          <div class="w-full mb-8 lg:w-1/2">
            <Line :data="clickData" :options="clickOptions" />
          </div>
          <!-- Source Distribution Graph -->
          <div class="w-full mb-8 lg:w-1/2">
            <Bar :data="sourceData" :options="sourceOptions" />
          </div>
        </div>

        <!-- Search and Table for Links -->
        <div class="flex justify-between">
          <div>
            <div class="">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search Links"
                class="w-full p-2 border-gray-300 rounded-md"
              />
            </div>
          </div>
          <div>
            <Link
              :href="`/campaign-links/create?campaign_token=${campaign.campaign_token}`"
              class="px-4 py-3 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-500"
            >
              + Link
            </Link>
          </div>
        </div>

        <div class="flow-root mt-8">
          <div v-if="copyMessage" class="mb-4 font-medium text-green-600">
            {{ copyMessage }}
          </div>
          <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div
              class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8"
            >
              <div
                class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg"
              >
                <table class="min-w-full divide-y divide-gray-300">
                  <thead class="bg-gray-50">
                    <tr>
                      <th
                        @click="toggleSort('date')"
                        scope="col"
                        class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 cursor-pointer sm:pl-6"
                      >
                        Date
                      </th>
                      <th
                        @click="toggleSort('landing_page')"
                        scope="col"
                        class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 cursor-pointer sm:pl-6"
                      >
                        Landing Page
                        <span v-if="sortColumn === 'landing_page'">
                          {{ sortDirection === "asc" ? "↑" : "↓" }}
                        </span>
                      </th>
                      <th
                        scope="col"
                        class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                      >
                        Tagged URL
                      </th>
                      <th
                        @click="toggleSort('source')"
                        scope="col"
                        class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 cursor-pointer"
                      >
                        Source
                        <span v-if="sortColumn === 'source'">
                          {{ sortDirection === "asc" ? "↑" : "↓" }}
                        </span>
                      </th>
                      <th
                        @click="toggleSort('medium')"
                        scope="col"
                        class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 cursor-pointer"
                      >
                        Medium
                        <span v-if="sortColumn === 'medium'">
                          {{ sortDirection === "asc" ? "↑" : "↓" }}
                        </span>
                      </th>
                      <th
                        @click="toggleSort('clicks_count')"
                        scope="col"
                        class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 cursor-pointer"
                      >
                        Clicks
                        <span v-if="sortColumn === 'clicks_count'">
                          {{ sortDirection === "asc" ? "↑" : "↓" }}
                        </span>
                      </th>
                      <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                        <span class="sr-only">Actions</span>
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="link in filteredLinks" :key="link.id">
                      <!-- showing date in format: 24th may 24, togglesort -->
                      <td
                        @click="toggleSort('date')"
                        class="px-3 py-4 text-sm font-medium text-gray-900 cursor-pointer whitespace-nowrap sm:pl-6"
                      >
                        {{
                          new Date(link.date).toLocaleDateString("en-GB", {
                            day: "numeric",
                            month: "short",
                            year: "numeric",
                          })
                        }}
                      </td>
                      <td
                        class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 whitespace-nowrap sm:pl-6"
                      >
                        {{ truncateUrl(link.landing_page) }}
                      </td>
                      <td
                        class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap"
                      >
                        <a href="#" @click="copyToClipboard(link.tagged_url)"
                          >{{ truncateUrl(link.tagged_url) }}
                        </a>
                      </td>
                      <td
                        class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap"
                      >
                        {{ link.source }}
                      </td>
                      <td
                        class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap"
                      >
                        {{ link.medium }}
                      </td>
                      <td
                        class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap"
                      >
                        {{ link.clicks_count }}
                      </td>
                      <td
                        class="relative py-4 pl-3 pr-4 text-sm font-medium text-right whitespace-nowrap sm:pr-6"
                      >
                        <Link
                          :href="`/campaign-links/${link.link_token}/copy`"
                          class="ml-2 text-indigo-600 hover:text-indigo-900"
                        >
                          Copy
                        </Link>
                        <Link
                          :href="`/campaign-links/${link.link_token}/edit`"
                          class="ml-2 text-indigo-600 hover:text-indigo-900"
                        >
                          Edit
                        </Link>
                        <button
                          @click="deleteLink(link.id)"
                          class="ml-2 text-red-600 hover:text-red-900"
                        >
                          Delete
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <Pagination :links="links.links" />
      </div>
    </div>
  </AppLayout>
</template>
