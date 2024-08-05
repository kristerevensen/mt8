
<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps, ref } from "vue";
import CreateCampaignForm from "@/Pages/Campaigns/components/CreateCampaignForm.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";

// Define props to accept teams from the parent component
const props = defineProps({
  teams: Array,
});
const breadcrumbs = [
  { name: "All Campaigns", href: "/campaigns", current: false },
  { name: "Create Campaign", current: true },
];
</script>

<template>
  <Head title="Campaigns" />
  <AppLayout title="Campaigns">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        <Breadcrumbs :pages="breadcrumbs" />
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
