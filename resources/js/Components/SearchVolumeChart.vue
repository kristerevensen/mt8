<template>
  <div class="chart-container">
    <h2 class="mb-4 text-lg font-semibold">Search Volume per Month</h2>
    <Bar :data="chartData" :options="chartOptions" />
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import { Bar } from "vue-chartjs";
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
} from "chart.js";

// Register Chart.js components
ChartJS.register(
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale
);

// Props passed from parent component
const props = defineProps({
  searchVolumes: Array, // Array of monthly search volumes for the keywords
});

// Data for Chart.js
const chartData = ref({
  labels: [],
  datasets: [
    {
      label: "Search Volume",
      backgroundColor: "#4B5563", // Customize the color as needed
      data: [],
    },
  ],
});

// Chart options
const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  scales: {
    y: {
      beginAtZero: true,
    },
  },
});

// Update chart data based on props
const updateChartData = () => {
  // Generate labels in the format 'Year-Month'
  const months = props.searchVolumes.map(
    (item) => `${item.year}-${item.month}`
  );
  const volumes = props.searchVolumes.map((item) => item.search_volume);

  chartData.value.labels = months;
  chartData.value.datasets[0].data = volumes;
};

// Watch for changes in searchVolumes and update chart
watch(() => props.searchVolumes, updateChartData);

// Initial update on mount
onMounted(() => {
  updateChartData();
});
</script>

<style scoped>
.chart-container {
  height: 400px; /* Adjust height as needed */
  width: 100%; /* Adjust width as needed */
}
</style>
