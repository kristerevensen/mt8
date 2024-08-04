<script setup>
import { Link } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";

// Define the campaigns prop
defineProps({
  campaigns: Array,
});

// Initialize form data for Inertia.js
const form = useForm({
  campaign_id: "",
});

// Function to delete a campaign
const deleteCampaign = (campaign_id) => {
  if (confirm("Are you sure you want to delete this campaign?")) {
    const del = form.delete(route("campaigns.destroy", campaign_id));
    del.then(() => {
      console.log("Campaign deleted");
    });
  }
};
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8">
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
                    scope="col"
                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6"
                  >
                    Campaign
                  </th>
                  <th
                    scope="col"
                    class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                  >
                    Campaign Code
                  </th>
                  <th
                    scope="col"
                    class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                  >
                    Groups
                  </th>
                  <th
                    scope="col"
                    class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                  >
                    Links
                  </th>
                  <th
                    scope="col"
                    class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                  >
                    Clicks
                  </th>
                  <th
                    scope="col"
                    class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                  >
                    Status
                  </th>
                  <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                    <span class="sr-only">Actions</span>
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(item, index) in $page.props.campaigns" :key="index">
                  <td
                    class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 whitespace-nowrap sm:pl-6"
                  >
                    {{ item.campaign_name }}
                  </td>
                  <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                    {{ item.campaign_token }}
                  </td>
                  <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                    Groups
                  </td>
                  <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                    Links
                  </td>
                  <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                    Clicks
                  </td>
                  <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                    {{ item.status ? "Active" : "Inactive" }}
                  </td>
                  <td
                    class="relative py-4 pl-3 pr-4 text-sm font-medium text-right whitespace-nowrap sm:pr-6"
                  >
                    <Link
                      :href="route('campaigns.show', item.campaign_token)"
                      class="text-indigo-600 hover:text-indigo-900"
                      >View<span class="sr-only">
                        {{ item.campaign_token }}</span
                      ></Link
                    >
                    <Link
                      :href="route('campaigns.edit', item.id)"
                      class="ml-4 text-indigo-600 hover:text-indigo-900"
                      >Edit<span class="sr-only">
                        {{ item.campaign_name }}</span
                      ></Link
                    >
                    <button
                      @click="deleteCampaign(item.id)"
                      class="ml-4 text-red-600 hover:text-red-900"
                    >
                      Delete<span class="sr-only"
                        >, {{ item.campaign_name }}</span
                      >
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

