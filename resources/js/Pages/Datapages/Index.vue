<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Pagination from "@/Components/Paginator.vue";
import { reactive, onMounted, ref, computed } from "vue";
import { Link, router } from "@inertiajs/vue3";
import NavLink from "@/Components/NavLink.vue";

// Import vue-chartjs components
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

// Register necessary chart.js components
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
  pages: Object,
  metrics: Object,
  pageviews: Array, // Array to match the received data structure
});

// Add the truncateUrl method here
function truncateUrl(url) {
  const maxLength = 50;
  return url.length > maxLength ? `${url.substring(0, maxLength)}...` : url;
}

const dateSelector = reactive({
  form_search: null,
  fromDate: null,
  toDate: null,
});

// Function to format date to YYYY-MM-DD
function formatDate(date) {
  return new Date(date).toISOString().split("T")[0];
}

// Set or retrieve dates when the component is mounted
onMounted(() => {
  const storedFromDate = localStorage.getItem("fromDate");
  const storedToDate = localStorage.getItem("toDate");
  const storedDateSet = localStorage.getItem("dateSet");

  const today = new Date();
  const formattedToday = formatDate(today);

  // Check if the stored date is today's date
  if (storedDateSet === formattedToday && storedFromDate && storedToDate) {
    dateSelector.fromDate = storedFromDate;
    dateSelector.toDate = storedToDate;
  } else {
    // Set default dates to the last 28 days excluding today
    const toDate = new Date(today);
    toDate.setDate(toDate.getDate() - 1); // Set to yesterday

    const fromDate = new Date(toDate);
    fromDate.setDate(fromDate.getDate() - 27); // Set to 28 days before yesterday

    dateSelector.fromDate = formatDate(fromDate);
    dateSelector.toDate = formatDate(toDate);

    // Store the current date as the date set
    localStorage.setItem("dateSet", formattedToday);
    localStorage.setItem("fromDate", dateSelector.fromDate);
    localStorage.setItem("toDate", dateSelector.toDate);
  }
});

// Convert props.pageviews data to the required chart.js format
const chartData = computed(() => {
  const labels = [];
  const data = [];

  if (props.pageviews && Array.isArray(props.pageviews)) {
    props.pageviews.forEach((item) => {
      labels.push(formatDateChart(item.date));
      data.push(item.pageviews);
    });
  }

  return {
    labels, // Dates for x-axis
    datasets: [
      {
        label: "Pageviews",
        data, // Pageview counts for y-axis
        borderColor: "#4f46e5", // Indigo
        backgroundColor: "rgba(79, 70, 229, 0.1)",
        fill: true,
        tension: 0.1,
      },
    ],
  };
});

// Options for the Line Chart
const chartOptions = {
  responsive: true,
  plugins: {
    legend: {
      position: "top",
    },
    title: {
      display: true,
      text: "Pageviews Trend",
    },
  },
};

// Method to submit date range and store in localStorage
function submitDateRange() {
  if (!dateSelector.fromDate || !dateSelector.toDate) {
    alert("Please select both from and to dates");
    return;
  }

  localStorage.setItem("fromDate", dateSelector.fromDate);
  localStorage.setItem("toDate", dateSelector.toDate);

  router.get("/pages", {
    preserveQuery: true,
    from: dateSelector.fromDate,
    to: dateSelector.toDate,
  });
}

