<script setup>
import { defineProps } from "vue";

const props = defineProps({
  links: Array,
});
</script>

<template>
  <div class="mt-4">
    <nav
      role="navigation"
      aria-label="Pagination Navigation"
      class="flex justify-center"
    >
      <span v-for="link in links" :key="link.url" class="px-2">
        <a
          v-if="link.url"
          :href="link.url"
          class="text-indigo-600 hover:text-indigo-900"
          :class="{ 'font-bold': link.active }"
          v-html="link.label"
        ></a>
        <span v-else v-html="link.label" class="text-gray-500"></span>
      </span>
    </nav>
  </div>
</template>
