<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps, computed } from "vue";
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
import Campaigns from "@/Pages/Campaigns/components/Campaigns.vue"; // Import Campaigns component

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
  { name: "All Campaigns", href: "/campaigns", current: false },
];

// Define props to accept campaigns and clicks from the parent component
const props = defineProps({
  campaigns: Array,
  clicks: Array,
});

// Prepare data for total clicks line graph
const totalClicksData = computed(() => {
  if (!props.clicks || props.clicks.length === 0) {
    return {
      labels: [],
      datasets: [],
    };
  }

  const dates = props.clicks.map((click) => click.date);
  const counts = props.clicks.map((click) => click.count);
  return {
    labels: dates,
    datasets: [
      {
        label: "Total Clicks",
        data: counts,
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

// Prepare data for clicks per campaign bar graph
const clicksPerCampaign = computed(() => {
  if (!props.campaigns || props.campaigns.length === 0) {
    return [];
  }

  return props.campaigns
    .map((campaign) => ({
      name: campaign.campaign_name,
      clicks: campaign.clicks_count || 0,
    }))
    .sort((a, b) => b.clicks - a.clicks);
});

const campaignClicksData = computed(() => {
  if (clicksPerCampaign.value.length === 0) {
    return {
      labels: [],
      datasets: [],
    };
  }

  return {
    labels: clicksPerCampaign.value.map((campaign) => campaign.name),
    datasets: [
      {
        label: "Clicks per Campaign",
        data: clicksPerCampaign.value.map((campaign) => campaign.clicks),
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

// Function to handle campaign deletion
const deleteCampaign = (id) => {
  if (confirm("Are you sure you want to delete this campaign?")) {
    Inertia.delete(route("campaigns.destroy", id));
  }
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
        <div>
          <Link
            :href="route('campaigns.create')"
            class="block px-3 py-2 text-sm font-semibold text-center text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
          >
            Add Campaign
          </Link>
        </div>
      </div>
    </template>
    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
          <!-- Total Clicks Line Graph -->
          <div class="mb-8">
            <Line :data="totalClicksData" :options="totalClicksOptions" />
          </div>
          <!-- Clicks per Campaign Bar Graph -->
          <div class="mb-8">
            <Bar :data="campaignClicksData" :options="campaignClicksOptions" />
          </div>
        </div>

        <!-- Campaigns Table -->
        <div class="flow-root mt-8">
          <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div
              class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8"
            >
              <div
                class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg"
              >
                <Campaigns :campaigns="props.campaigns" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
