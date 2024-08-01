<template>
  <AppLayout title="Dashboard">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Dashboard
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
          <!-- Pass chartData and chartOptions as props -->
          <div class="p-6 max-w-96">
            <BarChart :chartData="chartData" :chartOptions="chartOptions" />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Welcome from "@/Components/Welcome.vue";
import BarChart from "@/Components/BarChart.vue"; // Ensure correct import path
import { ref } from "vue";

// Initialize chart data
const chartData = ref({
  labels: ["January", "February", "March", "April", "May"],
  datasets: [
    {
      label: "Sales",
      backgroundColor: "#42A5F5",
      data: [40, 20, 30, 50, 60],
    },
  ],
});

// Initialize chart options
const chartOptions = ref({
  responsive: true,
  plugins: {
    legend: {
      position: "top",
    },
    title: {
      display: true,
      text: "Monthly Sales",
    },
  },
});
</script>
