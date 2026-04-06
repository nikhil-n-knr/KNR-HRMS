<script setup>
import { ref, watch, onMounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
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

const activeTab = ref(props.initialTab || 'analytics');

const tabs = [
    { id: 'analytics', label: 'Analytics & Insights', icon: 'ChartBarIcon' },
    { id: 'approvals', label: 'Pending Approvals', icon: 'InboxIcon' },
    { id: 'disbursement', label: 'Ready for Disbursement', icon: 'CurrencyRupeeIcon' },
    { id: 'history', label: 'Expense History', icon: 'ArchiveBoxIcon' },
    { id: 'config', label: 'Configuration', icon: 'CogIcon' },
];

const switchTab = (tabId) => {
    activeTab.value = tabId;
    // Optional: Update URL without reload
    const url = new URL(window.location.href);
    url.searchParams.set('tab', tabId);
    window.history.replaceState({}, '', url);
};
</script>

<template>
    <Head title="Expense Dashboard" />
    <MainLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Comprehensive Expense Dashboard</h2>
                <div class="flex gap-3">
                     <button class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 shadow-sm">
                        Export Report
                     </button>
                     <button class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 shadow-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        New Expense Request
                     </button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Smart Tab System -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="border-b border-gray-100">
                        <nav class="flex -mb-px overflow-x-auto">
                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                @click="switchTab(tab.id)"
                                :class="[
                                    activeTab === tab.id
                                        ? 'border-indigo-500 text-indigo-600 bg-indigo-50/50'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'py-4 px-6 text-center border-b-2 font-medium text-sm transition-all duration-200 flex items-center justify-center gap-2 whitespace-nowrap'
                                ]"
                            >   
                                <!-- Simple Icon Placeholder check -->
                                <span v-if="tab.id === 'analytics'">📊</span>
                                <span v-if="tab.id === 'approvals'">📨</span>
                                <span v-if="tab.id === 'disbursement'">💰</span>
                                <span v-if="tab.id === 'history'">📜</span>
                                <span v-if="tab.id === 'config'">⚙️</span>
                                {{ tab.label }}
                            </button>
                        </nav>
                    </div>

                    <div class="p-6 bg-gray-50/30 min-h-[500px]">
                        <Transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="transform opacity-0 translate-y-2"
                            enter-to-class="transform opacity-100 translate-y-0"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="transform opacity-100 translate-y-0"
                            leave-to-class="transform opacity-0 translate-y-2"
                            mode="out-in"
                        >
                            <div :key="activeTab">
                                <ExpenseAnalytics v-if="activeTab === 'analytics'" />
                                <PendingApprovals v-if="activeTab === 'approvals'" />
                                <DisbursementQueue v-if="activeTab === 'disbursement'" />
                                <ExpenseHistory v-if="activeTab === 'history'" />
                                <Configuration v-if="activeTab === 'config'" :categories="categories" :workflows="workflows" />
                            </div>
                        </Transition>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
