<template>
    <AppLayout title="Search Console">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Search Console Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="!connected" class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="text-center">
                        <h3 class="text-lg font-medium text-gray-900">Connect to Google Search Console</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Connect your Google account to access Search Console data
                        </p>
                        <div class="mt-6">
                            <Link :href="route('search-console.connect')" 
                                  class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                                Connect with Google
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else>
                    <!-- Existing sites list code -->
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    connected: Boolean,
    sites: Array
})
</script>