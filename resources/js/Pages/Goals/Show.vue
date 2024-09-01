<script setup>
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { computed, defineProps, ref } from "vue";

const props = defineProps({
  goal: Object,
  project_code: String,
});

const conversionScript = computed(() => {
  return (
    `<!-- MeasureTank Conversion Tracking Snippet -->
<script>
  (function() {
    var mt = document.createElement('script');
    mt.type = 'text/javascript';
    mt.async = true;
    mt.src = 'https://tracking.measuretank.com/conversion.js';
    mt.setAttribute('data-conversion-uuid', '${props.goal.goal_uuid}');
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(mt, s);
  })();
</` + `script>`
  );
});

const copyConversionCode = async () => {
  try {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      await navigator.clipboard.writeText(conversionScript.value);
      alert("Conversion code copied to clipboard");
    } else {
      const textarea = document.createElement("textarea");
      textarea.value = conversionScript.value;
      document.body.appendChild(textarea);
      textarea.select();
      document.execCommand("copy");
      document.body.removeChild(textarea);
      alert("Conversion code copied to clipboard ");
    }
  } catch (error) {
    console.error("Failed to copy: ", error);
  }
};
const breadcrumbs = [
  { name: "All goals", href: "/goals", current: true },
  { name: "Conversion Script", current: true },
];
</script>

<template>
  <AppLayout title="Conversion Script">
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
    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px lg:px-0">
        <div class="sm:px-6 lg:px-8">
          <div class="space-y-10 divide-y divide-gray-900/10">
            <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
              <div class="px-4 sm:px-0">
                <h2 class="text-base font-semibold leading-7 text-gray-900">
                  Conversion Script
                </h2>
                <p class="mt-1 text-sm leading-6 text-gray-600">
                  This script should be placed after the tracking script to
                  capture conversions for the selected goal.
                </p>
              </div>

              <div
                class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2"
              >
                <div class="px-4 py-6 sm:p-8">
                  <div
                    class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6"
                  >
                    <div class="col-span-full">
                      <label
                        for="conversion"
                        class="block text-sm font-medium leading-6 text-gray-900"
                        >Conversion Code</label
                      >
                      <div class="mt-2">
                        <textarea
                          id="conversion"
                          v-model="conversionScript"
                          name="conversion"
                          rows="14"
                          class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                          readonly
                        >
                        </textarea>
                      </div>
                      <p class="mt-3 text-sm leading-6 text-gray-600">
                        Should be fired after the tracking script.
                      </p>
                    </div>
                  </div>
                </div>
                <div
                  class="flex items-center justify-end px-4 py-4 border-t gap-x-6 border-gray-900/10 sm:px-8"
                >
                  <button
                    type="button"
                    @click="copyConversionCode"
                    class="px-3 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                  >
                    Copy Conversion Snippet
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
