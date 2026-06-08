<script setup>
import { ref, computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import PremiumModal from '@/Components/PremiumModal.vue';
import { 
    ShoppingCartIcon, 
    ArrowPathIcon, 
    CheckCircleIcon, 
    ClockIcon, 
    TruckIcon,
    CurrencyRupeeIcon as CashIcon,
    PlusIcon,
    TrashIcon,
    InformationCircleIcon,
    BuildingStorefrontIcon,
    BoltIcon,
    SparklesIcon,
    ArchiveBoxArrowDownIcon,
    CubeIcon,
    TagIcon,
    ArrowUpRightIcon,
    BanknotesIcon,
    ChartBarIcon,
    ClipboardDocumentListIcon
} from '@heroicons/vue/24/solid';
import BaseDataTable from '@/Components/BaseDataTable.vue';

const props = defineProps({
    requests: Object,
    vendors: Array
});

const columns = [
    { key: 'po_node', label: 'PO Number', sortable: true },
    { key: 'vendor', label: 'Vendor', sortable: true },
    { key: 'total_cost', label: 'Capital Outlay', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'actions', label: '', sortable: false, align: 'right' }
];

const getStatusStyle = (status) => {
    switch (status) {
        case 'Planning':
        case 'Draft': return 'bg-slate-50 text-slate-400 border-slate-100 italic shadow-sm';
        case 'Ready to Buy':
        case 'Approved': return 'bg-indigo-50 text-indigo-600 border-indigo-100 italic shadow-sm';
        case 'On the Way':
        case 'Ordered': return 'bg-amber-50 text-amber-600 border-amber-100 italic shadow-sm animate-pulse';
        case 'In Stock':
        case 'Received': return 'bg-emerald-50 text-emerald-600 border-emerald-100 italic shadow-sm';
        default: return 'bg-slate-50 text-slate-400 border-slate-100 italic';
    }
};

const formatStatus = (s) => ({
    'Draft': 'Planning',
    'Approved': 'Ready to Buy',
    'Ordered': 'On the Way',
    'Received': 'In Stock',
}[s] || s);
</script>

<template>
    <div class="space-y-8 font-outfit animate-in fade-in slide-in-from-bottom-5 duration-700">
        
        <!-- Logistics Strategic Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div v-for="stat in [
                { label: 'Pending POs', value: requests.data?.length || 0, icon: ClipboardDocumentListIcon, color: 'text-rose-600', bg: 'bg-rose-50' },
                { label: 'Capital Outlay', value: '₹' + (requests.data?.reduce((acc, r) => acc + (r.total_cost || 0), 0) || 0).toLocaleString(), icon: BanknotesIcon, color: 'text-emerald-600', bg: 'bg-emerald-50' },
                { label: 'Supply Health', value: '94%', icon: ChartBarIcon, color: 'text-indigo-600', bg: 'bg-indigo-50' },
                { label: 'Incoming Stock', value: requests.data?.filter(r => r.status === 'Ordered').length || 0, icon: TruckIcon, color: 'text-amber-600', bg: 'bg-amber-50' }
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

        <!-- Procurement Overview -->
        <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col relative z-10 transition-all duration-700">
            <header class="px-8 py-8 border-b border-slate-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-8 bg-slate-50/50">
                <div class="flex items-center gap-6 text-left">
                    <div class="w-14 h-14 bg-rose-600 rounded-xl flex items-center justify-center text-white shadow-lg shrink-0">
                        <ShoppingCartIcon class="w-7 h-7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-rose-600 leading-none">Procurement</p>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight mt-1 uppercase">Restock Queue</h3>
                        <p class="text-xs font-semibold text-slate-400 mt-2">Track purchase orders and inbound stock.</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                     <Link :href="route('procurement.index')" class="h-12 px-6 bg-slate-900 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-md hover:bg-indigo-600 transition-all flex items-center gap-3 active:scale-95 group/btn">
                        <BoltIcon class="w-4 h-4 text-indigo-400 group-hover/btn:rotate-180 transition-transform duration-700" />
                        Open Procurement
                     </Link>
                </div>
            </header>

            <BaseDataTable
                :rows="requests.data"
                :columns="columns"
                search-placeholder="Search purchase orders"
                class="flex-1"
            >
                <template #cell-po_node="{ row }">
                    <div class="flex items-center gap-5">
                        <div class="w-10 h-10 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-center text-slate-400 group-hover:bg-rose-50 group-hover:text-rose-600 transition-all shadow-sm shrink-0">
                            <ArchiveBoxArrowDownIcon class="w-5 h-5" />
                        </div>
                        <div class="text-left">
                            <div class="text-[13px] font-black text-slate-900 uppercase tracking-tight group-hover:text-rose-700 transition-colors leading-none">{{ row.po_number || 'DRAFT PO' }}</div>
                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1.5 opacity-60 leading-none font-mono">PO ID: {{ String(row.id).padStart(5, '0') }}</div>
                        </div>
                    </div>
                </template>

                <template #cell-vendor="{ row }">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-slate-50 border border-slate-100 rounded-lg flex items-center justify-center text-rose-500 shadow-sm group-hover:bg-rose-600 group-hover:text-white transition-all shrink-0">
                             <BuildingStorefrontIcon class="w-4 h-4" />
                        </div>
                        <div class="text-left">
                            <span class="text-[11px] font-bold text-slate-900 uppercase tracking-widest leading-none block">{{ row.vendor?.name || 'Not Selected' }}</span>
                            <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-1 block">Approved Vendor</span>
                        </div>
                    </div>
                </template>

                <template #cell-total_cost="{ row }">
                    <div class="flex items-center gap-3">
                         <div class="p-1 px-2 bg-emerald-50 rounded-lg border border-emerald-100 text-[10px] font-bold text-emerald-600 leading-none shrink-0">₹</div>
                         <div class="text-left">
                            <span class="text-[15px] font-black text-slate-900 tabular-nums leading-none tracking-tight">{{ (row.total_cost || 0).toLocaleString() }}</span>
                            <span class="text-[8px] font-bold text-slate-400 block uppercase tracking-widest mt-0.5">Capital Outlay</span>
                         </div>
                    </div>
                </template>

                <template #cell-status="{ row }">
                    <div :class="getStatusStyle(formatStatus(row.status))" class="px-4 py-2 border rounded-xl text-[9px] font-bold uppercase tracking-widest shadow-sm flex items-center gap-2.5 w-fit">
                        <div class="w-1.5 h-1.5 rounded-full" :class="row.status === 'Received' ? 'bg-emerald-500 animate-pulse' : 'bg-current'"></div>
                        {{ formatStatus(row.status).toUpperCase() }}
                    </div>
                </template>

                <template #cell-actions="{ row }">
                    <div class="flex justify-end gap-3 opacity-0 group-hover/row:opacity-100 transition-all -translate-x-2 group-hover:translate-x-0">
                                 <Link :href="route('procurement.index')" class="w-9 h-9 bg-white border border-slate-200 rounded-lg flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm active:scale-90">
                                     <ArrowUpRightIcon class="w-4 h-4" />
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
