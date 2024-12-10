<template>
    <AppLayout title="Analysis Details">
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    <Breadcrumbs :pages="breadcrumbs" />
                </h2>
            </div>
        </template>

        <div class="py-12">
            
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                    <!-- Historical Rank Graph -->
                        <div v-if="props.analysis.historical_ranks?.length" class="mb-6">
                            <div class="bg-white p-6 rounded-lg shadow">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Historical Rank Overview</h3>
                                <div class="h-64">
                                    <Line
                                        :data="getHistoricalRankChartData(props.analysis.historical_ranks)"
                                        :options="{
                                            responsive: true,
                                            maintainAspectRatio: false,
                                            scales: {
                                                y: {
                                                    reverse: true,
                                                    beginAtZero: true,
                                                    title: {
                                                        display: true,
                                                        text: 'Rank Position'
                                                    }
                                                },
                                                x: {
                                                    title: {
                                                        display: true,
                                                        text: 'Date'
                                                    }
                                                }
                                            },
                                            plugins: {
                                                tooltip: {
                                                    callbacks: {
                                                        label: function(context) {
                                                            return `Rank: ${context.parsed.y}`;
                                                        }
                                                    }
                                                },
                                                legend: {
                                                    display: false
                                                },
                                                filler: {
                                                    propagate: false
                                                }
                                            }
                                        }"
                                    />
                                </div>
                            </div>
                        </div>



                         
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                    <!-- Screenshot Section -->
                    <div v-if="props.analysis.screenshot_url" class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Screenshots</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Desktop Screenshot -->
                            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                                <div class="p-4 border-b">
                                    <h4 class="text-sm font-medium text-gray-700">Desktop View</h4>
                                    <p class="text-xs text-gray-500 mt-1">Full width desktop screenshot</p>
                                </div>
                                <div class="p-4">
                                    <div class="relative group cursor-pointer" @click="openModal(props.analysis.screenshot_url)">
                                        <img 
                                            :src="props.analysis.screenshot_url" 
                                            :alt="'Desktop Screenshot of ' + props.analysis.url"
                                            class="w-full h-48 object-cover rounded-md"
                                        />
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-200 flex items-center justify-center">
                                            <span class="text-white opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                Click to enlarge
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Mobile Screenshot -->
                            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                                <div class="p-4 border-b">
                                    <h4 class="text-sm font-medium text-gray-700">Mobile View</h4>
                                    <p class="text-xs text-gray-500 mt-1">320px width mobile screenshot</p>
                                </div>
                                <div class="p-4">
                                    <div class="relative group cursor-pointer" @click="openModal(props.analysis.screenshot_url)">
                                        <img 
                                            :src="props.analysis.screenshot_url" 
                                            :alt="'Mobile Screenshot of ' + props.analysis.url"
                                            class="w-full h-48 object-cover rounded-md"
                                        />
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-200 flex items-center justify-center">
                                            <span class="text-white opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                Click to enlarge
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs Navigation -->
                    <div class="border-b border-gray-200 mb-6">
                        <nav class="-mb-px flex space-x-8">
                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                @click="activeTab = tab.id"
                                :class="[
                                    activeTab === tab.id
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                                ]"
                            >
                                {{ tab.name }}
                            </button>
                        </nav>
                    </div>

                    <!-- Tab Content -->
                    <div v-if="props.analysis.status === 'completed'" class="mb-6">
                        <!-- Meta Tab -->
