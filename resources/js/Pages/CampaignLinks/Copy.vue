<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps } from "vue";
import CopyCampaignLink from "@/Pages/CampaignLinks/components/CopyCampaignLink.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";

const props = defineProps({
  link: Object,
  campaigns: Array,
  campaign_token: String,
  campaign_name: String,
});

const breadcrumbs = [
  { name: "All Campaigns", href: "/campaigns", current: false },
  {
    name: props.campaign_name,
    href: "/campaigns/" + props.campaign_token,
    current: false,
  },
  { name: "Copy Campaign Link", current: true },
];
</script>

<template>
  <Head title="Edit Campaign Link" />
  <AppLayout title="Edit Campaign Link">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        <Breadcrumbs :pages="breadcrumbs" />
      </h2>
    </template>
    <div>
      <div class="py-10 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Pass the campaigns prop and the current link to the CopyCampaignLink -->
        <CopyCampaignLink :link="link" :campaigns="campaigns" />
      </div>
    </div>
  </AppLayout>
</template>
