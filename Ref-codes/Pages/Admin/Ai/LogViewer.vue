<script setup>
import { Head } from '@inertiajs/vue3';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import { ExclamationTriangleIcon, CheckCircleIcon, InformationCircleIcon } from '@heroicons/vue/24/solid';

defineProps({
    logs: Object,
    stats: Object
});

const columns = {
    created_at: { label: 'Timestamp', class: 'text-left text-sm text-gray-500' },
    type: { label: 'Type', class: 'text-left font-medium' },
    severity: { label: 'Severity', class: 'text-center' },
    summary: { label: 'Summary', class: 'text-left w-1/3' },
    confidence_score: { label: 'Confidence', class: 'text-center' },
};

const formatDate = (date) => {
    return new Date(date).toLocaleString();
};

const getSeverityClass = (sev) => {
    switch (sev) {
        case 'Warning': return 'bg-amber-100 text-amber-800 border-amber-200';
        case 'Critical': return 'bg-red-100 text-red-800 border-red-200';
        default: return 'bg-blue-50 text-blue-700 border-blue-200';
    }
};
</script>

<template>
    <Head title="AI Watchdog Logs" />

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center bg-white/60 backdrop-blur-md p-6 rounded-2xl shadow-sm border border-white/50">
            <div>
                 <h2 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-violet-600 to-indigo-600">
                    AI Neural Watchdog
                </h2>
                <p class="text-sm text-gray-500 mt-1">Real-time anomaly detection and predictive analysis logs.</p>
            </div>
            <div class="flex space-x-3">
                <div class="px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg text-sm font-medium border border-indigo-100 shadow-sm flex items-center">
                    <div class="w-2 h-2 rounded-full bg-green-500 mr-2 animate-pulse"></div>
                    System Active
                </div>
            </div>
        </div>

        <!-- Stats Cards (Dynamic & Accurate) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
             <div class="bg-white/70 p-5 rounded-xl border border-white/60 shadow-sm backdrop-blur-sm">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Scans</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ stats?.total_scans || 0 }}</p>
             </div>
             <div class="bg-white/70 p-5 rounded-xl border border-white/60 shadow-sm backdrop-blur-sm">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Anomalies Found</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ stats?.anomalies_count || 0 }}</p>
             </div>
             <div class="bg-white/70 p-5 rounded-xl border border-white/60 shadow-sm backdrop-blur-sm">
                 <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Accuracy / Confidence</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1">{{ stats?.accuracy || '100.0%' }}</p>
             </div>
        </div>

        <!-- Logs Table -->
        <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-white/50 overflow-hidden">
            <BaseDataTable 
                :columns="columns" 
                :data="logs?.data || []" 
                :pagination="logs || {}"
            >
                <template #cell-created_at="{ item }">
                    {{ formatDate(item.created_at) }}
                </template>
                
                <template #cell-severity="{ item }">
                    <span :class="['px-2.5 py-0.5 rounded-full text-xs font-bold border', getSeverityClass(item.severity)]">
                        {{ item.severity }}
                    </span>
                </template>

                <template #cell-confidence_score="{ item }">
                    <div class="flex items-center justify-center space-x-2">
                         <div class="w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-500" :style="{ width: (item.confidence_score * 100) + '%' }"></div>
                         </div>
                         <span class="text-xs text-gray-600 font-mono">{{ (item.confidence_score * 100).toFixed(0) }}%</span>
                    </div>
                </template>
                
                 <template #rowActions="{ item }">
                    <button class="text-gray-400 hover:text-indigo-600 transition-colors">
                        View Details
                    </button>
                </template>
            </BaseDataTable>
        </div>
    </div>
</template>