<div v-if="activeTab === 'meta'" class="space-y-6">
    <div class="bg-gray-50 p-6 rounded-lg">
        <h4 class="text-lg font-medium text-gray-900 mb-4">Meta Information</h4>
        
        <!-- Title and Description -->
        <div class="mb-6">
            <div class="mb-4">
                <h5 class="text-sm font-medium text-gray-700 mb-2">Title</h5>
                <p class="text-gray-900 bg-white p-3 rounded border">{{ props.analysis.metadata?.title || 'No title available' }}</p>
            </div>
            <div>
                <h5 class="text-sm font-medium text-gray-700 mb-2">Description</h5>
                <p class="text-gray-900 bg-white p-3 rounded border">{{ props.analysis.metadata?.description || 'No description available' }}</p>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <h5 class="text-sm font-medium text-gray-700 mb-2">Contact Information</h5>
                <div class="bg-white p-3 rounded border">
                    <div v-if="props.analysis.metadata?.phone_numbers?.length" class="mb-2">
                        <span class="text-sm font-medium text-gray-600">Phone Numbers:</span>
                        <ul class="list-disc list-inside">
                            <li v-for="phone in props.analysis.metadata.phone_numbers" :key="phone" class="text-gray-900">
                                {{ phone }}
                            </li>
                        </ul>
                    </div>
                    <div v-if="props.analysis.metadata?.email_addresses?.length">
                        <span class="text-sm font-medium text-gray-600">Email Addresses:</span>
                        <ul class="list-disc list-inside">
                            <li v-for="email in props.analysis.metadata.email_addresses" :key="email" class="text-gray-900">
                                {{ email }}
                            </li>
                        </ul>
                    </div>
                    <p v-if="!props.analysis.metadata?.phone_numbers?.length && !props.analysis.metadata?.email_addresses?.length" class="text-gray-500 italic">
                        No contact information available
                    </p>
                </div>
            </div>
            
            <div>
                <h5 class="text-sm font-medium text-gray-700 mb-2">Social Media</h5>
                <div class="bg-white p-3 rounded border">
                    <div v-if="props.analysis.metadata?.social_media_urls?.length">
                        <ul class="space-y-2">
                            <li v-for="url in props.analysis.metadata.social_media_urls" :key="url">
                                <a :href="url" target="_blank" class="text-blue-600 hover:text-blue-800">
                                    {{ url }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <p v-else class="text-gray-500 italic">No social media links available</p>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div>
            <h5 class="text-sm font-medium text-gray-700 mb-2">Additional Information</h5>
            <div class="bg-white p-3 rounded border grid grid-cols-2 gap-4">
                <div>
                    <span class="text-sm font-medium text-gray-600">Country:</span>
                    <p class="text-gray-900">{{ props.analysis.metadata?.country_iso_code || 'Not available' }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-600">Language:</span>
                    <p class="text-gray-900">{{ props.analysis.metadata?.language_code || 'Not available' }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-600">Domain Rank:</span>
                    <p class="text-gray-900">{{ props.analysis.domain_rank || 'Not available' }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-600">Last Visited:</span>
                    <p class="text-gray-900">{{ props.analysis.last_visited ? format(new Date(props.analysis.last_visited), 'PPpp') : 'Not available' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

                        <!-- Technical Tab -->
                        <div v-if="activeTab === 'technical'" class="space-y-6">
                            <div v-if="props.analysis.technologies && props.analysis.technologies.length > 0">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div 
                                        v-for="tech in props.analysis.technologies" 
                                        :key="tech.name"
                                        class="border rounded-lg p-4 hover:shadow-md transition-shadow"
                                    >
                                        <div class="flex items-center mb-2">
                                            <img 
                                                v-if="tech.icon"
                                                :src="tech.icon"
                                                :alt="tech.name"
                                                class="w-6 h-6 mr-2"
                                            />
                                            <h4 class="font-medium">{{ tech.name }}</h4>
                                        </div>
                                        <p v-if="tech.version" class="text-sm text-gray-600 mb-2">
                                            Version: {{ tech.version }}
                                        </p>
                                        <div v-if="tech.categories && tech.categories.length" class="flex flex-wrap gap-1">
                                            <span 
                                                v-for="category in tech.categories"
                                                :key="category"
                                                class="px-2 py-1 text-xs bg-gray-100 rounded-full"
                                            >
                                                {{ category }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CRO Tab -->
                        <div v-if="activeTab === 'cro'" class="space-y-6">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium mb-4">Social Metrics</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <p><span class="font-medium">Facebook Shares:</span> {{ props.analysis.metrics?.facebook_shares }}</p>
                                    </div>
                                    <div>
                                        <p><span class="font-medium">Twitter Shares:</span> {{ props.analysis.metrics?.twitter_shares }}</p>
                                    </div>
                                    <div>
                                        <p><span class="font-medium">LinkedIn Shares:</span> {{ props.analysis.metrics?.linkedin_shares }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Competitors Tab -->
                        <div v-if="activeTab === 'competitors'" class="space-y-6">
                            <div v-if="props.analysis.competitors && props.analysis.competitors.length" class="space-y-4">
                                <!-- Search Bar -->
                                <div class="flex items-center space-x-4">
                                    <div class="flex-1">
                                        <input
                                            type="text"
                                            v-model="searchQuery"
                                            placeholder="Søk i konkurrenter..."
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                        />
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        Viser {{ filteredCompetitors.length }} av {{ props.analysis.competitors.length }} konkurrenter
                                    </div>
                                </div>

                                <!-- Table -->
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th v-for="(column, key) in {
                                                    competitor_domain: 'Domain',
                                                    avg_position: 'Avg Position',
                                                    intersections: 'Intersections',
                                                    location_code: 'Location',
                                                    se_type: 'Type'
                                                }" 
                                                    :key="key"
                                                    scope="col" 
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                                    @click="toggleSort(key)"
                                                >
                                                    <div class="flex items-center space-x-1">
                                                        <span>{{ column }}</span>
                                                        <span class="text-gray-400">{{ getSortIcon(key) }}</span>
                                                    </div>
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <template v-for="competitor in paginatedCompetitors" :key="competitor.id">
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4">
                                                        <button 
                                                            @click="toggleCompetitor(competitor.id)"
                                                            class="text-blue-600 hover:text-blue-800 font-medium"
                                                        >
                                                            {{ competitor.competitor_domain }}
                                                        </button>
                                                    </td>
                                                    <td class="px-6 py-4">{{ competitor.avg_position }}</td>
                                                    <td class="px-6 py-4">{{ competitor.intersections }}</td>
                                                    <td class="px-6 py-4">{{ competitor.location_code }}</td>
                                                    <td class="px-6 py-4">{{ competitor.se_type }}</td>
                                                    <td class="px-6 py-4">
                                                        <button 
                                                            @click="deleteCompetitor(competitor.id)"
                                                            :disabled="deletingCompetitor === competitor.id"
                                                            class="text-red-600 hover:text-red-900"
                                                        >
                                                            <span v-if="deletingCompetitor === competitor.id">
                                                                Deleting...
                                                            </span>
                                                            <span v-else>
                                                                Delete
                                                            </span>
                                                        </button>
                                                    </td>
                                                </tr>
                                                <!-- Expanded Metrics Section -->
                                                <tr v-if="expandedCompetitor === competitor.id">
                                                    <td colspan="6" class="px-6 py-4 bg-gray-50">
                                                        <div v-if="competitor.metrics" class="space-y-4">
                                                            <!-- Organic Metrics -->
                                                            <div v-if="competitor.metrics?.organic" class="bg-white p-4 rounded-lg shadow">
                                                                <h4 class="text-lg font-medium text-gray-900 mb-3">Organic Metrics</h4>
                                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                                    <div class="space-y-4">
                                                                        <div class="grid grid-cols-2 gap-4">
                                                                            <div class="space-y-2">
                                                                                <p class="flex justify-between">
                                                                                    <span class="text-gray-600">ETV:</span>
                                                                                    <span class="font-medium">{{ formatNumber(competitor.metrics.organic.etv) }}</span>
                                                                                </p>
                                                                                <p class="flex justify-between">
                                                                                    <span class="text-gray-600">Keywords:</span>
                                                                                    <span class="font-medium">{{ formatNumber(competitor.metrics.organic.count) }}</span>
                                                                                </p>
                                                                                <p class="flex justify-between">
                                                                                    <span class="text-gray-600">Traffic Cost:</span>
                                                                                    <span class="font-medium">${{ formatNumber(competitor.metrics.organic.estimated_paid_traffic_cost) }}</span>
                                                                                </p>
                                                                            </div>
                                                                            <div class="space-y-2">
                                                                                <p class="flex justify-between">
                                                                                    <span class="text-gray-600">New:</span>
                                                                                    <span class="font-medium text-green-600">+{{ formatNumber(competitor.metrics.organic.is_new) }}</span>
                                                                                </p>
                                                                                <p class="flex justify-between">
                                                                                    <span class="text-gray-600">Improved:</span>
                                                                                    <span class="font-medium text-green-600">+{{ formatNumber(competitor.metrics.organic.is_up) }}</span>
                                                                                </p>
                                                                                <p class="flex justify-between">
                                                                                    <span class="text-gray-600">Declined:</span>
                                                                                    <span class="font-medium text-red-600">-{{ formatNumber(competitor.metrics.organic.is_down) }}</span>
                                                                                </p>
                                                                                <p class="flex justify-between">
                                                                                    <span class="text-gray-600">Lost:</span>
                                                                                    <span class="font-medium text-red-600">-{{ formatNumber(competitor.metrics.organic.is_lost) }}</span>
                                                                                </p>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <!-- Position Distribution Chart -->
                                                                    <div class="h-48">
                                                                        <Bar
                                                                            v-if="getPositionChartData(competitor.metrics)"
                                                                            :data="getPositionChartData(competitor.metrics)"
                                                                            :options="{
                                                                                responsive: true,
                                                                                maintainAspectRatio: false,
                                                                                scales: {
                                                                                    y: {
                                                                                        beginAtZero: true,
                                                                                        title: {
                                                                                            display: true,
                                                                                            text: 'Number of Keywords'
                                                                                        }
                                                                                    }
                                                                                },
                                                                                plugins: {
                                                                                    legend: {
                                                                                        display: false
                                                                                    },
                                                                                    tooltip: {
                                                                                        callbacks: {
                                                                                            label: function(context) {
                                                                                                return `${context.parsed.y} keywords`;
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                }
                                                                            }"
                                                                        />
                                                                        <div v-else class="flex items-center justify-center h-full text-gray-500">
                                                                            No position data available
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div v-else class="text-gray-500 italic">
                                                            No metrics available for this competitor
                                                        </div>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>

                                    <!-- Pagination -->
                                    <div class="mt-4 flex items-center justify-between">
                                        <div class="flex-1 flex justify-between sm:hidden">
                                            <button
                                                @click="currentPage--"
                                                :disabled="currentPage === 1"
                                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                            >
                                                Previous
                                            </button>
                                            <button
                                                @click="currentPage++"
                                                :disabled="currentPage >= totalPages"
                                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                            >
                                                Next
                                            </button>
                                        </div>
                                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                                            <div>
                                                <p class="text-sm text-gray-700">
                                                    Showing
                                                    <span class="font-medium">{{ ((currentPage - 1) * itemsPerPage) + 1 }}</span>
                                                    to
                                                    <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, props.analysis.competitors?.length) }}</span>
                                                    of
                                                    <span class="font-medium">{{ props.analysis.competitors?.length }}</span>
                                                    results
                                                </p>
                                            </div>
                                            <div>
                                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                                    <button
                                                        v-for="page in totalPages"
                                                        :key="page"
                                                        @click="currentPage = page"
                                                        :class="[
                                                            currentPage === page
                                                                ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                                                                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                                            'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                                                        ]"
                                                    >
                                                        {{ page }}
                                                    </button>
                                                </nav>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Add the new Ranked Keywords tab content -->
                            <!-- Replace the existing keywords table section with this -->
                            <div v-if="activeTab === 'keywords'" class="space-y-6">
                                <div v-if="props.analysis.keywords && props.analysis.keywords.length" class="space-y-4">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th scope="col" 
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                                                        @click="toggleKeywordSort('keyword')"
                                                    >
                                                        Keyword {{ getKeywordSortIcon('keyword') }}
                                                    </th>
                                                    <th scope="col" 
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                                                        @click="toggleKeywordSort('search_volume')"
                                                    >
                                                        Search Volume {{ getKeywordSortIcon('search_volume') }}
                                                    </th>
                                                    <th scope="col" 
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                                                        @click="toggleKeywordSort('competition')"
                                                    >
                                                        Competition {{ getKeywordSortIcon('competition') }}
                                                    </th>
                                                    <th scope="col" 
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                                                        @click="toggleKeywordSort('cpc')"
                                                    >
                                                        CPC {{ getKeywordSortIcon('cpc') }}
                                                    </th>
                                                    <th scope="col" 
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                                                        @click="toggleKeywordSort('traffic_cost')"
                                                    >
                                                        Traffic Cost {{ getKeywordSortIcon('traffic_cost') }}
                                                    </th>
                                                    <template v-for="(month, index) in monthNames" :key="index">
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            {{ month }}
                                                        </th>
                                                    </template>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <tr v-for="keyword in paginatedKeywords" :key="keyword.keyword" class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ keyword.keyword }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ formatNumber(keyword.search_volume) }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ (keyword.competition * 100).toFixed(2) }}%
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        ${{ keyword.cpc?.toFixed(2) || '0.00' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        ${{ keyword.traffic_cost?.toFixed(2) || '0.00' }}
                                                    </td>
                                                    <template v-for="(month, index) in monthNames" :key="index">
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm"
                                                            :class="getMonthlySearchColor(keyword.monthly_searches?.[index], keyword.monthly_searches || [])"
                                                        >
                                                            {{ keyword.monthly_searches?.[index] ? formatNumber(keyword.monthly_searches[index]) : '-' }}
                                                        </td>
                                                    </template>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination -->
                                    <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                                        <div class="flex-1 flex justify-between sm:hidden">
                                            <button
                                                @click="currentKeywordPage--"
                                                :disabled="currentKeywordPage === 1"
                                                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                                :class="{ 'opacity-50 cursor-not-allowed': currentKeywordPage === 1 }"
                                            >
                                                Previous
                                            </button>
                                            <button
                                                @click="currentKeywordPage++"
                                                :disabled="currentKeywordPage >= totalKeywordPages"
                                                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                                :class="{ 'opacity-50 cursor-not-allowed': currentKeywordPage >= totalKeywordPages }"
                                            >
                                                Next
                                            </button>
                                        </div>
                                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                                            <div>
                                                <p class="text-sm text-gray-700">
                                                    Showing
                                                    <span class="font-medium">{{ ((currentKeywordPage - 1) * itemsPerPage) + 1 }}</span>
                                                    to
                                                    <span class="font-medium">{{ Math.min(currentKeywordPage * itemsPerPage, props.analysis.keywords?.length) }}</span>
                                                    of
                                                    <span class="font-medium">{{ props.analysis.keywords?.length }}</span>
                                                    results
                                                </p>
                                            </div>
                                            <div>
                                                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                                    <button
                                                        @click="currentKeywordPage--"
                                                        :disabled="currentKeywordPage === 1"
                                                        class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0"
                                                        :class="{ 'opacity-50 cursor-not-allowed': currentKeywordPage === 1 }"
                                                    >
                                                        <span class="sr-only">Previous</span>
                                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                    <button
                                                        @click="currentKeywordPage++"
                                                        :disabled="currentKeywordPage >= totalKeywordPages"
                                                        class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0"
                                                        :class="{ 'opacity-50 cursor-not-allowed': currentKeywordPage >= totalKeywordPages }"
                                                    >
                                                        <span class="sr-only">Next</span>
                                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </nav>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center text-gray-500 py-8">
                                    No keyword data available
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" 
             class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
             @click="closeModal">
            <div class="relative max-w-4xl w-full max-h-[90vh] bg-white rounded-lg p-4 overflow-auto"
                 @click.stop>
                <button 
                    @click="closeModal"
                    class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 bg-white rounded-full p-2"
                    aria-label="Close modal">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <img 
                    :src="modalImage" 
                    :alt="'Screenshot of ' + props.analysis.url"
                    class="w-full h-auto"
                />
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link } from "@inertiajs/vue3";
import { router } from '@inertiajs/vue3';
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import { Bar, Line } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, PointElement, LineElement, Title, Tooltip, Legend, Filler } from 'chart.js';
import dayjs from 'dayjs';

// Register Chart.js components
ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

// Props
const props = defineProps({
    analysis: Object,
    project: Object,
});

// Constants
const itemsPerPage = 10;
const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

const tabs = [
    { id: 'meta', name: 'Meta Information' },
    { id: 'technical', name: 'Technical' },
    { id: 'competitors', name: 'Competitors' },
    { id: 'keywords', name: 'Ranked Keywords' }
];

// Refs
const activeTab = ref('meta');
const currentPage = ref(1);
const currentKeywordPage = ref(1);
const keywordSort = ref({
    column: 'search_volume',
    direction: 'desc'
});
const competitorSort = ref({
    column: 'competitor_domain',
    direction: 'asc'
});
const expandedCompetitor = ref(null);
const searchQuery = ref('');
const showModal = ref(false);
const modalImage = ref('');
const deletingCompetitor = ref(null);

// Computed Properties
const filteredCompetitors = computed(() => {
    if (!props.analysis.competitors) return [];
    let filtered = props.analysis.competitors.filter(competitor =>
        competitor.competitor_domain.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
    
    // Apply sorting
    return [...filtered].sort((a, b) => {
        const column = competitorSort.value.column;
        const direction = competitorSort.value.direction === 'asc' ? 1 : -1;
        
        if (column === 'competitor_domain') {
            return direction * a.competitor_domain.localeCompare(b.competitor_domain);
        }
        
        // Handle numeric columns
        return direction * (a[column] - b[column]);
    });
});

const paginatedCompetitors = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return filteredCompetitors.value.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(filteredCompetitors.value.length / itemsPerPage);
});

const paginatedKeywords = computed(() => {
    const start = (currentKeywordPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return sortedKeywords.value.slice(start, end);
});

const sortedKeywords = computed(() => {
    if (!props.analysis.keywords) return [];
    
    let sorted = [...props.analysis.keywords];
    if (keywordSort.value.column) {
        sorted.sort((a, b) => {
            let aValue = a[keywordSort.value.column];
            let bValue = b[keywordSort.value.column];
            
            // Convert string numbers to actual numbers for sorting
            if (typeof aValue === 'string' && !isNaN(aValue)) {
                aValue = parseFloat(aValue);
            }
            if (typeof bValue === 'string' && !isNaN(bValue)) {
                bValue = parseFloat(bValue);
            }
            
            if (keywordSort.value.direction === 'asc') {
                return aValue > bValue ? 1 : -1;
            } else {
                return aValue < bValue ? 1 : -1;
            }
        });
    }
    return sorted;
});

const totalKeywordPages = computed(() => {
    return Math.ceil(sortedKeywords.value.length / itemsPerPage);
});

// Methods
const toggleCompetitor = (id) => {
    expandedCompetitor.value = expandedCompetitor.value === id ? null : id;
};

const deleteCompetitor = async (id) => {
    if (!confirm('Are you sure you want to delete this competitor?')) {
        return;
    }

    deletingCompetitor.value = id;

    try {
        await router.delete(`/website-spy/competitors/${props.analysis.uuid}/${id}`, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                // Close expanded view if the deleted competitor was expanded
                if (expandedCompetitor.value === id) {
                    expandedCompetitor.value = null;
                }
                deletingCompetitor.value = null;
            },
            onError: () => {
                deletingCompetitor.value = null;
                alert('Failed to delete competitor. Please try again.');
            }
        });
    } catch (error) {
        console.error('Error deleting competitor:', error);
        deletingCompetitor.value = null;
        alert('Failed to delete competitor. Please try again.');
    }
};

