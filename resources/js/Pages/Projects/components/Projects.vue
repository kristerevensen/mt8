<template>
  <div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
      <div class="sm:flex-auto">
        <h1 class="text-base font-semibold leading-6 text-gray-900">
          Projects
        </h1>
        <p class="mt-2 text-sm text-gray-700">A list of all the projects</p>
      </div>
      <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
        <Link
          :href="route('projects.create')"
          class="block px-3 py-2 text-sm font-semibold text-center text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
        >
          Add Project</Link
        >
      </div>
    </div>
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
                    Project
                  </th>
                  <th
                    scope="col"
                    class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                  >
                    Domain
                  </th>
                  <th
                    scope="col"
                    class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                  >
                    Team
                  </th>

                  <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                    <span class="sr-only">Actions</span>
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(item, index) in $page.props.projects" :key="index">
                  <td
                    class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 whitespace-nowrap sm:pl-6"
                  >
                    {{ item.project_name }}
                  </td>
                  <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                    {{ item.project_domain }}
                  </td>
                  <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                    {{ item.team_name ? item.team_name : "No team" }}
                  </td>

                  <td
                    class="relative py-4 pl-3 pr-4 text-sm font-medium text-right whitespace-nowrap sm:pr-6"
                  >
                    <Link
                      :href="route('projects.settings', item.project_code)"
                      class="text-indigo-600 hover:text-indigo-900"
                      >View<span class="sr-only">
                        {{ item.project_code }}</span
                      ></Link
                    >
                    <Link
                      :href="route('projects.edit', item.project_code)"
                      class="ml-4 text-indigo-600 hover:text-indigo-900"
                      >Edit<span class="sr-only">
                        {{ item.project_name }}</span
                      ></Link
                    >
                    <button
                      @click="deleteProject(item.project_code)"
                      class="ml-4 text-red-600 hover:text-red-900"
                    >
                      Delete<span class="sr-only"
                        >, {{ item.project_name }}</span
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
<script setup>
import { Link } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";

defineProps({
  projects: Array,
});

// Initialiserer formdata ved hjelp av useForm hook fra Inertia
const form = useForm({
  project_code: "",
});

const deleteProject = (project_code) => {
  if (confirm("Are you sure you want to delete this project?")) {
    const del = form.delete(route("projects.destroy", project_code));
    del.then(() => {
      console.log("Project deleted");
    });
  }
};
</script>
