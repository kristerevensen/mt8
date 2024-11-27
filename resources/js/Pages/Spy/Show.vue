<script setup>
import { ref } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

// Props from the controller
const props = defineProps({
  spyRequest: Object, // Fetched spy request data
  competitors: Array, // Competitors data fetched based on uuid
  technologies: Array, // Technologies data, assuming each tech has category, subcategory, and items
});

console.log("Spy Request:", props.spyRequest);
console.log("Competitors:", props.competitors);
console.log("Technologies:", props.technologies);
</script>

<template>
  <Head title="Website Spy Details" />
  <AppLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          Website Spy Details:
          {{ props.spyRequest?.domain || "No domain available" }}
        </h2>
        <Link
          href="{{ route('website-spy.index') }}"
          class="text-sm text-gray-600 hover:text-gray-900"
        >
          Back to List
        </Link>
      </div>
    </template>

    <!-- Spy Request Details Section -->
    <div class="py-10 mx-auto max-w-7xl sm:px-6 lg:px-8">
      <!-- Ensure data is loaded correctly before trying to display it -->
      <div
        v-if="props.spyRequest"
        class="overflow-hidden bg-white shadow sm:rounded-lg"
      >
        <div class="px-4 py-5 sm:px-6">
          <h3 class="text-lg font-medium leading-6 text-gray-900">
            Website Details
          </h3>
          <p class="max-w-2xl mt-1 text-sm text-gray-500">
            Information about the website.
          </p>
        </div>
        <div class="border-t border-gray-200">
          <dl>
            <div
              class="px-4 py-5 bg-gray-50 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
            >
              <dt class="text-sm font-medium text-gray-500">Domain</dt>
              <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                {{ props.spyRequest.domain || "N/A" }}
              </dd>
            </div>
            <div
              class="px-4 py-5 bg-white sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
            >
              <dt class="text-sm font-medium text-gray-500">Description</dt>
              <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                {{ props.spyRequest.description || "No description available" }}
              </dd>
            </div>
            <div
              class="px-4 py-5 bg-gray-50 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
            >
              <dt class="text-sm font-medium text-gray-500">Domain Rank</dt>
              <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                {{ props.spyRequest.domain_rank || "N/A" }}
              </dd>
            </div>
            <div
              class="px-4 py-5 bg-white sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
            >
              <dt class="text-sm font-medium text-gray-500">Last Visited</dt>
              <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                {{ props.spyRequest.last_visited || "N/A" }}
              </dd>
            </div>
          </dl>
        </div>
      </div>
      <div v-else>
        <p class="text-red-600">No data available for this request.</p>
      </div>

      <!-- Dynamic Technologies Section -->
      <div class="grid grid-cols-1 gap-5 mt-6 sm:grid-cols-3">
        <div
          v-for="category in props.technologies"
          :key="category.id"
          class="p-4 bg-white rounded-lg shadow"
        >
          <h3 class="mb-2 text-lg font-medium leading-6 text-gray-900">
            {{ category.category }}
          </h3>

          <!-- Subcategories and Items -->
          <div
            v-for="subcategory in category.subcategories"
            :key="subcategory.id"
            class="mt-4"
          >
            <h4 class="font-semibold text-gray-700 text-md">
              {{ subcategory.subcategory }}
            </h4>
            <ul class="mt-2 list-disc list-inside">
              <li
                v-for="item in subcategory.items"
                :key="item"
                class="text-sm text-gray-500"
              >
                {{ item }}
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Competitors Section -->
      <div
        v-if="props.competitors && props.competitors.length"
        class="mt-10 overflow-hidden bg-white shadow sm:rounded-lg"
      >
        <div class="px-4 py-5 sm:px-6">
          <h3 class="text-lg font-medium leading-6 text-gray-900">
            Competitors
          </h3>
          <p class="max-w-2xl mt-1 text-sm text-gray-500">
            Competitors information.
          </p>
        </div>
        <div class="border-t border-gray-200">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th
                  scope="col"
                  class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                >
                  Competitor Domain
                </th>
                <th
                  scope="col"
                  class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                >
                  Avg. Position
                </th>
                <th
                  scope="col"
                  class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                >
                  Intersections
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="competitor in props.competitors" :key="competitor.id">
                <td
                  class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap"
                >
                  {{ competitor.competitor_domain }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                  {{ competitor.avg_position }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                  {{ competitor.intersections }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div v-else>
        <p>No competitors data available.</p>
      </div>
    </div>
  </AppLayout>
</template>
