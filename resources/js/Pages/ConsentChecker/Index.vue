<!-- resources/js/Pages/ConsentChecker/Index.vue -->
<template>
  <AppLayout title="Consent Checker">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Consent Checker
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="p-6 overflow-hidden bg-white shadow-xl sm:rounded-lg">
          <!-- Progress Bar -->
          <div v-if="processing" class="mb-6">
            <div class="relative pt-1">
              <div class="flex items-center justify-between mb-2">
                <div>
                  <span
                    class="inline-block px-2 py-1 text-xs font-semibold text-indigo-600 uppercase bg-indigo-200 rounded-full"
                  >
                    Analyserer nettsted
                  </span>
                </div>
                <div class="text-right">
                  <span
                    class="inline-block text-xs font-semibold text-indigo-600"
                  >
                    {{ currentStep }}/{{ totalSteps }}
                  </span>
                </div>
              </div>
              <div
                class="flex h-2 mb-4 overflow-hidden text-xs bg-indigo-200 rounded"
              >
                <div
                  :style="{ width: `${(currentStep / totalSteps) * 100}%` }"
                  class="flex flex-col justify-center text-center text-white transition-all duration-500 bg-indigo-600 shadow-none whitespace-nowrap"
                ></div>
              </div>
              <div class="text-xs text-center text-gray-600">
                {{ currentStepText }}
              </div>
            </div>
          </div>

          <!-- Skjema -->
          <form @submit.prevent="checkUrl" class="space-y-4">
            <div>
              <label for="url" class="block text-sm font-medium text-gray-700">
                Nettadresse
              </label>
              <div class="flex mt-1 rounded-md shadow-sm">
                <input
                  id="url"
                  type="url"
                  v-model="form.url"
                  :disabled="processing"
                  required
                  class="flex-1 block w-full border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                  placeholder="https://example.com"
                />
                <button
                  type="submit"
                  :disabled="processing || !form.url"
                  class="inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-white transition-all duration-200 bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <template v-if="processing">
                    <svg
                      class="w-5 h-5 mr-3 -ml-1 text-white animate-spin"
                      xmlns="http://www.w3.org/2000/svg"
                      fill="none"
                      viewBox="0 0 24 24"
                    >
                      <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                      ></circle>
                      <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                      ></path>
                    </svg>
                    Analyserer...
                  </template>
                  <template v-else>
                    <span>Sjekk nettsted</span>
                  </template>
                </button>
              </div>
            </div>
          </form>

          <!-- Feilmelding -->
          <div v-if="form.errors.url" class="p-4 mt-4 rounded-md bg-red-50">
            <div class="flex">
              <div class="flex-shrink-0">
                <XCircleIcon class="w-5 h-5 text-red-400" aria-hidden="true" />
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Feil</h3>
                <div class="mt-2 text-sm text-red-700">
                  <p>{{ form.errors.url }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Resultat -->
          <div v-if="result" class="mt-6">
            <div
              :class="[
                'rounded-md p-4',
                result.compliant ? 'bg-green-50' : 'bg-red-50',
              ]"
            >
              <div class="flex">
                <div class="flex-shrink-0">
                  <CheckCircleIcon
                    v-if="result.compliant"
                    class="w-5 h-5 text-green-400"
                    aria-hidden="true"
                  />
                  <XCircleIcon
                    v-else
                    class="w-5 h-5 text-red-400"
                    aria-hidden="true"
                  />
                </div>
                <div class="ml-3">
                  <h3
                    :class="[
                      'text-sm font-medium',
                      result.compliant ? 'text-green-800' : 'text-red-800',
                    ]"
                  >
                    {{
                      result.compliant
                        ? "Nettstedet ser ut til å være kompliant"
                        : "Nettstedet kan ha mangler"
                    }}
                  </h3>
                  <div class="mt-4">
                    <div class="space-y-3">
                      <div
                        v-for="(value, key) in result.details"
                        :key="key"
                        class="flex items-center"
                      >
                        <CheckCircleIcon
                          v-if="value"
                          class="w-5 h-5 mr-2 text-green-400"
                        />
                        <XCircleIcon v-else class="w-5 h-5 mr-2 text-red-400" />
                        <span
                          class="text-sm"
                          :class="value ? 'text-green-700' : 'text-red-700'"
                        >
                          {{ getDetailLabel(key) }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<!-- resources/js/Pages/ConsentChecker/Index.vue -->
<script setup>
import { ref, computed } from "vue"; // La til computed her
import { useForm } from "@inertiajs/vue3";
import { CheckCircleIcon, XCircleIcon } from "@heroicons/vue/24/solid";
import AppLayout from "@/Layouts/AppLayout.vue";

const result = ref(null);
const processing = ref(false);
const currentStep = ref(0);
const totalSteps = 4;
const currentStepText = ref("");

const form = useForm({
  url: "",
});

const detailLabels = {
  https: "HTTPS-sikkerhet",
  cookiePolicy: "Cookie-policy",
  consentManager: "Consent Manager implementert",
};

const steps = [
  "Validerer URL",
  "Sjekker HTTPS",
  "Analyserer cookie-policy",
  "Undersøker consent manager",
];

// Computed property for å sjekke om feilen er en tilkoblingsfeil
const isConnectionError = computed(() => {
  return form.errors.url?.toLowerCase().includes("kunne ikke analysere");
});

const getDetailLabel = (key) => detailLabels[key] || key;

const resetProgress = () => {
  currentStep.value = 0;
  currentStepText.value = "";
  processing.value = false;
};

const simulateProgress = async () => {
  for (let i = 0; i < steps.length; i++) {
    currentStep.value = i + 1;
    currentStepText.value = steps[i];
    await new Promise((resolve) => setTimeout(resolve, 800));
  }
};

const checkUrl = async () => {
  if (processing.value) return;

  processing.value = true;
  currentStep.value = 1;
  currentStepText.value = steps[0];
  result.value = null;
  form.clearErrors();

  try {
    await simulateProgress();
    const response = await axios.post(route("consent-checker.analyze"), form);
    result.value = response.data;
  } catch (error) {
    const errorMessage =
      error.response?.data?.error ||
      "Kunne ikke analysere nettstedet. Vennligst sjekk URL-en og prøv igjen.";
    form.setError("url", errorMessage);
  } finally {
    resetProgress();
  }
};
</script>
