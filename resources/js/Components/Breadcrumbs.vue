<!-- Breadcrumbs.vue -->
<template>
  <nav class="flex" aria-label="Breadcrumb">
    <ol role="list" class="flex px-6 space-x-4 bg-white rounded-md shadow">
      <li class="flex">
        <div class="flex items-center">
          <a href="/" class="text-gray-400 hover:text-gray-500">
            <HomeIcon class="flex-shrink-0 w-5 h-5" aria-hidden="true" />
            <span class="sr-only">Home</span>
          </a>
        </div>
      </li>
      <li v-for="page in pages" :key="page.name" class="flex">
        <div class="flex items-center">
          <svg
            class="flex-shrink-0 w-6 h-full text-gray-200"
            viewBox="0 0 24 44"
            preserveAspectRatio="none"
            fill="currentColor"
            aria-hidden="true"
          >
            <path d="M.293 0l22 22-22 22h1.414l22-22-22-22H.293z" />
          </svg>
          <a
            :href="page.href"
            class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700"
            :aria-current="page.current ? 'page' : undefined"
          >
            {{ page.name }}
          </a>
        </div>
      </li>
    </ol>
  </nav>
</template>

<script setup>
import { HomeIcon } from "@heroicons/vue/20/solid";
import { defineProps } from "vue";

// Define the pages prop and mark it as required
const props = defineProps({
  pages: {
    type: Array,
    required: true, // Mark as required
  },
});
</script>
