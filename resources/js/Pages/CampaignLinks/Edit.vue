<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps } from "vue";
import EditCampaignLinkForm from "@/Pages/CampaignLinks/components/EditCampaignLinkForm.vue";

const props = defineProps({
  link: Object,
  campaigns: Array,
});
</script>

<template>
  <Head title="Edit Campaign Link" />
  <AppLayout title="Edit Campaign Link">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Edit Campaign Link
      </h2>
    </template>
    <div>
      <div class="py-10 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Pass the campaigns prop and the current link to the EditCampaignLinkForm -->
        <EditCampaignLinkForm :link="link" :campaigns="campaigns" />
      </div>
    </div>
  </AppLayout>
</template>
