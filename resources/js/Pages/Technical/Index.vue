<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps } from "vue";
import CreateCampaignLinkForm from "@/Pages/CampaignLinks/components/CreateCampaignLinkForm.vue";

const props = defineProps({
  campaigns: Array,
});
</script>

<template>
  <Head title="Technical" />
  <AppLayout title="Technical">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Technical
      </h2>
    </template>
    <div>
      <div class="py-10 mx-auto max-w-7xl sm:px-6 lg:px-8"></div>
    </div>
  </AppLayout>
</template>
