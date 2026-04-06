<script setup>
import ProjectLayout from '@/Layouts/ProjectLayout.vue';
import { ref, onMounted, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ChartBarIcon, CommandLineIcon, QueueListIcon, UserGroupIcon, ShieldCheckIcon } from '@heroicons/vue/24/outline';
import PulseTab from './Tabs/PulseTab.vue';
import PrAnalyticsTab from './Tabs/PrAnalyticsTab.vue';
import ReviewIntelligenceTab from './Tabs/ReviewIntelligenceTab.vue';
import axios from 'axios';

const props = defineProps({
    project: Object,
    repos: Array,
    contributors: { type: Array, default: () => [] }
});

const activeTab = ref('pulse');
const commitsData = ref([]);

const filters = ref({
    dateRange: '30',
    contributor: ''
});

watch(filters, (newFilters) => {
    // router.get(route('projects.devops.index', props.project.id), newFilters, { preserveState: true, preserveScroll: true });
}, { deep: true });

const exportData = (type) => {
    window.open(route('projects.devops.export', { project: props.project.id, type, ...filters.value }), '_blank');
};

const fetchCommits = async () => {
    const res = await axios.get(route('projects.devops.commits', props.project.id));
    commitsData.value = res.data.data; // Paginated
};

onMounted(() => {
    fetchCommits();
});

const tabs = [
    { id: 'pulse', name: 'Pulse', icon: ChartBarIcon },
    { id: 'prs', name: 'Bottlenecks', icon: QueueListIcon },
    { id: 'reviews', name: 'Review Quality', icon: ShieldCheckIcon },
    { id: 'commits', name: 'Commit Stream', icon: CommandLineIcon },
];
</script>

<template>
    <Head title="DevOps Dashboard" />

    <ProjectLayout :project="project">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Code & Repositories
            </h2>
        </template>

        <div class="py-12">
            <!-- Full Width Refactor -->
            <div class="max-w-[100rem] mx-auto sm:px-6 lg:px-8">
                
                <!-- Repo Connection Warning -->
                <div v-if="repos.length === 0" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <div class="flex">
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                This project is not linked to any Git repositories. 
                                <Link :href="route('admin.devops.providers.index')" class="font-medium underline hover:text-yellow-600">Configure Repositories</Link>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Control Bar -->
                <div class="bg-white p-4 rounded-lg shadow mb-6 flex flex-wrap items-center justify-between gap-4 border-l-4 border-indigo-500">
                    <div class="flex items-center space-x-4">
                        <div>
                            <select v-model="filters.dateRange" class="block w-40 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="7">Last 7 Days</option>
                                <option value="30">Last 30 Days</option>
                                <option value="90">Last 90 Days</option>
                                <option value="365">Year to Date</option>
                            </select>
                        </div>
                        <div>
                            <select v-model="filters.contributor" class="block w-48 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">All Contributors</option>
                                <option v-for="c in contributors" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button @click="exportData('pdf')" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            Export PDF
                        </button>
                        <button @click="exportData('excel')" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                            Export Excel
                        </button>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="border-b border-gray-200 mb-6">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button v-for="tab in tabs" :key="tab.name"
                            @click="activeTab = tab.id"
                            :class="[activeTab === tab.id ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700', 'group inline-flex items-center border-b-2 py-4 px-1 text-sm font-medium']">
                            <component :is="tab.icon" :class="[activeTab === tab.id ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500', '-ml-0.5 mr-2 h-5 w-5']" aria-hidden="true" />
                            {{ tab.name }}
                        </button>
                    </nav>
                </div>

                <!-- Tab: Pulse -->
                <div v-if="activeTab === 'pulse'">
                    <PulseTab :project-id="project.id" />
                </div>

                <!-- Tab: PRs (Bottlenecks) -->
                <div v-if="activeTab === 'prs'">
                    <PrAnalyticsTab :project-id="project.id" />
                </div>

                <!-- Tab: Reviews (Quality) -->
                <div v-if="activeTab === 'reviews'">
                    <ReviewIntelligenceTab :project-id="project.id" />
                </div>

                <!-- Tab: Commits -->
                <div v-if="activeTab === 'commits'" class="bg-white shadow overflow-hidden sm:rounded-md">
                    <ul role="list" class="divide-y divide-gray-200">
                        <li v-for="commit in commitsData" :key="commit.id">
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <p class="truncate text-sm font-medium text-indigo-600">{{ commit.message }}</p>
                                    <div class="ml-2 flex flex-shrink-0">
                                        <p class="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800">
                                            {{ commit.hash.substring(0, 7) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-2 sm:flex sm:justify-between">
                                    <div class="sm:flex">
                                        <p class="flex items-center text-sm text-gray-500">
                                            <CommandLineIcon class="mr-1.5 h-5 w-5 flex-shrink-0 text-gray-400" aria-hidden="true" />
                                            {{ commit.author_name }}
                                        </p>
                                    </div>
                                    <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                        <p>
                                            <time :datetime="commit.committed_at">{{ new Date(commit.committed_at).toLocaleDateString() }}</time>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li v-if="commitsData.length === 0" class="px-4 py-8 text-center text-gray-500">
                            No commits found.
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </ProjectLayout>
</template>
