<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps } from "vue";
import EditCampaignForm from "@/Pages/Campaigns/components/EditCampaignForm.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";

// Define props to accept campaigns and projects from the parent component
const props = defineProps({
  campaign: Object,
  projects: Array,
});
const breadcrumbs = [
  { name: "All Campaigns", href: "/campaigns", current: false },
  { name: "Edit Campaign", current: true },
];
</script>

<template>
  <Head title="Edit Campaign" />
  <AppLayout title="Edit Campaign">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        <Breadcrumbs :pages="breadcrumbs" />
      </h2>
    </template>
    <div>
      <div class="py-10 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Pass the campaign and projects prop to the EditCampaignForm -->
        <EditCampaignForm :campaign="campaign" :projects="projects" />
      </div>
    </div>
  </AppLayout>
</template>
