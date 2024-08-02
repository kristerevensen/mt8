<template>
  <Head title="Campaigns" />
  <AppLayout title="Campaigns">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Campaigns
      </h2>
    </template>
    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-0">
        <Campaigns />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps, computed } from "vue";
import Campaigns from "@/Pages/Campaigns/components/Campaigns.vue";

// Define props to accept campaigns from the parent component
const props = defineProps({
  campaigns: Array,
});

// Access the authenticated user from the Inertia page props
const user = computed(() => page.props.auth.user);

// Assign the campaigns prop to a local constant
const campaigns = props.campaigns;

// Function to handle campaign deletion
const deleteCampaign = (id) => {
  if (confirm("Are you sure you want to delete this campaign?")) {
    Inertia.delete(route("campaigns.destroy", id));
  }
};
</script>
