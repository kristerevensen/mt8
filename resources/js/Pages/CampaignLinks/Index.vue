<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps } from "vue";
</script>

<template>
  <Head title="Campaign Link" />
  <AppLayout title="Campaign Link">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Campaign links
      </h2>
    </template>
    <div>
      <div class="py-10 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Pass the campaigns prop and the current link to the EditCampaignLinkForm -->
      </div>
    </div>
  </AppLayout>
</template>
