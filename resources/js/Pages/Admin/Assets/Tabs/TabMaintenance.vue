<script setup>
import { Link } from '@inertiajs/vue3';
import { 
    WrenchScrewdriverIcon, 
    QueueListIcon, 
    ExclamationTriangleIcon,
    ChevronRightIcon,
    WrenchIcon
} from '@heroicons/vue/24/outline';

defineProps({
    tickets: Object
});

const getStatusStyles = (status) => {
    switch (status) {
        case 'Fixed':
        case 'Completed': return 'bg-emerald-50 text-emerald-600 border-emerald-100 shadow-emerald-500/5';
        case 'In Repair': return 'bg-amber-50 text-amber-600 border-amber-100 shadow-amber-500/5';
        case 'Reported':
        case 'Vendor Pending': return 'bg-rose-50 text-rose-600 border-rose-100 shadow-rose-500/5';
        case 'Bill Submitted': return 'bg-blue-50 text-blue-600 border-blue-100 shadow-blue-500/5';
        default: return 'bg-slate-50 text-slate-400 border-slate-100';
    }
};
</script>

<template>
    <div class="space-y-6 font-outfit">
        <!-- Maintenance Registry Terminal -->
        <div class="bg-white rounded-[2rem] border border-slate-100 shadow-2xl shadow-slate-200/40 overflow-hidden relative min-h-[400px]">
            <div class="px-8 py-6 border-b border-slate-50 flex flex-col md:flex-row justify-between items-center bg-slate-50/30 gap-4">
                <div>
                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-[0.2em] flex items-center gap-3">
                        Maintenance Pulse
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-blue-50 text-blue-600 border border-blue-100 uppercase tracking-widest">Resource Health</span>
                    </h3>
                </div>
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <Link :href="route('admin.maintenance.board')" class="flex-1 md:flex-none h-10 px-6 bg-white border-2 border-slate-100 text-slate-500 rounded-2xl text-sm font-black uppercase tracking-[0.2em] hover:bg-slate-50 transition-all flex items-center justify-center gap-2 group">
                        <QueueListIcon class="h-4 w-4 group-hover:scale-110 transition-transform" />
                        Kanban Board
                    </Link>
                    <button class="flex-1 md:flex-none h-10 px-6 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.2em] shadow-xl shadow-slate-200 flex items-center justify-center gap-2 hover:bg-blue-600 transition-all active:scale-95 group">
                        <ExclamationTriangleIcon class="h-4 w-4 text-blue-400 group-hover:rotate-12 transition-transform" />
                        Report Node Failure
                    </button>
                </div>
            </div>

            <!-- Desktop View -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-slate-900 border-b border-slate-800">
                            <th class="px-6 py-5 text-left w-64">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Target Hardware</span>
                            </th>
                            <th class="px-6 py-5 text-left">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Class</span>
                            </th>
                            <th class="px-6 py-5 text-left">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Fault Vector</span>
                            </th>
                            <th class="px-6 py-5 text-left">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Reporter Index</span>
                            </th>
                            <th class="px-6 py-5 text-center w-32">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Protocol Status</span>
                            </th>
                            <th class="px-6 py-5 text-right w-40">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Repair Cost</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="ticket in tickets.data" :key="ticket.id" class="group hover:bg-blue-50/20 transition-all duration-300 pointer group cursor-pointer">
                            <td class="px-6 py-6 border-l-4 border-transparent group-hover:border-blue-500 transition-all">
                                <div class="flex items-center gap-4">
                                    <div class="w-11 h-11 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 shadow-inner group-hover:bg-white transition-colors border-2 border-transparent group-hover:border-slate-100">
                                        <WrenchIcon class="h-6 w-6 opacity-30 group-hover:opacity-100 group-hover:animate-bounce" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-base font-black text-slate-900 uppercase tracking-tight group-hover:text-blue-700 transition-colors truncate">{{ ticket.asset?.name || 'UNKNOWN_HARDWARE' }}</div>
                                        <div class="text-sm font-black text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-100 font-mono tracking-tighter mt-1.5 self-start inline-block shadow-sm">#{{ ticket.asset?.asset_code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6 font-black text-sm text-slate-500 uppercase tracking-widest italic opacity-70">
                                {{ ticket.type }}
                            </td>
                            <td class="px-6 py-6">
                                <div class="max-w-xs truncate text-base font-black text-slate-500 uppercase tracking-tight leading-none italic">" {{ ticket.description }} "</div>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex flex-col gap-1.5">
                                    <div class="text-base font-black text-slate-800 uppercase tracking-tight leading-none">{{ ticket.logger?.name || 'SYS_MONITOR' }}</div>
                                    <div class="text-xs font-black text-slate-400 uppercase tracking-widest leading-none">{{ ticket.service_date }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-6 text-center">
                                <span class="px-4 py-1.5 text-xs font-black rounded-full border uppercase tracking-widest transition-all shadow-sm"
                                      :class="getStatusStyles(ticket.status)">
                                  {{ ticket.status }}
                                </span>
                            </td>
                            <td class="px-6 py-6 text-right">
                                <div v-if="ticket.cost > 0" class="text-base font-black text-slate-900 tabular-nums leading-none">INR {{ Number(ticket.cost).toLocaleString() }}</div>
                                <div v-else class="text-sm font-black text-slate-200 uppercase tracking-widest italic opacity-40 leading-none">NO_COST_RECORDED</div>
                            </td>
                        </tr>
                        <tr v-if="!tickets.data || tickets.data.length === 0">
                            <td colspan="6" class="px-6 py-32 text-center grayscale opacity-20">
                                <WrenchScrewdriverIcon class="h-16 w-16 mx-auto mb-4 animate-pulse text-blue-500" />
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.3em]">No Active Hardware Repairs Located</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="lg:hidden divide-y divide-slate-50 bg-slate-50/50">
                 <div v-for="ticket in tickets.data" :key="'mb-'+ticket.id" class="p-6 space-y-5 group relative overflow-hidden bg-white hover:bg-slate-50 transition-all active:scale-[0.98]">
                    <div class="flex justify-between items-start relative z-10">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-slate-900 flex items-center justify-center text-blue-400 shadow-xl border-2 border-white group-hover:bg-blue-600 group-hover:text-white transition-all shrink-0">
                                <WrenchIcon class="h-6 w-6" />
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight leading-none mb-2 truncate">{{ ticket.asset?.name || 'UNKNOWN' }}</h4>
                                <span class="text-sm font-black text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-100 font-mono tracking-tighter self-start inline-block">NODE_ID: {{ ticket.asset?.asset_code }}</span>
                            </div>
                        </div>
                        <span class="px-3 py-1 text-xs font-black rounded-full border uppercase tracking-widest shadow-sm shrink-0" :class="getStatusStyles(ticket.status)">
                            {{ ticket.status }}
                        </span>
                    </div>

                    <div class="bg-indigo-50/20 p-5 rounded-3xl border border-indigo-100 group-hover:bg-white transition-all relative z-10 shadow-inner space-y-4">
                         <div class="flex justify-between items-center border-b border-indigo-100 pb-3">
                             <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Fault Vector</span>
                             <span class="text-sm font-black text-slate-700 uppercase tracking-widest">{{ ticket.type }}</span>
                         </div>
                         <p class="text-base font-black text-slate-500 uppercase tracking-tight leading-normal italic opacity-80">" {{ ticket.description }} "</p>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <div class="flex items-center gap-3">
                             <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-sm font-black text-slate-400 border border-slate-200 uppercase font-mono">{{ ticket.logger?.name?.charAt(0) || 'S' }}</div>
                             <div class="min-w-0">
                                <span class="block text-sm font-black text-slate-700 uppercase tracking-tight truncate leading-none">{{ ticket.logger?.name || 'SYS_MONITOR' }}</span>
                                <span class="block text-xs font-black text-slate-400 uppercase tracking-widest mt-1 opacity-60">Auth Index</span>
                             </div>
                        </div>
                        <div v-if="ticket.cost > 0" class="text-right">
                             <span class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-1 opacity-60">Expended Load</span>
                             <span class="text-base font-black text-slate-900 tabular-nums leading-none">INR {{ Number(ticket.cost).toLocaleString() }}</span>
                        </div>
                        <ChevronRightIcon v-else class="h-5 w-5 text-slate-300 group-hover:text-blue-500 transition-colors" />
                    </div>
                 </div>
            </div>
        </div>
    </div>
</template>
