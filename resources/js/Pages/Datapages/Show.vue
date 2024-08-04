<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Pagination from "@/Components/Paginator.vue";
import { reactive, onMounted, ref } from "vue";
import { Link, router } from "@inertiajs/vue3";

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
import Breadcrumbs from "@/Components/Breadcrumbs.vue";

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
  page: Object,
  metrics: Object,
  pageviews: Object,
  params: Array,
  sources: Object,
});

// Add the truncateUrl method here
function truncateUrl(url) {
  const maxLength = 50;
  return url.length > maxLength ? `${url.substring(0, maxLength)}...` : url;
}

// Add the truncateUrl method here
function truncateUrlShort(url) {
  const maxLength = 20;
  return url.length > maxLength ? `${url.substring(0, maxLength)}...` : url;
}

//shorten the text to 20 characters
function Shorten(text) {
  const maxLength = 20;
  return text.length > maxLength ? `${text.substring(0, maxLength)}...` : text;
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
  const today = new Date();
  today.setDate(today.getDate() - 1); // Set to yesterday

  if (storedFromDate && storedToDate) {
    dateSelector.fromDate = storedFromDate;
    dateSelector.toDate = storedToDate;
  } else {
    const fromDate = new Date();
    fromDate.setDate(today.getDate() - 29); // Set to 28 days ago
    dateSelector.fromDate = formatDate(fromDate);
    dateSelector.toDate = formatDate(today);
  }
});

