<script setup>
import { ref, computed, onMounted } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import Pagination from "@/Components/Pagination.vue";
import Chart from "chart.js/auto";

// Props from the server-side
const props = defineProps({
  project: Object,
  keywordList: Object,
  keywords: Object, // Use the paginated keywords data directly
  searchVolumes: Object, // Original search volume data for the graph
});

// Log data to ensure data integrity
console.log("Paginated Keywords:", props.keywords);
console.log("Search Volumes data:", props.searchVolumes);

// Month names for display and conversion
const monthNames = [
  "Jan",
  "Feb",
  "Mar",
  "Apr",
  "May",
  "Jun",
  "Jul",
  "Aug",
  "Sep",
  "Oct",
  "Nov",
  "Dec",
];

// Helper function to get the month index from the month name
const monthIndex = (monthName) => {
  return monthNames.indexOf(monthName) + 1; // Returns 1-12 based on monthName
};

// Breadcrumbs for navigation
const breadcrumbs = computed(() => [
  { name: "Projects", href: "/projects" },
  {
    name: props.project ? props.project.project_name : "Project",
    href: props.project ? `/projects/${props.project.project_code}` : "#",
  },
  {
    name: "Keyword Lists",
    href: `/keyword-lists`,
  },
  {
    name: props.keywordList ? props.keywordList.name : "Keyword List",
    href: props.keywordList
      ? `/keyword-lists/${props.keywordList.list_uuid}`
      : "#",
    current: true,
  },
]);

// Function to render the chart
const chartRef = ref(null);
const renderChart = () => {
  if (chartRef.value) {
    const ctx = chartRef.value.getContext("2d");
    new Chart(ctx, {
      type: "bar",
      data: {
        labels: monthNames,
        datasets: [
          {
            label: "Total Monthly Search Volume",
            data: Object.values(props.searchVolumes || []),
            backgroundColor: "rgba(75, 192, 192, 0.2)",
            borderColor: "rgba(75, 192, 192, 1)",
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
          },
        },
      },
    });
  }
};

// Render the chart on mount
onMounted(() => {
  renderChart();
});
</script>

<template>
  <Head title="Keyword List" />
  <AppLayout title="Keyword List">
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <Breadcrumbs :pages="breadcrumbs" />
          </h2>
        </div>
      </div>
    </template>

    <div class="py-10 mx-auto max-w-7xl sm:px-6 lg:px-8">
      <!-- Chart Section -->
      <div class="p-6 mb-8 bg-white rounded-lg shadow-md">
        <h3 class="mb-4 text-lg font-medium text-gray-900">
          Monthly Search Volume - {{ keywordList.name }}
        </h3>
        <canvas ref="chartRef" height="150"></canvas>
      </div>

      <!-- Keyword Table Section -->
      <div class="p-6 bg-white rounded-lg shadow-md">
        <h3 class="mb-4 text-lg font-medium text-gray-900">
          Keywords in {{ keywordList.name }}
        </h3>
        <div class="overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th
                  class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                >
                  Keyword
                </th>
                <th
                  class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                >
                  Total
                </th>
                <th
                  v-for="month in monthNames"
                  :key="month"
                  class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                >
                  {{ month }}
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <!-- Use the paginated keywords data to display rows -->
              <tr
                v-for="keyword in props.keywords.data"
                :key="keyword.keyword_uuid || keyword.keyword"
              >
                <td
                  class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap"
                >
                  {{ keyword.keyword }}
                </td>
                <td
                  class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap"
                >
                  {{ keyword.total_volume || 0 }}
                  <!-- Display total volume -->
                </td>
                <td
                  v-for="month in monthNames"
                  :key="month"
                  class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap"
                >
                  {{ keyword.monthly_searches[monthIndex(month)] || 0 }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination for keywords -->
      <Pagination :links="props.keywords.links" />
    </div>
  </AppLayout>
</template>
