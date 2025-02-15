<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import BarChart from "@/Components/BarChart.vue";
import { ref } from "vue";

const chartData = ref({
  labels: ["Januar", "Februar", "Mars", "April", "Mai"],
  datasets: [
    {
      label: "Klikk",
      backgroundColor: "#111827",
      data: [1240, 1580, 2100, 1890, 2400],
    },
  ],
});

const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    },
    title: {
      display: false
    },
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        display: false
      },
      ticks: {
        font: {
          family: "'Inter', sans-serif",
          size: 12
        },
        color: '#6B7280'
      }
    },
    x: {
      grid: {
        display: false
      },
      ticks: {
        font: {
          family: "'Inter', sans-serif",
          size: 12
        },
        color: '#6B7280'
      }
    }
  }
});

const stats = [
  { id: 1, name: 'Totale Klikk', value: '12,897', unit: 'klikk', change: '+24.75%', trend: 'increase' },
  { id: 2, name: 'Aktive Kampanjer', value: '8', unit: 'kampanjer', change: '+2', trend: 'increase' },
  { id: 3, name: 'Beste Kilde', value: 'Facebook', unit: '45% av trafikk', change: '+5.2%', trend: 'increase' },
  { id: 4, name: 'Konverteringsrate', value: '3.2', unit: 'prosent', change: '+0.8%', trend: 'increase' },
];

const topCampaigns = [
  { 
    id: 1, 
    name: 'Summer Sale 2024',
    clicks: 2445,
    sources: {
      facebook: 45,
      instagram: 30,
      email: 25
    },
    trend: '+12%'
  },
  { 
    id: 2, 
    name: 'Product Launch',
    clicks: 1876,
    sources: {
      facebook: 35,
      instagram: 40,
      email: 25
    },
    trend: '+8%'
  }
];
</script>

<template>
  <AppLayout title="Dashboard">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
          <h1 class="text-2xl font-semibold text-gray-900">
            Campaign Dashboard
          </h1>
          <div class="flex items-center gap-4">
            <button class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
              Last ned rapport
            </button>
            <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
              Ny kampanje
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
      <!-- Stats Grid -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div v-for="stat in stats" :key="stat.id" class="rounded-lg border border-gray-200 bg-white p-6">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-medium text-gray-500">{{ stat.name }}</h3>
            <span 
              :class="[
                stat.trend === 'increase' ? 'text-green-600' : 'text-red-600',
                'inline-flex items-center rounded-full px-2 py-1 text-xs font-medium'
              ]"
            >
              {{ stat.change }}
            </span>
          </div>
          <div class="mt-2 flex items-baseline">
            <p class="text-2xl font-semibold text-gray-900">{{ stat.value }}</p>
            <p class="ml-2 text-sm text-gray-500">{{ stat.unit }}</p>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Clicks Chart -->
        <div class="rounded-lg border border-gray-200 bg-white p-6">
          <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Klikk per måned</h3>
            <div class="flex items-center space-x-2">
              <button class="rounded-md px-3 py-1.5 text-sm font-medium text-gray-600 hover:text-gray-900">7D</button>
              <button class="rounded-md bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-900">30D</button>
              <button class="rounded-md px-3 py-1.5 text-sm font-medium text-gray-600 hover:text-gray-900">1Y</button>
            </div>
          </div>
          <div class="h-72">
            <BarChart :chartData="chartData" :chartOptions="chartOptions" />
          </div>
        </div>

        <!-- Top Campaigns -->
        <div class="rounded-lg border border-gray-200 bg-white p-6">
          <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Topp kampanjer</h3>
            <button class="text-sm font-medium text-gray-600 hover:text-gray-900">Se alle</button>
          </div>
          <div class="space-y-6">
            <div v-for="campaign in topCampaigns" :key="campaign.id" class="flex items-start space-x-3">
              <div class="flex-1 space-y-3">
                <div class="flex items-center justify-between">
                  <p class="text-sm font-medium text-gray-900">{{ campaign.name }}</p>
                  <span class="text-sm text-green-600">{{ campaign.trend }}</span>
                </div>
                <div class="flex items-center space-x-2">
                  <span class="text-sm text-gray-500">{{ campaign.clicks }} klikk</span>
                  <span class="text-gray-300">|</span>
                  <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-full">
                      FB {{ campaign.sources.facebook }}%
                    </span>
                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-pink-600 bg-pink-100 rounded-full">
                      IG {{ campaign.sources.instagram }}%
                    </span>
                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-purple-600 bg-purple-100 rounded-full">
                      Email {{ campaign.sources.email }}%
                    </span>
                  </div>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-1.5">
                  <div 
                    class="bg-gray-900 h-1.5 rounded-full" 
                    :style="{ width: campaign.sources.facebook + '%' }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>