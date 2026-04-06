<script setup>
import { Link } from '@inertiajs/vue3';
import { 
    TagIcon, 
    ShoppingCartIcon, 
    UserIcon, 
    CurrencyDollarIcon,
    ChevronRightIcon
} from '@heroicons/vue/24/outline';

defineProps({
    requests: Object
});

const getStatusStyles = (status) => {
    switch (status) {
        case 'Approved':
        case 'Converted': return 'bg-emerald-50 text-emerald-600 border-emerald-100 shadow-emerald-500/5';
        case 'Draft':
        case 'Pending': return 'bg-amber-50 text-amber-600 border-amber-100 shadow-amber-500/5';
        case 'Rejected': return 'bg-rose-50 text-rose-600 border-rose-100 shadow-rose-500/5';
        default: return 'bg-slate-50 text-slate-400 border-slate-100';
    }
};
</script>

<template>
    <div class="space-y-6 font-outfit">
        <!-- Procurement Registry Terminal -->
        <div class="bg-white rounded-[2rem] border border-slate-100 shadow-2xl shadow-slate-200/40 overflow-hidden relative min-h-[400px]">
            <div class="px-8 py-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                <div>
                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-[0.2em] flex items-center gap-3">
                        Procurement Log
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-rose-50 text-rose-600 border border-rose-100 uppercase tracking-widest">Active Orders</span>
                    </h3>
                </div>
                <button class="h-10 px-6 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.2em] shadow-xl shadow-slate-200 flex items-center justify-center gap-2 hover:bg-rose-600 transition-all active:scale-95 group">
                    <ShoppingCartIcon class="h-4 w-4 group-hover:rotate-12 transition-transform" />
                    <span>Initiate Order</span>
                </button>
            </div>

            <!-- Desktop View -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-slate-900 border-b border-slate-800">
                            <th class="px-6 py-5 text-left w-40">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">PO Reference</span>
                            </th>
                            <th class="px-6 py-5 text-left">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Supply Source</span>
                            </th>
                            <th class="px-6 py-5 text-left">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Engagement Scope</span>
                            </th>
                            <th class="px-6 py-5 text-left">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Resource Load</span>
                            </th>
                            <th class="px-6 py-5 text-center w-32">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Status</span>
                            </th>
                            <th class="px-6 py-5 text-right w-40">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Architect</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="pr in requests.data" :key="pr.id" class="group hover:bg-rose-50/20 transition-all duration-300 cursor-pointer">
                            <td class="px-6 py-6 font-black text-sm text-indigo-600 font-mono tracking-tighter shadow-inner px-2 py-1 bg-slate-50 rounded-lg group-hover:bg-white border border-transparent group-hover:border-slate-100 transition-all">
                                {{ pr.po_number }}
                            </td>
                            <td class="px-6 py-6">
                                <div class="text-base font-black text-slate-900 uppercase tracking-tight group-hover:text-rose-600 transition-colors">{{ pr.vendor?.name || 'GENERIC_VENDOR_NODE' }}</div>
                                <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1.5 opacity-60">Verified Supply Chain</div>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full bg-slate-300 group-hover:bg-rose-400 transition-colors"></div>
                                    <span class="text-sm font-black text-slate-500 uppercase tracking-widest">{{ pr.items?.length || 0 }} Distinct Nodes</span>
                                </div>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex items-center gap-2">
                                     <CurrencyDollarIcon class="h-3.5 w-3.5 text-emerald-500" />
                                     <span class="text-base font-black text-slate-900 tabular-nums">INR {{ Number(pr.total_cost).toLocaleString() }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-6 text-center">
                                <span class="px-4 py-1.5 text-xs font-black rounded-full border uppercase tracking-widest transition-all shadow-sm"
                                      :class="getStatusStyles(pr.status)">
                                  {{ pr.status }}
                                </span>
                            </td>
                            <td class="px-6 py-6 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <div class="text-right">
                                        <div class="text-sm font-black text-slate-800 uppercase tracking-tight leading-none">{{ pr.created_by?.name || 'SYS_PROC' }}</div>
                                        <div class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1 opacity-60">Auth Profile</div>
                                    </div>
                                    <div class="w-8 h-8 rounded-lg bg-slate-900 flex items-center justify-center text-white text-sm font-black shadow-lg border-2 border-white uppercase">
                                        {{ pr.created_by?.name ? pr.created_by.name.charAt(0) : 'S' }}
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!requests.data || requests.data.length === 0">
                            <td colspan="6" class="px-6 py-32 text-center grayscale opacity-20">
                                <ShoppingCartIcon class="h-16 w-16 mx-auto mb-4 animate-pulse" />
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.3em]">No Procurement Operations in Queue</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="lg:hidden divide-y divide-slate-50 bg-slate-50/50">
                 <div v-for="pr in requests.data" :key="'mb-'+pr.id" class="p-6 space-y-5 group relative overflow-hidden bg-white hover:bg-slate-50 transition-all active:scale-[0.98]">
                    <div class="flex justify-between items-start relative z-10">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-slate-900 flex items-center justify-center text-rose-400 shadow-xl border-2 border-white group-hover:bg-rose-500 group-hover:text-white transition-all shrink-0">
                                <TagIcon class="h-6 w-6" />
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight leading-none mb-2 truncate">{{ pr.vendor?.name || 'GENERIC_VENDOR' }}</h4>
                                <span class="text-sm font-black text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100 font-mono tracking-tighter">{{ pr.po_number }}</span>
                            </div>
                        </div>
                        <span class="px-3 py-1 text-xs font-black rounded-full border uppercase tracking-widest shadow-sm shrink-0" :class="getStatusStyles(pr.status)">
                            {{ pr.status }}
                        </span>
                    </div>

                    <div class="bg-slate-50/80 p-4 rounded-2xl border border-slate-100 group-hover:bg-white transition-all relative z-10 shadow-inner flex justify-between items-center">
                        <div class="space-y-1">
                            <span class="text-xs font-black text-slate-400 uppercase tracking-widest block">Resource Load</span>
                            <div class="text-[14px] font-black text-slate-900 tabular-nums leading-none italic">INR {{ Number(pr.total_cost).toLocaleString() }}</div>
                        </div>
                        <div class="text-right space-y-1 pl-4 border-l border-slate-200">
                             <span class="text-xs font-black text-slate-400 uppercase tracking-widest block text-right">Index</span>
                             <span class="text-sm font-black text-slate-700 uppercase tracking-widest">{{ pr.items?.length || 0 }} Nodes</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-sm font-black text-slate-400 border border-slate-200 uppercase">{{ pr.created_by?.name?.charAt(0) || 'S' }}</div>
                            <span class="text-sm font-black text-slate-500 uppercase tracking-tight">{{ pr.created_by?.name || 'SYS_PROC' }}</span>
                        </div>
                        <ChevronRightIcon class="h-5 w-5 text-slate-300 group-hover:text-rose-500 transition-colors" />
                    </div>
                 </div>
            </div>
        </div>
    </div>
</template>
