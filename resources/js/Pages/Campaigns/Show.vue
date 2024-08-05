<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps, ref, computed, watch } from "vue";
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
import Pagination from "@/Components/Pagination.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  CategoryScale,
  LinearScale,
  PointElement
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

const breadcrumbs = [
  { name: "All Campaigns", href: "/campaigns", current: false },
  { name: props.campaign.campaign_name, current: true },
];
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
        <!-- Link Clicks Graph -->
        <div class="mb-8">
          <Line :data="clickData" :options="clickOptions" />
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
        <div class="overflow-x-auto">
          <table class="min-w-full bg-white border">
            <thead class="bg-gray-200">
              <tr>
                <th class="px-4 py-2 border-b">Landing Page</th>
                <th class="px-4 py-2 border-b">Tagged URL</th>
                <th class="px-4 py-2 border-b">Source</th>
                <th class="px-4 py-2 border-b">Medium</th>
                <th class="px-4 py-2 border-b">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="link in filteredLinks" :key="link.id" class="border-b">
                <td class="px-4 py-2">{{ link.landing_page }}</td>
                <td class="px-4 py-2">{{ link.tagged_url }}</td>
                <td class="px-4 py-2">{{ link.source }}</td>
                <td class="px-4 py-2">{{ link.medium }}</td>
                <td class="px-4 py-2">
                  <Link
                    :href="`/campaign-links/${link.link_token}/edit`"
                    class="text-indigo-600 hover:text-indigo-900"
                    >Edit</Link
                  >
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
