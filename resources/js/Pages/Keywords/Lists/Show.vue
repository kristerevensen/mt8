<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import Pagination from "@/Components/Pagination.vue";
import Chart from "chart.js/auto";

// Props from the server-side
const props = defineProps({
  project: Object,
  keywordList: Object,
  keywords: Object, // Paginated keywords with search volume data
  searchVolumes: Array, // Array of search volume data for each keyword per month
});

// Data tracking
const selectedKeywords = ref([]); // For tracking selected keywords

// Initialize breadcrumbs with correct navigation
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

// Get the monthly search volume data for the chart
const monthlySearchVolumes = computed(() => {
  // Initialize an array with zeros for each month
  const monthlyData = new Array(12).fill(0);

  // Iterate over the `props.searchVolumes` data to fill the monthlyData array
  if (props.searchVolumes) {
    Object.entries(props.searchVolumes).forEach(([date, volume]) => {
      const [year, month] = date.split("-").map(Number);
      const monthIndex = month - 1; // Convert month to array index (0-11)
      monthlyData[monthIndex] += volume; // Aggregate search volume for the month
    });
  }

  return monthlyData;
});

// Function to format date
const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};

// Chart setup for monthly search volume
const chartRef = ref(null);
const renderChart = () => {
  if (chartRef.value) {
    const ctx = chartRef.value.getContext("2d");
    new Chart(ctx, {
      type: "bar",
      data: {
        labels: [
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
        ],
        datasets: [
          {
            label: "Total Monthly Search Volume",
            data: monthlySearchVolumes.value,
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

// Call renderChart on component mount
onMounted(() => {
  renderChart();
});
watch(
  () => monthlySearchVolumes.value,
  () => {
    renderChart(); // Re-render the chart whenever `monthlySearchVolumes` changes
  }
);
</script>

<template>
  <Head title="Keyword List" />
  <AppLayout title="Keyword List">
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <!-- Breadcrumbs component showing navigation -->
            <Breadcrumbs :pages="breadcrumbs" />
          </h2>
        </div>
      </div>
    </template>

    <div class="py-10 mx-auto max-w-7xl sm:px-6 lg:px-8">
      <!-- Chart Section -->
      <div class="p-6 mb-8 bg-white rounded-lg shadow-md">
        <h3 class="mb-4 text-lg font-medium text-gray-900">
          Monthly Search Volume
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
                  scope="col"
                  class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                >
                  Keyword
                </th>
                <!-- Add table headers for each month -->
                <th
                  v-for="month in [
                    'Jan',
                    'Feb',
                    'Mar',
                    'Apr',
                    'May',
                    'Jun',
                    'Jul',
                    'Aug',
                    'Sep',
                    'Oct',
                    'Nov',
                    'Dec',
                  ]"
                  :key="month"
                  class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                >
                  {{ month }}
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="keyword in keywords.data" :key="keyword.keyword_uuid">
                <td
                  class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap"
                >
                  <Link
                    :href="route('keywords.show', [keyword.keyword_uuid])"
                    class="text-indigo-600 hover:text-indigo-900"
                    title="View keyword details"
                  >
                    {{
                      keyword.keyword.length > 30
                        ? keyword.keyword.substring(0, 30) + "..."
                        : keyword.keyword
                    }}
                  </Link>
                </td>
                <!-- Monthly Search Volume Data (limited to current keyword's monthly search volumes) -->
                <!-- Monthly Search Volume Data -->
                <td
                  v-for="month in [
                    'Jan',
                    'Feb',
                    'Mar',
                    'Apr',
                    'May',
                    'Jun',
                    'Jul',
                    'Aug',
                    'Sep',
                    'Oct',
                    'Nov',
                    'Dec',
                  ]"
                  :key="month"
                  class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap"
                >
                  {{
                    // Map keyword.monthly_searches to month index and find the corresponding value
                    keyword.monthly_searches && keyword.monthly_searches.length
                      ? keyword.monthly_searches.find((search) => {
                          const searchMonth = new Date(
                            search.date
                          ).toLocaleString("default", { month: "short" });
                          return searchMonth === month;
                        })?.search_volume || 0
                      : 0
                  }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination for keywords -->
      <Pagination :links="keywords.links" />
    </div>
  </AppLayout>
</template>
