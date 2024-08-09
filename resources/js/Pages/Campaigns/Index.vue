<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, computed, onMounted } from "vue";
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
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import Campaigns from "@/Pages/Campaigns/components/Campaigns.vue";
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

const breadcrumbs = [
  { name: "All Campaigns", href: "/campaigns", current: true },
];

const props = defineProps({
  campaigns: Array,
  clicks: Array,
});

const searchQuery = ref("");
const startDate = ref(""); // Start date
const endDate = ref(""); // End date

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

const filteredCampaigns = computed(() => {
  if (props.campaigns) {
    return props.campaigns.filter((campaign) => {
      return campaign.campaign_name
        .toLowerCase()
        .includes(searchQuery.value.toLowerCase());
    });
  }
  return [];
});

const totalClicksData = computed(() => {
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

const totalClicksOptions = {
  responsive: true,
  plugins: {
    legend: {
      position: "top",
    },
    title: {
      display: true,
      text: "Total Clicks for All Campaigns",
    },
  },
};

const campaignClicksData = computed(() => {
  const campaigns = filteredCampaigns.value;
  const campaignNames = campaigns.map((campaign) => campaign.campaign_name);
  const campaignClicks = campaigns.map(
    (campaign) => campaign.clicks_count || 0
  );

  return {
    labels: campaignNames,
    datasets: [
      {
        label: "Clicks per Campaign",
        data: campaignClicks,
        backgroundColor: "rgba(54, 162, 235, 0.5)",
        borderColor: "rgba(54, 162, 235, 1)",
        borderWidth: 1,
      },
    ],
  };
});

const campaignClicksOptions = {
  responsive: true,
  plugins: {
    legend: {
      position: "top",
    },
    title: {
      display: true,
      text: "Clicks per Campaign",
    },
  },
};

// Function to handle data filtering
const fetchFilteredData = () => {
  form.get(route("campaigns.index"), {
    params: {
      start: startDate.value,
      end: endDate.value,
      search: searchQuery.value,
    },
    onSuccess: () => {
      alert("Data updated successfully.");
    },
    onError: (error) => {
      console.error("Error fetching data:", error);
    },
  });
};
</script>

<template>
  <Head title="Campaigns" />
  <AppLayout title="Campaigns">
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
          <!-- Clicks per Campaign Bar Graph -->
          <div class="">
            <Bar :data="campaignClicksData" :options="campaignClicksOptions" />
          </div>
        </div>
        <div class="flex justify-between mt-2">
          <div>
            <input
              type="text"
              v-model="searchQuery"
              placeholder="Search Campaigns"
              class="border-gray-300 rounded-md"
            />
          </div>
          <div>
            <Link
              :href="route('campaigns.create')"
              class="block px-3 py-2 text-sm font-semibold text-center text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
            >
              Add Campaign
            </Link>
          </div>
        </div>
        <!-- Campaigns Table -->
        <div class="flow-root">
          <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div
              class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8"
            >
              <div class="ring-black ring-opacity-5 sm:rounded-lg">
                <Campaigns :campaigns="filteredCampaigns" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
