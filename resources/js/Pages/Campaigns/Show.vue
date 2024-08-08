<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps, ref, computed } from "vue";
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
const filteredLinks = computed(() => {
  return props.links.data.filter((link) => {
    return link.landing_page
      .toLowerCase()
      .includes(searchQuery.value.toLowerCase());
  });
});

const clickData = {
  labels: props.clicks.map((click) => click.date),
  datasets: [
    {
      label: "Link Clicks",
      data: props.clicks.map((click) => click.count),
      borderColor: "#4f46e5", // Indigo
      backgroundColor: "rgba(79, 70, 229, 0.1)",
      fill: true,
      tension: 0.1,
    },
  ],
};

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
    if (counts[link.source]) {
      counts[link.source]++;
    } else {
      counts[link.source] = 1;
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
    this.$inertia.delete(`/campaign-links/${id}`, {
      onSuccess: () => {
        alert("Link deleted successfully.");
      },
    });
  }
};
</script>

<template>
  <Head title="Campaign Details" />
  <AppLayout title="Campaign Details">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          <Breadcrumbs :pages="breadcrumbs" />
        </h2>
        <Link
          :href="`/campaign-links/create?campaign_token=${campaign.campaign_token}`"
          class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-500"
        >
          Add Campaign Link
        </Link>
      </div>
    </template>
    <div>
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
        <div class="mb-4">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search Links"
            class="w-full p-2 border rounded"
          />
        </div>
        <div class="flow-root mt-8">
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
                        scope="col"
                        class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6"
                      >
                        Landing Page
                      </th>
                      <th
                        scope="col"
                        class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                      >
                        Tagged URL
                      </th>
                      <th
                        scope="col"
                        class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                      >
                        Source
                      </th>
                      <th
                        scope="col"
                        class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                      >
                        Medium
                      </th>
                      <th
                        scope="col"
                        class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                      >
                        Clicks
                      </th>
                      <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                        <span class="sr-only">Actions</span>
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="link in filteredLinks" :key="link.id">
                      <td
                        class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 whitespace-nowrap sm:pl-6"
                      >
                        {{ link.landing_page }}
                      </td>
                      <td
                        class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap"
                      >
                        {{ link.tagged_url }}
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
                          :href="`/campaign-links/${link.link_token}/edit`"
                          class="text-indigo-600 hover:text-indigo-900"
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

<script>
export default {
  methods: {
    deleteLink(id) {
      if (confirm("Are you sure you want to delete this link?")) {
        this.$inertia.delete(`/campaign-links/${id}`, {
          onSuccess: () => {
            alert("Link deleted successfully.");
          },
        });
      }
    },
  },
};
</script>
