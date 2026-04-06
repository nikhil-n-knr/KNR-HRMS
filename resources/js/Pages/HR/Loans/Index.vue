<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import DashboardMetrics from './Partials/DashboardMetrics.vue';
import ApplicationQueue from './Partials/ApplicationQueue.vue';
import ActivePortfolio from './Partials/ActivePortfolio.vue';
import DisbursementPanel from './Partials/DisbursementPanel.vue';
import ProductConfiguration from './Partials/ProductConfiguration.vue';

const props = defineProps({
    initialTab: String
});

const activeTab = ref(props.initialTab || 'dashboard');

const tabs = [
    { id: 'dashboard', label: 'Overview', icon: 'ChartBar' },
    { id: 'queue', label: 'Application Queue', icon: 'Inbox' },
    { id: 'portfolio', label: 'Active Portfolio', icon: 'FolderOpen' },
    { id: 'disbursement', label: 'Disbursement', icon: 'Cash' },
    { id: 'config', label: 'Policies', icon: 'Cog' },
];
</script>

<template>
    <Head title="Loan Management" />
    <MainLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Banking-Grade Loan Management</h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Tab Navigation -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="border-b border-gray-100">
                         <nav class="flex -mb-px overflow-x-auto">
                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                @click="activeTab = tab.id"
                                :class="[
                                    activeTab === tab.id
                                        ? 'border-indigo-500 text-indigo-600 bg-indigo-50/50'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'py-4 px-6 text-center border-b-2 font-medium text-sm transition-all duration-200 whitespace-nowrap'
                                ]"
                            >   
                                {{ tab.label }}
                            </button>
                        </nav>
                    </div>

                    <div class="p-6 bg-gray-50/30 min-h-[500px]">
                        <Transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 translate-y-1"
                            enter-to-class="opacity-100 translate-y-0"
                            mode="out-in"
                        >
                            <div :key="activeTab">
                                <DashboardMetrics v-if="activeTab === 'dashboard'" />
                                <ApplicationQueue v-if="activeTab === 'queue'" />
                                <ActivePortfolio v-if="activeTab === 'portfolio'" />
                                <DisbursementPanel v-if="activeTab === 'disbursement'" />
                                <ProductConfiguration v-if="activeTab === 'config'" />
                            </div>
                        </Transition>
                    </div>
                </div>

            </div>
        </div>
    </MainLayout>
</template>


