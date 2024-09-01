<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { computed, defineProps, ref } from "vue";

const props = defineProps({
  project: Object,
  project_code: String,
});

const selectedCountry = ref("");
const selectedLanguage = ref("");

let project = props.project;

const trackingScript = computed(() => {
  return (
    `<!-- MeasureTank Tracking Snippet -->
<script>
  (function() {
    var mt = document.createElement('script');
    mt.type = 'text/javascript';
    mt.async = true;
    mt.src = 'https://tracking.measuretank.com/tracking.js';
    mt.setAttribute('data-project-code', '${props.project.project_code}');
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(mt, s);
  })();
</` + `script>`
  );
});

const conversionScript = computed(() => {
  return (
    `<!-- MeasureTank Conversion Tracking Snippet -->
<script>
  (function() {
    var mt = document.createElement('script');
    mt.type = 'text/javascript';
    mt.async = true;
    mt.src = 'https://tracking.measuretank.com/conversion.js'; // Bruker conversion.js som nevnt
    mt.setAttribute('data-project-code', '${props.project.project_code}');
    mt.setAttribute('data-conversion-type', 'purchase');  // Sett inn konverteringstype
    mt.setAttribute('data-conversion-value', '199.99');   // Sett inn konverteringsverdi
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(mt, s);
  })();
</` + `script>`
  );
});

const copyTrackingCode = async () => {
  try {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      // Prøv å bruke Clipboard API hvis tilgjengelig
      await navigator.clipboard.writeText(trackingScript.value);
      alert("Tracking code copied to clipboard");
    } else {
      // Fallback-metode for eldre nettlesere
      const textarea = document.createElement("textarea");
      textarea.value = trackingScript.value;
      document.body.appendChild(textarea);
      textarea.select();
      document.execCommand("copy");
      document.body.removeChild(textarea);
      alert("Tracking code copied to clipboard ");
    }
  } catch (error) {
    console.error("Failed to copy: ", error);
  }
};

const copyConversionCode = async () => {
  try {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      // Prøv å bruke Clipboard API hvis tilgjengelig
      await navigator.clipboard.writeText(conversionScript.value);
      alert("Conversion code copied to clipboard");
    } else {
      // Fallback-metode for eldre nettlesere
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

const deleteProject = (project_code) => {
  if (confirm("Are you sure you want to delete this project?")) {
    form.post(route("project.destroy", project_code));
  }
};
</script>

<template>
  <AppLayout title="Edit Project">
    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px lg:px-0">
        <div class="sm:px-6 lg:px-8">
          <div class="relative pb-5 mb-10 border-b border-gray-200 sm:pb-0">
            <div class="md:flex md:items-center md:justify-between">
              <h2
                class="mb-5 text-xl font-semibold leading-tight text-gray-800"
              >
                Project Settings
              </h2>
            </div>
          </div>

          <div class="space-y-10 divide-y divide-gray-900/10">
            <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
              <div class="px-4 sm:px-0">
                <h2 class="text-base font-semibold leading-7 text-gray-900">
                  Tracking Information
                </h2>
                <p class="mt-1 text-sm leading-6 text-gray-600">
                  Make sure the domain given, is the correct one you collect
                  data for.
                </p>
              </div>

              <div
                class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2"
              >
                <div class="px-4 py-6 sm:p-8">
                  <div
                    class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6"
                  >
                    <div class="sm:col-span-4">
                      <label
                        for="website"
                        class="block text-sm font-medium leading-6 text-gray-900"
                        >Domain</label
                      >
                      <div class="mt-2">
                        <div
                          class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md"
                        >
                          <span
                            class="flex items-center pl-3 text-gray-500 select-none sm:text-sm"
                            >http://</span
                          >
                          <input
                            type="text"
                            v-model="project.project_domain"
                            name="domain"
                            id="domain"
                            disabled
                            class="block flex-1 border-0 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6"
                          />
                        </div>
                      </div>
                    </div>

                    <div class="col-span-full">
                      <label
                        for="tracking"
                        class="block text-sm font-medium leading-6 text-gray-900"
                        >Tracking Code</label
                      >
                      <div class="mt-2">
                        <textarea
                          id="tracking"
                          v-model="trackingScript"
                          name="tracking"
                          rows="14"
                          class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                          readonly
                        >
                        </textarea>
                      </div>
                      <p class="mt-3 text-sm leading-6 text-gray-600">
                        Must be put in head of HTML document.
                      </p>
                    </div>
                  </div>
                </div>
                <div
                  class="flex items-center justify-end px-4 py-4 border-t gap-x-6 border-gray-900/10 sm:px-8"
                >
                  <button
                    type="button"
                    @click="copyTrackingCode"
                    class="px-3 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                  >
                    Copy Tracking Snippet
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-10 space-y-10 divide-y divide-gray-900/10">
            <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
              <div class="px-4 sm:px-0">
                <h2 class="text-base font-semibold leading-7 text-gray-900">
                  Conversion Tracking Information
                </h2>
                <p class="mt-1 text-sm leading-6 text-gray-600">
                  Make sure the domain given, is the correct one you collect
                  data for.
                </p>
              </div>

              <div
                class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2"
              >
                <div class="px-4 py-6 sm:p-8">
                  <div
                    class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6"
                  >
                    <div class="sm:col-span-4">
                      <label
                        for="website"
                        class="block text-sm font-medium leading-6 text-gray-900"
                        >Domain</label
                      >
                      <div class="mt-2">
                        <div
                          class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md"
                        >
                          <span
                            class="flex items-center pl-3 text-gray-500 select-none sm:text-sm"
                            >http://</span
                          >
                          <input
                            type="text"
                            v-model="project.project_domain"
                            name="domain"
                            id="domain"
                            disabled
                            class="block flex-1 border-0 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6"
                          />
                        </div>
                      </div>
                    </div>

                    <div class="col-span-full">
                      <label
                        for="tracking"
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
