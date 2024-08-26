<script setup>
import { ref, computed } from "vue";
import { Link, useForm } from "@inertiajs/vue3";

// Define the campaigns prop
const props = defineProps({
  campaigns: Object, // Expecting a paginated object
});

// Initialize form data for Inertia.js
const form = useForm({
  campaign_id: "",
});

// Sorting state
const sortColumn = ref("campaign_name");
const sortDirection = ref("asc");

// Function to toggle sorting
const toggleSort = (column) => {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
  } else {
    sortColumn.value = column;
    sortDirection.value = "asc";
  }
};

// Computed property to sort campaigns
const sortedCampaigns = computed(() => {
  if (!props.campaigns || !props.campaigns.data) return [];
  return [...props.campaigns.data].sort((a, b) => {
    const valueA = a[sortColumn.value] || "";
    const valueB = b[sortColumn.value] || "";

    if (sortDirection.value === "asc") {
      return valueA > valueB ? 1 : valueA < valueB ? -1 : 0;
    } else {
      return valueA < valueB ? 1 : valueA > valueB ? -1 : 0;
    }
  });
});

// Function to delete a campaign
const deleteCampaign = (campaign_id) => {
  if (confirm("Are you sure you want to delete this campaign?")) {
    form.delete(route("campaigns.destroy", campaign_id)).then(() => {
      console.log("Campaign deleted");
    });
  }
};
</script>



<template>
  <div class="flow-root mt-8">
    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
      <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
        <div
          class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg"
        >
          <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
              <tr>
                <th
                  @click="toggleSort('campaign_name')"
                  scope="col"
                  class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 cursor-pointer sm:pl-6"
                >
                  Campaign
                  <span v-if="sortColumn === 'campaign_name'">
                    {{ sortDirection === "asc" ? "↑" : "↓" }}
                  </span>
                </th>
                <th
                  @click="toggleSort('campaign_token')"
                  scope="col"
                  class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 cursor-pointer"
                >
                  Campaign Code
                  <span v-if="sortColumn === 'campaign_token'">
                    {{ sortDirection === "asc" ? "↑" : "↓" }}
                  </span>
                </th>
                <th
                  scope="col"
                  class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                >
                  Groups
                </th>
                <th
                  @click="toggleSort('links_count')"
                  scope="col"
                  class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 cursor-pointer"
                >
                  Links
                  <span v-if="sortColumn === 'links_count'">
                    {{ sortDirection === "asc" ? "↑" : "↓" }}
                  </span>
                </th>
                <th
                  @click="toggleSort('clicks_count')"
                  scope="col"
                  class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 cursor-pointer"
                >
                  Clicks
                  <span v-if="sortColumn === 'clicks_count'">
                    {{ sortDirection === "asc" ? "↑" : "↓" }}
                  </span>
                </th>
                <th
                  @click="toggleSort('status')"
                  scope="col"
                  class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 cursor-pointer"
                >
                  Status
                  <span v-if="sortColumn === 'status'">
                    {{ sortDirection === "asc" ? "↑" : "↓" }}
                  </span>
                </th>
                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                  <span class="sr-only">Actions</span>
                </th>
              </tr>
            </thead>
            <tbody
              v-if="sortedCampaigns.length > 0"
              class="bg-white divide-y divide-gray-200"
            >
              <tr v-for="(campaign, index) in sortedCampaigns" :key="index">
                <td
                  class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 whitespace-nowrap sm:pl-6"
                >
                  {{ campaign.campaign_name }}
                </td>
                <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                  {{ campaign.campaign_token }}
                </td>
                <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                  Groups
                </td>
                <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                  {{ campaign.links_count }}
                </td>
                <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                  {{ campaign.clicks_count }}
                </td>
                <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                  {{ campaign.status ? "Active" : "Inactive" }}
                </td>
                <td
                  class="relative py-4 pl-3 pr-4 text-sm font-medium text-right whitespace-nowrap sm:pr-6"
                >
                  <Link
                    :href="`/campaign-links/create?campaign_token=${campaign.campaign_token}`"
                    class="text-indigo-600 hover:text-indigo-900"
                  >
                    New Link<span class="sr-only">{{
                      campaign.campaign_token
                    }}</span>
                  </Link>
                  <Link
                    :href="route('campaigns.show', campaign.campaign_token)"
                    class="ml-4 text-indigo-600 hover:text-indigo-900"
                  >
                    View<span class="sr-only">{{
                      campaign.campaign_token
                    }}</span>
                  </Link>
                  <Link
                    :href="route('campaigns.edit', campaign.id)"
                    class="ml-4 text-indigo-600 hover:text-indigo-900"
                  >
                    Edit<span class="sr-only">{{
                      campaign.campaign_name
                    }}</span>
                  </Link>
                  <button
                    @click="deleteCampaign(campaign.id)"
                    class="ml-4 text-red-600 hover:text-red-900"
                  >
                    Delete<span class="sr-only">{{
                      campaign.campaign_name
                    }}</span>
                  </button>
                </td>
              </tr>
            </tbody>
            <tbody v-else>
              <tr>
                <td colspan="7" class="py-4 text-center text-gray-500">
                  No campaigns found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- Pagination Component -->
        <Pagination :links="campaigns.links" />
      </div>
    </div>
  </div>
</template>
