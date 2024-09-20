<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, computed, onMounted } from "vue";
import { Line } from "vue-chartjs";
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  CategoryScale,
  LinearScale,
  PointElement,
} from "chart.js";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import Pagination from "@/Components/Pagination.vue";
import { useForm } from "@inertiajs/vue3";
import { usePage } from "@inertiajs/vue3";

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  CategoryScale,
  LinearScale,
  PointElement
);

const breadcrumbs = [
  { name: "Google Search Console", href: "/gsc/index", current: true },
];

const props = defineProps({
  searchConsoleData: Object, // Data objekt for paginering
  clicks: Array,
  impressions: Array,
});

// Access the URL query parameters (if any)
const { url } = usePage();
const urlParams = new URLSearchParams(url.split("?")[1]);

const searchQuery = ref(urlParams.get("search") || "");
const startDate = ref(urlParams.get("start") || "");
const endDate = ref(urlParams.get("end") || "");

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
  if (!startDate.value || !endDate.value) {
    const today = new Date();
    const end = new Date(today.getFullYear(), today.getMonth(), today.getDate());
    const start = new Date(end);
    start.setDate(start.getDate() - 28);

    const formatDate = (date) => date.toISOString().split("T")[0];

    startDate.value = formatDate(start);
    endDate.value = formatDate(end);
  }
};

// Set default dates if they are not set in URL params
onMounted(() => {
  calculateDefaultDates();
});

const filteredUrls = computed(() => {
  if (props.searchConsoleData && props.searchConsoleData.data) {
    return props.searchConsoleData.data.filter((data) => {
      return data.url.toLowerCase().includes(searchQuery.value.toLowerCase());
    });
  }
  return [];
});

const totalClicksData = computed(() => {
  const dateRange = generateDateRange(startDate.value, endDate.value);
  const clicksByDate = {};

  props.clicks.forEach((click) => {
    clicksByDate[click.date] = click.count;
  });

  const data = dateRange.map((date) => clicksByDate[date] || 0);

  return {
    labels: dateRange,
    datasets: [
      {
        label: "Total Clicks",
        data: data,
        borderColor: "#4f46e5", // Indigo
        backgroundColor: "rgba(79, 70, 229, 0.1)",
        fill: true,
        tension: 0.1,
      },
    ],
  };
});

const totalImpressionsData = computed(() => {
  const dateRange = generateDateRange(startDate.value, endDate.value);
  const impressionsByDate = {};

  props.impressions.forEach((impression) => {
    impressionsByDate[impression.date] = impression.count;
  });

  const data = dateRange.map((date) => impressionsByDate[date] || 0);

  return {
    labels: dateRange,
    datasets: [
      {
        label: "Total Impressions",
        data: data,
        borderColor: "#10b981", // Green
        backgroundColor: "rgba(16, 185, 129, 0.1)",
        fill: true,
        tension: 0.1,
      },
    ],
  };
});

const totalClicksOptions = {
  responsive: true,
  plugins: {
    legend: {
      position: "top",
    },
    title: {
      display: true,
      text: "Total Clicks for All URLs",
    },
  },
};

const totalImpressionsOptions = {
  responsive: true,
  plugins: {
    legend: {
      position: "top",
    },
    title: {
      display: true,
      text: "Total Impressions for All URLs",
    },
  },
};

// Function to handle data filtering
const fetchFilteredData = () => {
  form.get(route("gsc.index"), {
    params: {
      start: startDate.value,
      end: endDate.value,
      search: searchQuery.value,
    },
  });
};
</script>

<template>
  <Head title="Google Search Console" />
  <AppLayout title="Google Search Console">
    <template #header>
      <div class="flex justify-between">
        <div>
          <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <Breadcrumbs :pages="breadcrumbs" />
          </h2>
        </div>
        <div class="flex space-x-4">
          <!-- Date Selectors -->
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
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-4 mb-4 lg:grid-cols-2">
          <!-- Total Clicks Line Graph -->
          <div class="">
            <Line :data="totalClicksData" :options="totalClicksOptions" />
          </div>
          <!-- Total Impressions Line Graph -->
          <div class="">
            <Line :data="totalImpressionsData" :options="totalImpressionsOptions" />
          </div>
        </div>
        <div class="flex justify-between mt-2">
          <div>
            <input
              type="text"
              v-model="searchQuery"
              placeholder="Search URLs"
              class="border-gray-300 rounded-md"
            />
          </div>
        </div>
        <!-- URLs Table -->
        <div class="flow-root">
          <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
              <div class="ring-black ring-opacity-5 sm:rounded-lg">
                <!-- Replace with your table component to display URLs -->
                <div v-for="data in filteredUrls" :key="data.url" class="p-4 bg-white border-b">
                  <h3>{{ data.url }}</h3>
                  <p>Clicks: {{ data.clicks_count }}</p>
                  <p>Impressions: {{ data.impressions_count }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Pagination Component -->
        <Pagination :links="props.searchConsoleData.links" />
      </div>
    </div>
  </AppLayout>
</template>