const toggleSort = (column) => {
    if (competitorSort.value.column === column) {
        competitorSort.value.direction = competitorSort.value.direction === 'asc' ? 'desc' : 'asc';
    } else {
        competitorSort.value.column = column;
        competitorSort.value.direction = 'asc';
    }
};

const getSortIcon = (column) => {
    if (competitorSort.value.column !== column) return '↕️';
    return competitorSort.value.direction === 'asc' ? '↑' : '↓';
};

const toggleKeywordSort = (column) => {
    if (keywordSort.value.column === column) {
        keywordSort.value.direction = keywordSort.value.direction === 'asc' ? 'desc' : 'asc';
    } else {
        keywordSort.value.column = column;
        keywordSort.value.direction = 'desc';
    }
};

const getKeywordSortIcon = (column) => {
    if (keywordSort.value.column !== column) return '↕️';
    return keywordSort.value.direction === 'asc' ? '↑' : '↓';
};

const getMonthlySearchColor = (value, monthlySearches) => {
    if (!value) return '';
    
    const validValues = monthlySearches.filter(v => v !== null && v !== undefined);
    if (validValues.length === 0) return '';
    
    const max = Math.max(...validValues);
    const min = Math.min(...validValues);
    const range = max - min;
    
    const percentage = range === 0 ? 1 : (value - min) / range;
    
    if (percentage >= 0.7) return 'text-green-600 font-medium';
    if (percentage <= 0.3) return 'text-red-600 font-medium';
    return 'text-gray-900';
};

