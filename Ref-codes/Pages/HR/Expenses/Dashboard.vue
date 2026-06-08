<script setup>
import { ref, watch } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

import Configuration from './Partials/Configuration.vue';
import ExpenseAnalytics from './Partials/ExpenseAnalytics.vue';
import PendingApprovals from './Partials/PendingApprovals.vue';
import DisbursementQueue from './Partials/DisbursementQueue.vue';
import ExpenseHistory from './Partials/ExpenseHistory.vue';

const props = defineProps({
    initialTab: String,
    categories: Array,
    workflows: Array
});

const page = usePage();

const urlTab = new URLSearchParams(page.url.split('?')[1]).get('tab');

const activeTab = ref(
    urlTab || props.initialTab || 'analytics'
);

const tabs = [
    { id: 'analytics', label: 'Analytics & Insights' },
    { id: 'approvals', label: 'Pending Approvals' },
    { id: 'disbursement', label: 'Ready for Disbursement'},
    { id: 'history', label: 'Expense History' },
    { id: 'config', label: 'Configuration' },
];

const switchTab = (tabId) => {
    activeTab.value = tabId;

    const url = new URL(window.location.href);
    url.searchParams.set('tab', tabId);
    window.history.replaceState({}, '', url.toString());
};

watch(
    () => page.url,
    (newUrl) => {
        const params = new URLSearchParams(newUrl.split('?')[1]);
        const tab = params.get('tab');

        if (tab) activeTab.value = tab;
    }
);
</script>

<template>
    <Head title="Expense Dashboard" />

    <MainLayout>

        <!-- PAGE BODY -->
        <div class="py-4">
            <div class="w-full space-y-6">

                <!-- HEADER MOVED HERE (INSIDE BODY) -->
       <div class="relative overflow-hidden rounded-[20px] bg-gradient-to-r from-indigo-700 via-violet-800 to-violet-600 px-8 md:px-6 py-6">

    <!-- Background Decorations -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-10 left-1/3 w-40 h-40 rounded-full bg-white/5"></div>
        <div class="absolute -bottom-20 right-10 w-[24rem] h-[24rem] rounded-full bg-white/10"></div>
    </div>

    <!-- Content -->
    <div class="relative z-10 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8">

        <!-- LEFT -->
        <h2 class="font-semibold text-xl md:text-2xl text-white leading-tight">
            Comprehensive Expense Dashboard
        </h2>

        <!-- RIGHT ACTIONS -->
        <div class="flex gap-3">

            <button class="h-12 px-6 rounded-xl bg-white/10 text-white font-bold uppercase tracking-[0.12em] border border-white/20 hover:bg-white/20 transition-all shadow-sm">
                Export Report
            </button>
  <button
        class="h-12 px-6 rounded-xl bg-white text-slate-900 font-bold uppercase tracking-[0.12em] flex items-center gap-2 shadow-xl hover:scale-105 hover:bg-indigo-50 transition-all duration-300 whitespace-nowrap"
    >
        <span>➕</span>
        New Expense Request
    </button>

        </div>

    </div>
</div>


<!-- STICKY TABS -->
<div class="sticky top-0 z-30 border-b border-gray-100 bg-white px-2 py-2 shadow-sm">

    <nav class="flex items-center gap-1 overflow-x-auto">

        <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="switchTab(tab.id)"
            :class="[
                activeTab === tab.id
                    ? 'bg-blue-50 text-blue-700 border-blue-200 shadow-sm'
                    : 'bg-white text-gray-600 border-transparent hover:bg-gray-50 hover:text-gray-900 hover:border-gray-200',
                'h-10 px-3 flex items-center gap-2 border rounded-lg font-semibold text-sm transition-all whitespace-nowrap'
            ]"
        >
            <span>{{ tab.label }}</span>
        </button>

    </nav>

</div>

                <!-- SMART TAB SYSTEM -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

      

                    <!-- CONTENT -->
                    <div class="p-6 bg-gray-50/30 min-h-[500px]">

                        <Transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 translate-y-2"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 translate-y-2"
                            mode="out-in"
                        >
                            <div :key="activeTab">

                                <ExpenseAnalytics v-if="activeTab === 'analytics'" />
                                <PendingApprovals v-if="activeTab === 'approvals'" />
                                <DisbursementQueue v-if="activeTab === 'disbursement'" />
                                <ExpenseHistory v-if="activeTab === 'history'" />
                                <Configuration
                                    v-if="activeTab === 'config'"
                                    :categories="categories"
                                    :workflows="workflows"
                                />

                            </div>
                        </Transition>

                    </div>
                </div>

            </div>
        </div>

    </MainLayout>
</template>