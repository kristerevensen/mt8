
<script setup>
import { ref, computed } from "vue";
import { Link, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

import Pagination from "@/Components/Pagination.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";

// Define the goals prop
const props = defineProps({
  goals: Object, // Expecting a paginated object
});

// Initialize form data for Inertia.js
const form = useForm({});

// Sorting state
const sortColumn = ref("goal_name");
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

// Computed property to sort goals
const sortedGoals = computed(() => {
  if (!props.goals || !props.goals.data) return [];
  return [...props.goals.data].sort((a, b) => {
    const valueA = a[sortColumn.value] || "";
    const valueB = b[sortColumn.value] || "";

    if (sortDirection.value === "asc") {
      return valueA > valueB ? 1 : valueA < valueB ? -1 : 0;
    } else {
      return valueA < valueB ? 1 : valueA > valueB ? -1 : 0;
    }
  });
});

// Function to delete a goal
const deleteGoal = (goal_id) => {
  if (confirm("Are you sure you want to delete this goal?")) {
    form.delete(route("goals.destroy", goal_id)).then(() => {
      console.log("Goal deleted");
    });
  }
};

const breadcrumbs = [{ name: "All goals", current: true }];
</script>


<template>
  <Head title="Goals" />
  <AppLayout title="Goals">
    <template #header>
      <div class="flex justify-between">
        <div>
          <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <Breadcrumbs :pages="breadcrumbs" />
          </h2>
        </div>
        <div>
          <Link
            :href="route('goals.create')"
            class="px-4 py-3 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-500"
          >
            + New Goal
          </Link>
        </div>
      </div>
    </template>
    <div class="py-5 mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="flow-root mt-8">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div
            class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8"
          >
            <div
              class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg"
            >
              <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                  <tr>
                    <th
                      @click="toggleSort('goal_name')"
                      scope="col"
                      class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 cursor-pointer sm:pl-6"
                    >
                      Goal Name
                      <span v-if="sortColumn === 'goal_name'">
                        {{ sortDirection === "asc" ? "↑" : "↓" }}
                      </span>
                    </th>
                    <th
                      @click="toggleSort('goal_type')"
                      scope="col"
                      class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 cursor-pointer"
                    >
                      Goal Type
                      <span v-if="sortColumn === 'goal_type'">
                        {{ sortDirection === "asc" ? "↑" : "↓" }}
                      </span>
                    </th>

                    <th
                      @click="toggleSort('goal_value')"
                      scope="col"
                      class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 cursor-pointer"
                    >
                      Goal Value
                      <span v-if="sortColumn === 'goal_value'">
                        {{ sortDirection === "asc" ? "↑" : "↓" }}
                      </span>
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 cursor-pointer"
                    >
                      Goal ID
                    </th>
                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                      <span class="sr-only">Actions</span>
                    </th>
                  </tr>
                </thead>
                <tbody
                  v-if="sortedGoals.length > 0"
                  class="bg-white divide-y divide-gray-200"
                >
                  <tr v-for="goal in sortedGoals" :key="goal.id">
                    <td
                      class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 whitespace-nowrap sm:pl-6"
                    >
                      {{ goal.goal_name }}
                    </td>
                    <td
                      class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap"
                    >
                      {{ goal.goal_type }}
                    </td>
                    <td
                      class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap"
                    >
                      {{
                        typeof goal.goal_value === "number" &&
                        goal.goal_value !== null
                          ? goal.goal_value.toFixed(2)
                          : "N/A"
                      }}
                    </td>
                    <td>
                      <span
                        class="px-3 py-1 text-sm font-semibold text-gray-900 bg-gray-100 rounded-full"
                      >
                        {{ goal.goal_uuid }}
                      </span>
                    </td>

                    <td
                      class="relative py-4 pl-3 pr-4 text-sm font-medium text-right whitespace-nowrap sm:pr-6"
                    >
                      <Link
                        :href="route('goals.show', goal.id)"
                        class="ml-2 text-indigo-600 hover:text-indigo-900"
                      >
                        View
                      </Link>
                      <Link
                        :href="route('goals.edit', goal.id)"
                        class="ml-2 text-indigo-600 hover:text-indigo-900"
                      >
                        Edit
                      </Link>
                      <button
                        @click="deleteGoal(goal.id)"
                        class="ml-2 text-red-600 hover:text-red-900"
                      >
                        Delete
                      </button>
                    </td>
                  </tr>
                </tbody>
                <tbody v-else>
                  <tr>
                    <td colspan="5" class="py-4 text-center text-gray-500">
                      No goals found.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <!-- Pagination Component -->
        <Pagination :links="goals.links" />
      </div>
    </div>
  </AppLayout>
</template>