// Method to submit date range and store in localStorage
function submitDateRange() {
  if (!dateSelector.fromDate || !dateSelector.toDate) {
    alert("Please select both from and to dates");
    return;
  }

  localStorage.setItem("fromDate", dateSelector.fromDate);
  localStorage.setItem("toDate", dateSelector.toDate);

  let pagePath = window.location.pathname;

  router.get(pagePath, {
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

// Initialize chart data
const chartData = ref({
  labels: [], // Dates for x-axis
  datasets: [
    {
      label: "Pageviews",
      data: [], // Pageview counts for y-axis
      borderColor: "#4f46e5", // Indigo
      backgroundColor: "rgba(79, 70, 229, 0.1)",
      tension: 0.1,
    },
  ],
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

// Convert pageviews data to chart.js format
onMounted(() => {
  if (props.pageviews && props.pageviews.length > 0) {
    const labels = [];
    const data = [];

    props.pageviews.forEach((item) => {
      labels.push(formatDateChart(item.date));
      data.push(item.pageviews);
    });

    // Assign data to chartData
    chartData.value.labels = labels;
    chartData.value.datasets[0].data = data;
  }
});

const pageTitle = truncateUrlShort(props.page.title);
const pageUrl = Shorten(props.page.url);

// Define breadcrumb data for this page
const breadcrumbs = [
  { name: "All Pages", href: "/pages", current: false },
  { name: pageTitle, current: true },
  { name: pageUrl, current: true },
];
</script>

<template>
  <AppLayout title="Page">
    <template #header>
      <div class="flex justify-between">
        <div>
          <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <Breadcrumbs :pages="breadcrumbs" />
          </h2>
        </div>
        <div>
          <div class="flex space-x-2">
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
    </template>

    <div class="mt-10">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="mb-5">
          <div class="flex justify-between px-2 py-2 mt-5 mb-4">
            <div class="">
              <h2>{{ page.title }}</h2>
            </div>
            <div>
              <span class="font-semibold">{{ truncateUrl(page.url) }}</span>
            </div>
          </div>
          <div></div>
        </div>
        <div class="">
          <!-- start list of metrics --->
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
        <!-- end list of metrics --->
        <div>
          <dl class="grid grid-cols-1 gap-5 mt-5 sm:grid-cols-5">
            <div class="bg-white rounded-lg shadow sm:col-span-3 sm:p-6">
              <!-- Replace Google Chart with vue-chartjs -->
              <Line :data="chartData" :options="chartOptions" />
            </div>
            <div class="sm:col-span-2">
              <h2 class="px-2 py-2">Technical data</h2>
              <ul
                role="list"
                class="overflow-hidden bg-white divide-y divide-gray-100 shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl"
              >
                <li
                  class="relative flex justify-between px-4 py-5 gap-x-6 hover:bg-gray-50 sm:px-6"
                >
                  <div class="flex min-w-0 gap-x-4">
                    <div class="flex-auto min-w-0">
                      <p class="text-sm font-semibold leading-6 text-gray-900">
                        <a href="#">
                          <span
                            class="absolute inset-x-0 bottom-0 -top-px"
                          ></span>
                          Form count
                        </a>
                      </p>
                    </div>
                  </div>
                  <div class="flex items-center shrink-0 gap-x-4">
                    <div class="hidden sm:flex sm:flex-col sm:items-end">
                      <p class="text-sm leading-6 text-gray-900">
                        {{ page.form_count }}
                      </p>
                    </div>
                  </div>
                </li>
                <li
                  class="relative flex justify-between px-4 py-5 gap-x-6 hover:bg-gray-50 sm:px-6"
                >
                  <div class="flex min-w-0 gap-x-4">
                    <div class="flex-auto min-w-0">
                      <p class="text-sm font-semibold leading-6 text-gray-900">
                        <a href="#">
                          <span
                            class="absolute inset-x-0 bottom-0 -top-px"
                          ></span>
                          Inbound Links
                        </a>
                      </p>
                    </div>
                  </div>
                  <div class="flex items-center shrink-0 gap-x-4">
                    <div class="hidden sm:flex sm:flex-col sm:items-end">
                      <p class="text-sm leading-6 text-gray-900">
                        {{ page.inbound_links }}
                      </p>
                    </div>
                  </div>
                </li>
                <li
                  class="relative flex justify-between px-4 py-5 gap-x-6 hover:bg-gray-50 sm:px-6"
                >
                  <div class="flex min-w-0 gap-x-4">
                    <div class="flex-auto min-w-0">
                      <p class="text-sm font-semibold leading-6 text-gray-900">
                        <a href="#">
                          <span
                            class="absolute inset-x-0 bottom-0 -top-px"
                          ></span>
                          Outbound Links
                        </a>
                      </p>
                    </div>
                  </div>
                  <div class="flex items-center shrink-0 gap-x-4">
                    <div class="hidden sm:flex sm:flex-col sm:items-end">
                      <p class="text-sm leading-6 text-gray-900">
                        {{ page.outbound_links }}
                      </p>
                    </div>
                  </div>
                </li>
                <li
                  class="relative flex justify-between px-4 py-5 gap-x-6 hover:bg-gray-50 sm:px-6"
                >
                  <div class="flex min-w-0 gap-x-4">
                    <div class="flex-auto min-w-0">
                      <p class="text-sm font-semibold leading-6 text-gray-900">
                        <a href="#">
                          <span
                            class="absolute inset-x-0 bottom-0 -top-px"
                          ></span>
                          Word Count
                        </a>
                      </p>
                    </div>
                  </div>
                  <div class="flex items-center shrink-0 gap-x-4">
                    <div class="hidden sm:flex sm:flex-col sm:items-end">
                      <p class="text-sm leading-6 text-gray-900">
                        {{ page.word_count }}
                      </p>
                    </div>
                  </div>
                </li>
                <li
                  class="relative flex justify-between px-4 py-5 gap-x-6 hover:bg-gray-50 sm:px-6"
                >
                  <div class="flex min-w-0 gap-x-4">
                    <div class="flex-auto min-w-0">
                      <p class="text-sm font-semibold leading-6 text-gray-900">
                        <a href="#">
                          <span
                            class="absolute inset-x-0 bottom-0 -top-px"
                          ></span>
                          Protocol
                        </a>
                      </p>
                    </div>
                  </div>
                  <div class="flex items-center shrink-0 gap-x-4">
                    <div class="hidden sm:flex sm:flex-col sm:items-end">
                      <p class="text-sm leading-6 text-gray-900">
                        {{ page.protocol }}
                      </p>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
          </dl>
        </div>

        <dl class="grid grid-cols-1 gap-5 mt-5 sm:grid-cols-3">
          <div>
            <!-- start list of parameters --->
            <h2 class="px-2 py-2">Parameters</h2>
            <ul
              class="overflow-hidden bg-white divide-y divide-gray-100 shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl"
            >
              <li
                v-for="(parameter, index) in params"
                :key="index"
                class="relative flex justify-between px-4 py-5 gap-x-6 sm:px-6"
              >
                <div class="flex min-w-0 gap-x-4">
                  <div class="flex-auto min-w-0">
                    <p class="text-sm font-semibold leading-6 text-gray-900">
                      <span class="absolute inset-x-0 bottom-0 -top-px"></span>
                      {{ parameter }}
                    </p>
                  </div>
                </div>
                <div class="flex items-center shrink-0 gap-x-4">
                  <div class="hidden sm:flex sm:flex-col sm:items-end">
                    <p class="text-sm leading-6 text-gray-900">
                      <button
                        class="px-3 py-1 text-xs leading-5 text-white bg-blue-500 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300"
                      >
                        Add to filter
                      </button>
                    </p>
                  </div>
                </div>
              </li>
            </ul>
          </div>
          <!-- end list of parameters --->
        </dl>
      </div>

      <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px lg:px-0">
          <div class="sm:px-6 lg:px-8">
            <div class="flow-root mt-0">
              <h2 class="px-2 py-2">Sources</h2>
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
                            Traffic Source
                          </th>
                          <th
                            scope="col"
                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                          >
                            Sessions
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
                            Bounce Rate
                          </th>
                          <th
                            scope="col"
                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                          >
                            Entrance Rate
                          </th>
                          <th
                            scope="col"
                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                          >
                            Exit Rate
                          </th>
                        </tr>
                      </thead>
                      <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="source in sources" :key="source.id">
                          <td
                            class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 whitespace-nowrap sm:pl-6"
                          >
                            <b>{{ source.source_type }}</b>
                          </td>
                          <td
                            class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap"
                          >
                            {{ source.sessions }}
                          </td>
                          <td
                            class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap"
                          >
                            {{ source.pageviews }}
                          </td>
                          <td
                            class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap"
                          >
                            {{
                              (
                                (source.bounces / metrics.sessions) *
                                100
                              ).toFixed(0)
                            }}%
                          </td>
                          <td
                            class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap"
                          >
                            {{
                              (
                                (source.entrances / metrics.sessions) *
                                100
                              ).toFixed(0)
                            }}%
                          </td>
                          <td
                            class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap"
                          >
                            {{
                              ((source.exits / metrics.sessions) * 100).toFixed(
                                0
                              )
                            }}%
                          </td>
                        </tr>

                        <!-- More people... -->
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            <Pagination class="mt-6" />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