function formatDateChart(dateString) {
  const months = [
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
  const date = new Date(dateString);
  const formattedDate = `${months[date.getMonth()]} ${date
    .getDate()
    .toString()
    .padStart(2, "0")}`;
  return formattedDate;
}

const tabs = [
  { name: "All Pages", href: "/pages", current: true },
  { name: "Technical", href: "/technical", current: false },
  { name: "Content", href: "/content", current: false },
  { name: "Ranking", href: "/ranking", current: false },
];
</script>

<template>
  <Head title="Pages" />
  <AppLayout title="Pages">
    <div class="mt-5">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="relative pb-5 border-b border-gray-200 sm:pb-0">
          <div class="sm:flex-auto md:flex md:items-center md:justify-between">
            <h1 class="text-base font-semibold leading-6 text-gray-900">
              Pages
            </h1>
            <div
              class="flex mt-3 space-x-2 md:absolute md:right-0 md:top-5 md:mt-0"
            >
              <input
                type="date"
                v-model="dateSelector.fromDate"
                class="border-gray-300 rounded-md"
              />
              <input
                type="date"
                v-model="dateSelector.toDate"
                class="border-gray-300 rounded-md"
              />
              <button
                @click="submitDateRange"
                class="px-4 py-2 text-white bg-blue-500 rounded-md"
              >
                Data Period
              </button>
            </div>
          </div>
        </div>
        <div class="mb-5">
          <div class="mt-5 mb-4"></div>
        </div>
        <div class="">
          <div>
            <dl class="grid grid-cols-1 gap-5 mt-5 sm:grid-cols-5">
              <div
                class="px-4 py-5 overflow-hidden bg-white rounded-lg shadow sm:p-6"
              >
                <dt class="text-sm font-medium text-gray-500 truncate">
                  Sessions
                </dt>
                <dd
                  class="mt-1 text-3xl font-semibold tracking-tight text-gray-900"
                >
                  {{ metrics.sessions }}
                </dd>
              </div>
              <div
                class="px-4 py-5 overflow-hidden bg-white rounded-lg shadow sm:p-6"
              >
                <dt class="text-sm font-medium text-gray-500 truncate">
                  Pageviews
                </dt>
                <dd
                  class="mt-1 text-3xl font-semibold tracking-tight text-gray-900"
                >
                  {{ metrics.pageviews }}
                </dd>
              </div>
              <div
                class="px-4 py-5 overflow-hidden bg-white rounded-lg shadow sm:p-6"
              >
                <dt class="text-sm font-medium text-gray-500 truncate">
                  Entrances
                </dt>
                <dd
                  class="mt-1 text-3xl font-semibold tracking-tight text-gray-900"
                >
                  {{ metrics.entrances }}
                </dd>
              </div>
              <div
                class="px-4 py-5 overflow-hidden bg-white rounded-lg shadow sm:p-6"
              >
                <dt class="text-sm font-medium text-gray-500 truncate">
                  Exits
                </dt>
                <dd
                  class="mt-1 text-3xl font-semibold tracking-tight text-gray-900"
                >
                  {{ metrics.exits }}
                </dd>
              </div>
              <div
                class="px-4 py-5 overflow-hidden bg-white rounded-lg shadow sm:p-6"
              >
                <dt class="text-sm font-medium text-gray-500 truncate">
                  Bounce
                </dt>
                <dd
                  class="mt-1 text-3xl font-semibold tracking-tight text-gray-900"
                >
                  {{ metrics.bounce }}
                </dd>
              </div>
            </dl>
          </div>
        </div>

        <div class="mt-10 bg-white rounded-lg shadow sm:p-6">
          <!-- Replace Google Chart with vue-chartjs -->
          <Line :data="chartData" :options="chartOptions" />
        </div>

        <div
          class="mt-10 overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg"
        >
          <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
              <tr>
                <th
                  scope="col"
                  class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6"
                >
                  Page Path
                </th>
                <th
                  scope="col"
                  class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                >
                  Pageviews
                </th>
                <th
                  scope="col"
                  class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                >
                  Bounce%
                </th>
                <th
                  scope="col"
                  class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                >
                  Entrance%
                </th>
                <th
                  scope="col"
                  class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                >
                  Exit%
                </th>
                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                  <span class="sr-only">Edit</span>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="page in pages.data" :key="page.id">
                <td
                  class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 whitespace-nowrap sm:pl-6"
                >
                  <Link
                    :href="`/page/view/${page.url_code}`"
                    class="hover:underline"
                    >{{ truncateUrl(page.url) }}</Link
                  >
                </td>
                <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                  {{ page.pageviews }}
                </td>
                <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                  {{ ((page.bounces / page.pageviews) * 100).toFixed(0) }}%
                </td>
                <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                  {{ ((page.entrances / page.pageviews) * 100).toFixed(0) }}%
                </td>
                <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                  {{ ((page.exits / page.pageviews) * 100).toFixed(0) }}%
                </td>
                <td
                  class="relative py-4 pl-3 pr-4 text-sm font-medium text-right whitespace-nowrap sm:pr-6"
                >
                  <Link
                    :href="'/project/${project.id}/edit'"
                    class="text-indigo-600 hover:text-indigo-900"
                  >
                    Edit
                  </Link>
                </td>
              </tr>

              <!-- More people... -->
            </tbody>
          </table>
        </div>
        <Pagination :links="pages.links" class="pb-10 mt-6" />
      </div>
    </div>
  </AppLayout>
</template>
