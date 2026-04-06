<template>
    <div class="space-y-8 text-left">
        <!-- Header -->
        <div class="flex justify-between items-end pb-6 border-b border-gray-100">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Intelligence Ledger</h2>
                <div class="flex items-center mt-2">
                    <span class="w-8 h-1 bg-emerald-500 rounded-full mr-3"></span>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-widest">Macro Trends & Data Summaries</p>
                </div>
            </div>
            <div class="flex gap-3">
                <button @click="exportData('leads')" class="px-6 py-2.5 bg-gray-900 text-white rounded-xl text-sm font-black uppercase tracking-widest hover:bg-emerald-600 transition-all flex items-center gap-2 shadow-xl shadow-gray-200">
                    <i class="fas fa-file-export"></i> LEADS CSV
                </button>
                <button @click="exportData('deals')" class="px-6 py-2.5 bg-gray-900/50 text-white rounded-xl text-sm font-black uppercase tracking-widest hover:bg-emerald-600 transition-all flex items-center gap-2 shadow-xl shadow-gray-200">
                    <i class="fas fa-file-export"></i> DEALS CSV
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Revenue Growth Chart Placeholder -->
            <div class="bg-white border border-gray-100 rounded-[40px] p-8 shadow-sm h-[400px] flex flex-col">
                <div class="flex justify-between items-center mb-10">
                    <h3 class="text-xs font-black uppercase tracking-widest text-gray-400">Monthly Revenue Growth</h3>
                    <div class="text-xs font-black text-emerald-600 bg-emerald-50 px-3 py-1 rounded-lg">FY {{ new Date().getFullYear() }}</div>
                </div>
                
                <div class="flex-1 flex items-end gap-4 px-4 pb-4">
                    <div v-for="item in monthly_revenue" :key="item.month" 
                         class="flex-1 bg-emerald-500 rounded-t-xl relative group"
                         :style="{ height: (item.revenue / maxRevenue * 100) + '%' }">
                        <!-- Tooltip -->
                        <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-sm font-black py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                            ${{ item.revenue }}
                        </div>
                        <div class="absolute -bottom-8 left-1/2 -translate-x-1/2 text-sm font-black text-gray-400 uppercase">
                            {{ formatMonth(item.month) }}
                        </div>
                    </div>
                </div>

                <div v-if="monthly_revenue.length === 0" class="flex-1 flex items-center justify-center text-gray-300 italic text-xs">
                    Insufficient revenue data for projection.
                </div>
            </div>

            <!-- Lead Source Attribution -->
            <div class="bg-white border border-gray-100 rounded-[40px] p-8 shadow-sm flex flex-col">
                <h3 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-10">Lead Source Attribution</h3>
                
                <div class="space-y-6">
                    <div v-for="source in lead_sources" :key="source.source" class="flex items-center gap-6">
                        <div class="w-10 h-10 bg-gray-50 rounded-xl flex items-center justify-center text-emerald-600 border border-gray-100">
                            <i :class="getSourceIcon(source.source)"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between mb-2">
                                <span class="text-xs font-black text-gray-800 uppercase tracking-widest">{{ source.source || 'Direct' }}</span>
                                <span class="text-xs font-black text-gray-400">{{ source.count }} LEADS</span>
                            </div>
                            <div class="w-full bg-gray-50 rounded-full h-2 overflow-hidden">
                                <div class="bg-emerald-500 h-full rounded-full" 
                                     :style="{ width: (source.count / totalLeads * 100) + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="lead_sources.length === 0" class="flex-1 flex items-center justify-center text-gray-300 italic text-xs">
                    Database pending initial lead synchronization.
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    monthly_revenue: { type: Array, default: () => [] },
    lead_sources: { type: Array, default: () => [] }
});

const maxRevenue = computed(() => {
    return Math.max(...props.monthly_revenue.map(i => i.revenue), 1000);
});

const totalLeads = computed(() => {
    return props.lead_sources.reduce((acc, curr) => acc + curr.count, 0) || 1;
});

const formatMonth = (month) => {
    const months = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
    return months[month - 1] || 'UNK';
};

const exportData = (type) => {
    window.location.href = route('crm.reports.export', { type });
};

const getSourceIcon = (source) => {
    const icons = {
        'website': 'fas fa-globe',
        'referral': 'fas fa-user-friends',
        'campaign': 'fas fa-bullhorn',
        'direct': 'fas fa-external-link-square-alt'
    };
    return icons[source?.toLowerCase()] || 'fas fa-dot-circle';
};
</script>
