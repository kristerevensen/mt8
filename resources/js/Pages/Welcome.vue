<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
  laravelVersion: {
    type: String,
    required: true,
  },
  phpVersion: {
    type: String,
    required: true,
  },
});
</script>

<template>
  <Head title="Measuretank" />
  
  <div class="min-h-screen flex flex-col bg-white">
    <!-- Navigation Bar -->
    <nav class="w-full bg-white border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <div class="text-xl font-semibold text-gray-900">
              Measuretank
            </div>
          </div>

          <div v-if="canLogin" class="flex items-center gap-6">
            <template v-if="$page.props.auth.user">
              <Link
                :href="route('dashboard')"
                class="px-4 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
              >
                Dashboard
              </Link>
            </template>
            <template v-else>
              <Link
                :href="route('login')"
                class="text-sm font-medium text-gray-700 hover:text-gray-900"
              >
                Logg inn
              </Link>

              <Link
                v-if="canRegister"
                :href="route('register')"
                class="px-4 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
              >
                Kom i gang
              </Link>
            </template>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1">
      <!-- Hero Section -->
      <div class="relative isolate pt-14">
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] bg-[size:16px_16px]"></div>
        <div class="mx-auto max-w-7xl px-6 py-16 sm:py-24 lg:px-8">
          <div class="mx-auto max-w-2xl text-center">
            <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl">
            Analyse og optimalisering på ett sted..
            </h1>
            <p class="mt-6 text-lg leading-8 text-gray-600">
              Få full kontroll på din digitale virksomhet med Measuretank.
            </p>
            <div class="mt-10 flex items-center justify-center gap-x-6">
              <Link
                v-if="canRegister"
                :href="route('register')"
                class="rounded-lg bg-gray-900 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
              >
              Measuretank er foreløpig 100% gratis - Registrer deg <span aria-hidden="true">→</span> 
              </Link>
              
            </div>
          </div>
        </div>
      </div>

   
    </main>


  </div>
</template>