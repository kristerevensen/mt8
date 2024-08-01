<script setup>
import { ref, computed, watchEffect } from "vue";
import { usePage } from "@inertiajs/vue3";

// Get the Inertia page object
const page = usePage();

// Reactive references for the banner's visibility, style, and message
const show = ref(false);
const style = ref("success");
const message = ref("");

// Computed property to determine which message to show
const flashMessage = computed(() => {
  // Determine the priority of messages: error > success > message
  if (page.props.flash?.error) {
    style.value = "danger";
    return page.props.flash.error;
  } else if (page.props.flash?.success) {
    style.value = "success";
    return page.props.flash.success;
  } else if (page.props.flash?.message) {
    style.value = "info";
    return page.props.flash.message;
  }
  return "";
});

// Watch for changes in the flash message and update the visibility and content
watchEffect(() => {
  message.value = flashMessage.value;
  show.value = !!message.value; // Convert message presence to boolean
});
</script>

<template>
  <div
    v-if="show"
    :class="{
      'bg-indigo-500': style == 'success',
      'bg-red-700': style == 'danger',
      'bg-yellow-500': style == 'info',
    }"
  >
    <div class="max-w-screen-xl px-3 py-2 mx-auto sm:px-6 lg:px-8">
      <div class="flex flex-wrap items-center justify-between">
        <div class="flex items-center flex-1 w-0 min-w-0">
          <span
            :class="{
              'flex p-2 rounded-lg bg-indigo-600': style == 'success',
              'flex p-2 rounded-lg bg-red-600': style == 'danger',
              'flex p-2 rounded-lg bg-yellow-600': style == 'info',
            }"
          >
            <!-- Success Icon -->
            <svg
              v-if="style == 'success'"
              class="w-5 h-5 text-white"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>

            <!-- Error Icon -->
            <svg
              v-if="style == 'danger'"
              class="w-5 h-5 text-white"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"
              />
            </svg>

            <!-- Info Icon -->
            <svg
              v-if="style == 'info'"
              class="w-5 h-5 text-white"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M13.875 9.75h-3.75M12 11.25v3.75m0-8.25v.75m0 8.25a9 9 0 110-18 9 9 0 010 18z"
              />
            </svg>
          </span>

          <p class="text-sm font-medium text-white truncate ms-3">
            {{ message }}
          </p>
        </div>

        <div class="shrink-0 sm:ms-3">
          <button
            type="button"
            class="flex p-2 transition rounded-md -me-1 focus:outline-none sm:-me-2"
            :class="{
              'hover:bg-indigo-600 focus:bg-indigo-600': style == 'success',
              'hover:bg-red-600 focus:bg-red-600': style == 'danger',
              'hover:bg-yellow-600 focus:bg-yellow-600': style == 'info',
            }"
            aria-label="Dismiss"
            @click.prevent="show = false"
          >
            <svg
              class="w-5 h-5 text-white"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
