<template>
    <AppLayout title="Fetch Search Console Data">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Fetch Search Console Data
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="$page.props.flash.error" class="mb-4">
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ $page.props.flash.error }}</span>
                    </div>
                </div>

                <div v-if="$page.props.flash.success" class="mb-4">
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ $page.props.flash.success }}</span>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <form @submit.prevent="submit">
                        <div class="space-y-6">
                            <div>
                                <label for="site" class="block text-sm font-medium text-gray-700">Select Site</label>
                                <select v-model="form.site" id="site" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="">Select a site</option>
                                    <option v-for="site in sites" :key="site.siteUrl" :value="site.siteUrl">
                                        {{ site.siteUrl }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label for="projectCode" class="block text-sm font-medium text-gray-700">Project Code</label>
                                <input type="text" v-model="form.projectCode" id="projectCode" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="startDate" class="block text-sm font-medium text-gray-700">Start Date</label>
                                    <input type="date" v-model="form.startDate" id="startDate" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                </div>

                                <div>
                                    <label for="endDate" class="block text-sm font-medium text-gray-700">End Date</label>
                                    <input type="date" v-model="form.endDate" id="endDate" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" :disabled="form.processing" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <span v-if="form.processing">Processing...</span>
                                    <span v-else>Fetch Data</span>
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
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    sites: {
        type: Array,
        required: true
    }
})

const form = useForm({
    site: '',
    projectCode: '',
    startDate: '',
    endDate: ''
})

const submit = () => {
    form.post(route('search-console.fetch.store'))
}
</script>
