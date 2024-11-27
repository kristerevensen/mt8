<!-- resources/js/Pages/MarketingHealthChecker/Index.vue -->
<template>
  <AppLayout title="Marketing Health Checker">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Marketing Health Checker
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Intro Section -->
        <div class="p-6 mb-6 overflow-hidden bg-white shadow-xl sm:rounded-lg">
          <h3 class="mb-4 text-lg font-medium text-gray-900">
            Analysér din nettsides markedsføringseffektivitet
          </h3>
          <p class="text-gray-600">
            Verktøyet sjekker trust signals, konverteringsoptimalisering,
            lokalisering, teknisk SEO, personvern, mobilopplevelse og
            analytics-oppsett.
          </p>
        </div>

        <!-- Analysis Form -->
        <div class="p-6 bg-white rounded-lg shadow">
          <div class="mb-4">
            <label
              for="url"
              class="block mb-2 text-sm font-medium text-gray-700"
            >
              Nettadresse å analysere
            </label>
            <div class="flex space-x-4">
              <input
                id="url"
                type="url"
                v-model="form.url"
                required
                :disabled="processing"
                class="flex-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                placeholder="https://www.example.no"
                @keyup.enter="analyze"
              />
              <button
                @click="analyze"
                :disabled="processing || !form.url"
                class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="!processing">Start analyse</span>
                <div v-else class="flex items-center">
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
                </div>
              </button>
            </div>
          </div>

          <!-- Analysis Results -->
          <div v-if="results" class="mt-8 space-y-6">
            <!-- Overall Score -->
            <div class="p-6 rounded-lg bg-gray-50">
              <div class="text-center">
                <h3 class="text-lg font-medium text-gray-900">Total Score</h3>
                <div class="mt-2 text-5xl font-bold text-indigo-600">
                  {{ results.overall_health }}%
                </div>
              </div>
            </div>

            <!-- Category Scores -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
              <div
                v-for="(category, key) in categoryResults"
                :key="key"
                class="p-6 bg-white rounded-lg shadow"
              >
                <div class="flex items-center justify-between">
                  <h4 class="text-lg font-medium text-gray-900">
                    {{ formatCategoryName(key) }}
                  </h4>
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="getScoreClass(category.score)"
                  >
                    {{ category.score }}%
                  </span>
                </div>
                <div class="mt-4 space-y-2">
                  <div
                    v-for="(value, check) in category.checks"
                    :key="check"
                    class="flex items-center text-sm"
                  >
                    <CheckCircleIcon
                      v-if="value"
                      class="w-5 h-5 mr-2 text-green-500"
                    />
                    <XCircleIcon v-else class="w-5 h-5 mr-2 text-red-500" />
                    <span :class="value ? 'text-gray-900' : 'text-gray-500'">
                      {{ formatCheckName(check) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Recommendations -->
            <div class="p-6 bg-white rounded-lg shadow">
              <h3 class="mb-4 text-lg font-medium text-gray-900">
                Anbefalinger
              </h3>
              <div class="space-y-4">
                <div
                  v-for="(recs, category) in results.recommendations"
                  :key="category"
                >
                  <h4 class="mb-2 text-sm font-medium text-gray-700">
                    {{ formatCategoryName(category) }}
                  </h4>
                  <ul class="pl-5 space-y-2 list-disc">
                    <li
                      v-for="(rec, index) in recs"
                      :key="index"
                      class="text-sm text-gray-600"
                    >
                      {{ rec }}
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- Error Display -->
          <!-- Error Message -->
          <div v-if="error" class="mt-4">
            <div class="p-4 rounded-md bg-red-50">
              <div class="flex">
                <div class="flex-shrink-0">
                  <XCircleIcon
                    class="w-5 h-5 text-red-400"
                    aria-hidden="true"
                  />
                </div>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-red-800">
                    Feil ved analyse
                  </h3>
                  <div class="mt-2 text-sm text-red-700">
                    <p>{{ error }}</p>
                    <div v-if="errorDetails" class="mt-2">
                      <p class="font-medium">Mulige løsninger:</p>
                      <ul class="pl-5 mt-1 space-y-1 list-disc">
                        <li v-if="error.includes('URL')">
                          Sjekk at URL-en er korrekt skrevet og inneholder
                          http:// eller https://
                        </li>
                        <li v-if="error.includes('tilgjengelig')">
                          Verifiser at nettsiden er oppe og tilgjengelig i en
                          nettleser
                        </li>
                        <li v-if="error.includes('tilgang')">
                          Sjekk at siden ikke krever innlogging eller er
                          beskyttet
                        </li>
                        <li v-if="error.includes('HTML')">
                          Kontroller at nettsiden er en vanlig nettside og ikke
                          en PDF eller annen filtype
                        </li>
                        <li>Prøv å laste siden på nytt om noen minutter</li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Checks -->
        <div v-if="recentChecks.length > 0" class="mt-8">
          <h3 class="mb-4 text-lg font-medium text-gray-900">
            Nylige analyser
          </h3>
          <div class="overflow-hidden bg-white shadow sm:rounded-lg">
            <ul class="divide-y divide-gray-200">
              <li
                v-for="check in recentChecks"
                :key="check.id"
                class="px-4 py-4 sm:px-6"
              >
                <div class="flex items-center justify-between">
                  <div class="text-sm font-medium text-indigo-600 truncate">
                    {{ check.url }}
                  </div>
                  <div class="flex flex-shrink-0 ml-2">
                    <span
                      class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full"
                      :class="getScoreClass(check.overall_health)"
                    >
                      Score: {{ check.overall_health }}%
                    </span>
                  </div>
                </div>
                <div class="mt-2 sm:flex sm:justify-between">
                  <div class="sm:flex">
                    <p class="flex items-center text-sm text-gray-500">
                      Analysert {{ check.formatted_date }}
                    </p>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { CheckCircleIcon, XCircleIcon } from "@heroicons/vue/24/solid";

const props = defineProps({
  recentChecks: {
    type: Array,
    default: () => [],
  },
});

const processing = ref(false);
const results = ref(null);
const error = ref(null);

const form = useForm({
  url: "",
});

const categoryResults = computed(() => {
  if (!results.value) return {};
  return {
    trust_signals: {
      score: calculateScore(results.value.trust_signals),
      checks: results.value.trust_signals,
    },
    conversion_elements: {
      score: calculateScore(results.value.conversion_elements),
      checks: results.value.conversion_elements,
    },
    localization: {
      score: calculateScore(results.value.localization),
      checks: results.value.localization,
    },
    technical_seo: {
      score: calculateScore(results.value.technical_seo),
      checks: results.value.technical_seo,
    },
    privacy_compliance: {
      score: calculateScore(results.value.privacy_compliance),
      checks: results.value.privacy_compliance,
    },
    mobile_experience: {
      score: calculateScore(results.value.mobile_experience),
      checks: results.value.mobile_experience,
    },
    analytics_setup: {
      score: calculateScore(results.value.analytics_setup),
      checks: results.value.analytics_setup,
    },
  };
});

const calculateScore = (category) => {
  if (!category || typeof category !== "object") return 0;

  const checks = Object.entries(category).filter(
    ([key]) => !["score", "details"].includes(key)
  );

  if (checks.length === 0) return 0;

  const passedChecks = checks.filter(([, value]) => value === true).length;
  return Math.round((passedChecks / checks.length) * 100);
};

const formatCategoryName = (name) => {
  return name
    .split("_")
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(" ");
};

const formatCheckName = (name) => {
  return name
    .replace(/_/g, " ")
    .replace(/has/g, "")
    .trim()
    .split(" ")
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(" ");
};

const getScoreClass = (score) => {
  if (score >= 80) return "bg-green-100 text-green-800";
  if (score >= 60) return "bg-yellow-100 text-yellow-800";
  return "bg-red-100 text-red-800";
};

const checkUrl = async () => {
  if (processing.value) return;

  processing.value = true;
  results.value = null;
  error.value = null;

  try {
    const response = await axios.post(route("marketing-health.analyze"), form);
    results.value = response.data;
  } catch (e) {
    error.value = e.response?.data?.error || "En feil oppstod under analysen";
  } finally {
    processing.value = false;
  }
};
</script>
