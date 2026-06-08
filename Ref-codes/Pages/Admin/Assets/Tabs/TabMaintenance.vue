<script setup>
import { Link } from '@inertiajs/vue3';
import { 
    WrenchScrewdriverIcon, 
    ArrowPathIcon, 
    CheckCircleIcon, 
    ClockIcon,
    ExclamationCircleIcon,
    WrenchIcon,
    ShieldCheckIcon,
    WalletIcon,
    ArrowRightIcon,
    ArchiveBoxIcon,
    ArchiveBoxArrowDownIcon,
    CubeIcon,
    BoltIcon,
    SparklesIcon,
    InformationCircleIcon,
    CpuChipIcon,
    CurrencyDollarIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/solid';
import BaseDataTable from '@/Components/BaseDataTable.vue';

const props = defineProps({
    tickets: Object
});

const columns = [
    { key: 'asset', label: 'Asset', sortable: true },
    { key: 'reporter', label: 'Reported By', sortable: true },
    { key: 'status', label: 'Health Status', sortable: true },
    { key: 'description', label: 'Issue', sortable: false },
    { key: 'actions', label: '', sortable: false, align: 'right' }
];

const getStatusStyle = (status) => {
    switch (status) {
        case 'Reported': return 'bg-rose-50 text-rose-600 border-rose-100 shadow-rose-500/5 animate-pulse';
        case 'Vendor Pending': return 'bg-amber-50 text-amber-600 border-amber-100 shadow-amber-500/5';
        case 'In Repair': return 'bg-blue-50 text-blue-600 border-blue-100 shadow-blue-500/5';
        case 'Fixed': return 'bg-emerald-50 text-emerald-600 border-emerald-100 shadow-emerald-500/5';
        case 'Bill Submitted': return 'bg-indigo-50 text-indigo-600 border-indigo-100 shadow-indigo-500/5';
        default: return 'bg-slate-50 text-slate-400 border-slate-100';
    }
};
</script>

<template>
    <div class="space-y-8 font-outfit animate-in fade-in slide-in-from-bottom-5 duration-700">
        
        <!-- Strategic Tactical Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div v-for="stat in [
                { label: 'Open Tickets', value: tickets.data?.length || 0, icon: WrenchScrewdriverIcon, color: 'text-sky-600', bg: 'bg-sky-50' },
                { label: 'Active Fixes', value: tickets.data?.filter(t => t.status === 'In Repair').length || 0, icon: BoltIcon, color: 'text-amber-600', bg: 'bg-amber-50' },
                { label: 'Compliance', value: '98.2%', icon: ShieldCheckIcon, color: 'text-emerald-600', bg: 'bg-emerald-50' },
                { label: 'Repair Budget', value: '$8.4k', icon: WalletIcon, color: 'text-indigo-600', bg: 'bg-indigo-50' }
            ]" :key="stat.label" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm group hover:border-indigo-100 transition-all text-left">
                <div class="flex items-center gap-5">
                    <div :class="[stat.bg, stat.color]" class="w-12 h-12 rounded-xl flex items-center justify-center shadow-sm">
                        <component :is="stat.icon" class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 leading-none">{{ stat.label }}</p>
                        <p class="text-2xl font-black text-slate-900 mt-1 tabular-nums tracking-tight">{{ stat.value }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Maintenance Overview -->
        <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col relative z-10 transition-all duration-700">
            <header class="px-8 py-8 border-b border-slate-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-8 bg-slate-50/50">
                <div class="flex items-center gap-6 text-left">
                    <div class="w-14 h-14 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shrink-0">
                        <WrenchIcon class="w-7 h-7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-indigo-600 leading-none">Maintenance</p>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight mt-1 uppercase">Repair Shop</h3>
                        <p class="text-xs font-semibold text-slate-400 mt-2">Track repair progress and service status.</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                            <Link :href="route('admin.assets.maintenance.index')" class="h-12 px-6 bg-indigo-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-md hover:bg-indigo-700 transition-all flex items-center gap-3 active:scale-95 group/btn">
                        <BoltIcon class="w-4 h-4 text-indigo-400 group-hover/btn:rotate-180 transition-transform duration-700" />
                        Open Workshop
                     </Link>
                </div>
            </header>

            <BaseDataTable
                :rows="tickets.data"
                :columns="columns"
                search-placeholder="Search maintenance tickets"
                class="flex-1"
            >
                <template #cell-asset="{ row }">
                    <div class="flex items-center gap-5">
                        <div class="w-10 h-10 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-center text-slate-400 group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-all shadow-sm shrink-0">
                            <CpuChipIcon class="w-5 h-5" />
                        </div>
                        <div class="text-left">
                            <div class="text-[13px] font-black text-slate-900 uppercase tracking-tight group-hover:text-indigo-700 transition-colors leading-none">{{ row.asset?.name || 'Unknown Asset' }}</div>
                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1.5 opacity-60 leading-none">{{ row.asset?.category?.name || 'Uncategorized' }}</div>
                        </div>
                    </div>
                </template>

                <template #cell-reporter="{ row }">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-slate-50 border border-slate-100 rounded-lg flex items-center justify-center text-[10px] font-bold text-slate-500 shadow-sm shrink-0">
                             {{ row.logger?.name?.charAt(0).toUpperCase() || 'S' }}
                        </div>
                        <div class="text-left">
                            <span class="text-[11px] font-bold text-slate-900 uppercase tracking-widest leading-none block">{{ row.logger?.name || 'System' }}</span>
                            <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-1 block">Reporter</span>
                        </div>
                    </div>
                </template>

                <template #cell-status="{ row }">
                    <div :class="getStatusStyle(row.status || 'Fixed')" class="px-4 py-2 border rounded-xl text-[9px] font-bold uppercase tracking-widest shadow-sm flex items-center gap-2.5 w-fit">
                        <div class="w-1.5 h-1.5 rounded-full" :class="(row.status || 'Fixed') === 'In Repair' ? 'bg-indigo-500 animate-pulse' : 'bg-current'"></div>
                        {{ (row.status || 'Fixed')?.toUpperCase() }}
                    </div>
                </template>

                <template #cell-description="{ row }">
                    <div class="max-w-md text-left">
                        <p class="text-[11px] font-medium text-slate-500 leading-relaxed line-clamp-1 group-hover/row:line-clamp-none transition-all duration-500 group-hover/row:text-slate-900">
                           "{{ row.description || row.type || 'No description available' }}"
                        </p>
                    </div>
                </template>

                <template #cell-actions="{ row }">
                    <div class="flex justify-end gap-3 opacity-0 group-hover/row:opacity-100 transition-all -translate-x-2 group-hover:translate-x-0">
                         <Link :href="route('admin.assets.maintenance.index')" class="w-9 h-9 bg-white border border-slate-200 rounded-lg flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm active:scale-90">
                            <ArrowRightIcon class="w-4 h-4" />
                         </Link>
                    </div>
                </template>
            </BaseDataTable>
        </section>
    </div>
</template>

<style scoped>
.shadow-3xl {
    box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.08);
}
</style>

<style scoped>
.shadow-3xl {
    box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.08);
}
</style>
