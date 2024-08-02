
<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps, ref } from "vue";
import CreateCampaignForm from "@/Pages/Campaigns/components/CreateCampaignForm.vue";

// Define props to accept teams from the parent component
const props = defineProps({
  teams: Array,
});
</script>

<template>
  <Head title="Campaigns" />
  <AppLayout title="Campaigns">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Create Campaign
      </h2>
    </template>
    <div>
      <div class="py-10 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Pass the teams prop to the CreateCampaignForm -->
        <CreateCampaignForm :teams="teams" />
      </div>
    </div>
  </AppLayout>
</template>
