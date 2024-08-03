<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
  links: Object,
});

const getComponentType = (link) => {
  return link.url ? Link : "span";
};
</script>

<template>
  <div>
    <component
      :is="getComponentType(link)"
      v-for="link in links"
      :key="link.label"
      :href="link.url"
      v-html="link.label"
      class="px-1"
      :class="{ 'text-gray-500': !link.url, 'font-bold': link.active }"
    />
  </div>
</template>
