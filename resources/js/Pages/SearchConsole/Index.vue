<template>
    <AppLayout title="Search Console Integration">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Search Console Integration
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <form @submit.prevent="startIntegration">
                        <div class="space-y-6">

                            <div class="space-y-4">
                                <h3 class="text-lg font-medium text-gray-900">Data Configuration</h3>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Historical Data Range</label>
                                    <div class="mt-1">
                                        <select v-model="form.months" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                            <option value="1">Last Month</option>
                                            <option value="3">Last 3 Months</option>
                                            <option value="6">Last 6 Months</option>
                                            <option value="12">Last 12 Months</option>
                                            <option value="16">Last 16 Months</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Data to Include</label>
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <input v-model="form.includeQueries" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                            <label class="ml-2 text-sm text-gray-700">Search Queries</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input v-model="form.includePages" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                            <label class="ml-2 text-sm text-gray-700">Pages</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input v-model="form.includeCountries" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                            <label class="ml-2 text-sm text-gray-700">Countries</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input v-model="form.includeDevices" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                            <label class="ml-2 text-sm text-gray-700">Devices</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="!canSubmit"
                                    @click.prevent="startIntegration"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                                >
                                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                    </svg>
                                    Connect Google Search Console
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const form = useForm({
    months: '3',
    includeQueries: true,
    includePages: true,
    includeCountries: false,
    includeDevices: false
})

const canSubmit = computed(() => {
    return form.includeQueries || form.includePages || form.includeCountries || form.includeDevices
})

const startIntegration = () => {
    console.log('Starting integration with data:', form.data())
    
    // Build the URL with query parameters
    const params = new URLSearchParams(form.data())
    const url = `${route('search-console.connect')}?${params.toString()}`
    
    // Redirect to the connect route
    window.location.href = url
}
</script>