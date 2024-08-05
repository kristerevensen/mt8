<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps } from "vue";
import CreateCampaignLinkForm from "@/Pages/CampaignLinks/components/CreateCampaignLinkForm.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";

const props = defineProps({
  campaigns: Array,
  campaign_token: String,
  campaign_id: Number,
});

const breadcrumbs = [
  { name: "All Campaigns", href: "/campaigns", current: false },
  {
    name: "New Campaign Link",
    current: true,
  },
];
</script>

<template>
  <Head title="Campaign Links" />
  <AppLayout title="Campaign Links">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        <Breadcrumbs :pages="breadcrumbs" />
      </h2>
    </template>
    <div>
      <div class="py-10 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Pass the campaigns prop to the CreateCampaignLinkForm -->
        <CreateCampaignLinkForm :campaigns="campaigns" />
      </div>
    </div>
  </AppLayout>
</template>
