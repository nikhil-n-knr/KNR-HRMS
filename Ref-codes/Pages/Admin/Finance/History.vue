<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import {
    BanknotesIcon,
    WrenchScrewdriverIcon,
    ShoppingBagIcon,
    CubeIcon
} from '@heroicons/vue/24/outline'; // v2
import GradientHeroHeader from "@/Components/UI/GradientHeroHeader.vue";


defineOptions({ layout: MainLayout });

const props = defineProps({
    ledger: Array,
    stats: Object,
    filters: Object
});

const startDate = ref(props.filters?.start_date || '');
const endDate = ref(props.filters?.end_date || '');

const applyFilters = () => {
    router.get('/admin/finance/ledger', {
        start_date: startDate.value,
        end_date: endDate.value
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

const clearFilters = () => {
    startDate.value = '';
    endDate.value = '';
    applyFilters();
};

const getIcon = (type) => {
    if (type === 'Purchase') return ShoppingBagIcon;
    if (type === 'Maintenance') return WrenchScrewdriverIcon;
    if (type === 'Consumption') return CubeIcon;
    return BanknotesIcon;
};

const formatDate = (dateStr) => {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleDateString() + ' ' + new Date(dateStr).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>

    <Head title="Transaction Ledger" />

        <GradientHeroHeader kicker="" title="Transaction Ledger"
            subtitle="Unified history of purchases, repairs, and consumption.">
            <template #right>
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <div class="px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl shadow-sm text-right">
                        <span class="block text-[10px] font-black uppercase tracking-widest">Total Tracked Spend
                            (Recent)</span>
                        <span class="block text-2xl font-black mt-1">${{
                            stats.total_spend.toLocaleString(undefined,
                                { minimumFractionDigits: 2 }) }}</span>
                    </div>
                </div>
            </template>
        </GradientHeroHeader>

        <div class="p-6" >
        <!-- Header -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm mb-6 flex flex-wrap items-end gap-4 z-10 relative">
            <div class="flex-1 min-w-[200px] text-left">
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Start Date</label>
                <input v-model="startDate" type="date"
                    class="w-full h-11 bg-slate-50 border border-slate-200 rounded-xl px-4 text-xs font-bold text-slate-900 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none" />
            </div>
            <div class="flex-1 min-w-[200px] text-left">
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">End Date</label>
                <input v-model="endDate" type="date"
                    class="w-full h-11 bg-slate-50 border border-slate-200 rounded-xl px-4 text-xs font-bold text-slate-900 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none" />
            </div>
            <div class="flex items-center gap-2">
                <button @click="applyFilters"
                    class="h-11 px-6 bg-indigo-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-sm hover:bg-indigo-700 transition-all cursor-pointer active:scale-95">
                    Filter
                </button>
                <button @click="clearFilters"
                    class="h-11 px-6 bg-slate-100 border border-slate-200 text-slate-600 rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-sm hover:bg-slate-200 transition-all cursor-pointer active:scale-95">
                    Clear
                </button>
            </div>
        </div>

        <!-- Ledger Table -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden z-10 relative">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm whitespace-nowrap">
                    <thead
                        class="sticky top-0 z-10 bg-slate-50/95 backdrop-blur-sm border-b border-slate-200 shadow-[0_1px_0_rgba(148,163,184,0.35)]">
                        <tr>
                            <th
                                class="px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-[0.22em] text-left">
                                Date</th>
                            <th
                                class="px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-[0.22em] text-left">
                                Type</th>
                            <th
                                class="px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-[0.22em] text-left">
                                Description</th>
                            <th
                                class="px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-[0.22em] text-right">
                                Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="item in ledger" :key="item.type + item.reference_id"
                            class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-6 py-4 font-bold font-mono text-xs">
                                {{ formatDate(item.date) }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="flex items-center gap-2 font-bold" :class="item.color">
                                    <component :is="getIcon(item.type)" class="h-4 w-4" />
                                    {{ item.type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-900">
                                {{ item.description }}
                            </td>
                            <td class="px-6 py-4 text-right font-mono font-medium text-slate-700">
                                ${{ parseFloat(item.amount).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
                            </td>
                        </tr>
                        <tr v-if="ledger.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                                No financial events recorded yet.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</template>