const formatNumber = (num) => {
    if (num === null || num === undefined || isNaN(num)) return '0';
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
};

const getHistoricalRankChartData = (historicalRanks) => {
    if (!historicalRanks || historicalRanks.length === 0) {
        return {
            labels: [],
            datasets: []
        };
    }

    const sortedRanks = [...historicalRanks].sort((a, b) => new Date(a.date) - new Date(b.date));

    return {
        labels: sortedRanks.map(rank => dayjs(rank.date).format('MMM YYYY')),
        datasets: [
            {
                label: 'Rank Position',
                data: sortedRanks.map(rank => rank.rank),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }
        ]
    };
};

const getPositionChartData = (metrics) => {
    if (!metrics?.organic) {
        console.log('No organic metrics found:', metrics);
        return null;
    }
    
    const organic = metrics.organic;

    console.log('Organic metrics:', organic);
    
    // Ensure all values are numbers
    const data = [
        parseInt(organic.pos_1) || 0,
        parseInt(organic.pos_2_3) || 0,
        parseInt(organic.pos_4_10) || 0,
        parseInt(organic.pos_11_20) || 0,
        parseInt(organic.pos_21_30) || 0,
        parseInt(organic.pos_31_40) || 0,
        parseInt(organic.pos_41_50) || 0,
        parseInt(organic.pos_51_60) || 0,
        parseInt(organic.pos_61_70) || 0,
        parseInt(organic.pos_71_80) || 0,
        parseInt(organic.pos_81_90) || 0,
        parseInt(organic.pos_91_100) || 0
    ];

    // Return null if all values are 0
    if (data.every(value => value === 0)) {
        console.log('All position values are 0');
        return null;
    }

    return {
        labels: ['Pos 1', 'Pos 2-3', 'Pos 4-10', 'Pos 11-20', 'Pos 21-30', 'Pos 31-40', 'Pos 41-50', 'Pos 51-60', 'Pos 61-70', 'Pos 71-80', 'Pos 81-90', 'Pos 91-100'],
        datasets: [{
            label: 'Keywords',
            data: data,
            backgroundColor: '#60A5FA',
            borderColor: '#3B82F6',
            borderWidth: 1
        }]
    };
};

// Define breadcrumbs
const breadcrumbs = [
    { name: "Home", href: "/" },
    { name: "Projects", href: "/projects" },
    { name: "Website Spy", href: "/website-spy" },
    { name: props.analysis.url , href: "https://"+props.analysis.url, target: "_blank" },
];

// Watch for status changes
watch(() => props.analysis.status, (newStatus, oldStatus) => {
    if (['completed', 'failed'].includes(newStatus)) {
        stopRefreshing();
    }
});

// Reset pagination when search query changes
watch(searchQuery, () => {
    currentPage.value = 1;
});

// Reset pagination when keyword sort changes
watch(keywordSort, () => {
    currentKeywordPage.value = 1;
}, { deep: true });

// Reset pagination when competitor sort changes
watch(competitorSort, () => {
    currentPage.value = 1;
}, { deep: true });
</script>

<style scoped>
/* Add any scoped styles here */
</style>