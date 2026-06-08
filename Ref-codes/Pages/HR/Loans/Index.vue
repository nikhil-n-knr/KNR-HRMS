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
    { id: 'dashboard', label: 'Overview', icon: '▥' },
    { id: 'queue', label: 'Application Queue', icon: '▣' },
    { id: 'portfolio', label: 'Active Portfolio', icon: '▤' },
    { id: 'disbursement', label: 'Disbursement', icon: '₹' },
    { id: 'config', label: 'Policies', icon: '⚙' },
];
</script>

<template>
    <Head title="Loan Management" />
    <MainLayout>
   

        <div class="py-4 md:py-5">
            <div class="w-full space-y-4 md:space-y-6 px-3 md:px-4 lg:px-6">

                <div class="relative overflow-hidden rounded-xl bg-gradient-to-r from-indigo-700 via-violet-800 to-violet-600 px-4 md:px-6 py-5 md:py-6 text-white shadow-sm border border-white/10">
                    <div class="absolute inset-0 overflow-hidden pointer-events-none">
                        <div class="absolute -top-10 left-1/3 w-40 h-40 rounded-full bg-white/5"></div>
                        <div class="absolute -bottom-20 right-10 w-[24rem] h-[24rem] rounded-full bg-white/10"></div>
                    </div>
                    <div class="relative z-10">
                        <h2 class="font-semibold text-lg md:text-xl lg:text-2xl text-white leading-tight">
                            Banking-Grade Loan Management
                        </h2>
                        <p class="mt-1 md:mt-2 text-xs md:text-sm text-slate-200 max-w-2xl">
                            A refined loan operations overview with the same expense dashboard header styling.
                        </p>
                    </div>
                </div>

                    <!-- Tab Navigation -->
                 <div class="sticky top-0 z-30 border-b border-gray-100 bg-white px-2 py-2 shadow-sm">
                        <nav class="flex flex-wrap items-center gap-1 md:gap-2 overflow-x-auto">

                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                @click="activeTab = tab.id"
                                :class="[
                                    activeTab === tab.id
                                        ? 'bg-blue-50 text-blue-700 border-blue-200 shadow-sm'
                                        : 'bg-white text-gray-600 border-transparent hover:bg-gray-50 hover:text-gray-900 hover:border-gray-200',
                                    'h-9 px-3 flex items-center gap-2 border rounded-lg font-semibold text-sm transition-all whitespace-nowrap'
                                ]"
                            >
                                <span>{{ tab.icon }}</span>
                                <span>{{ tab.label }}</span>
                            </button>

                        </nav>
                    </div>

                <!-- Tabs Container -->
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm">

                    
                    <!-- Tab Content -->
                    <div class="min-h-[420px] bg-slate-50 px-5 py-5">

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