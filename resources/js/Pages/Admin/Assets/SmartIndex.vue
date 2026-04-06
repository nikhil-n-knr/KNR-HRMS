<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref, computed } from 'vue';
import { debounce } from 'lodash';
import { 
    ChartBarIcon, 
    BuildingStorefrontIcon as LibraryIcon,
    TableCellsIcon as TableIcon, 
    ShoppingCartIcon, 
    CogIcon,
    ExclamationTriangleIcon,
    MagnifyingGlassIcon,
    CpuChipIcon,
    ServerStackIcon,
    WrenchScrewdriverIcon,
    CubeIcon,
    BanknotesIcon
} from '@heroicons/vue/24/outline';

defineOptions({ layout: MainLayout });

const props = defineProps({
    tab: { type: String, default: 'stats' },
    stats: Object,
    assets: Object,
    vendors: Array,
    procurement: Array,
    categories: Array,
    locations: Array,
    statuses: Array,
    filters: Object
});

// --- Filter Logic ---
const filterForm = ref({
    search: props.filters?.search || '',
    category_id: props.filters?.category_id || '',
    status: props.filters?.status || '',
    location_id: props.filters?.location_id || ''
});

const applyFilters = () => {
    router.get(route('admin.assets.dashboard', { view: 'list' }), filterForm.value, {
        preserveState: true,
        replace: true,
        preserveScroll: true
    });
};

const debouncedSearch = debounce(() => {
    applyFilters();
}, 500);

const resetFilters = () => {
    filterForm.value = { search: '', category_id: '', status: '', location_id: '' };
    applyFilters();
};

// Tab Navigation
const tabs = [
    { id: 'stats', label: 'Pulse & Insights', icon: ChartBarIcon },
    { id: 'vendors', label: 'Vendor Directory', icon: LibraryIcon },
    { id: 'list', label: 'Asset Inventory', icon: TableIcon },
    { id: 'procurement', label: 'Procurement', icon: ShoppingCartIcon },
    { id: 'config', label: 'Configuration', icon: CogIcon }
];

const switchTab = (id) => {
    router.get(route('admin.assets.dashboard'), { view: id }, { preserveState: true, replace: true, only: ['tab', 'stats', 'assets', 'vendors', 'procurement', 'categories', 'filters'] });
};

</script>

<template>
    <Head title="Asset Management Command" />

    <div class="space-y-10 pb-20 font-outfit animate-in fade-in slide-in-from-bottom-5 duration-700">
        <!-- Strategic Header Terminal -->
        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-slate-100 p-8 shadow-2xl shadow-slate-200/40 relative overflow-hidden group">
            <div class="absolute -left-8 -top-8 w-32 h-32 bg-emerald-50 rounded-full blur-2xl group-hover:bg-emerald-100 transition-colors duration-1000"></div>
            
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8 relative z-10">
                <div class="flex items-center gap-6">
                    <div class="w-14 h-14 bg-slate-900 rounded-2xl flex items-center justify-center text-emerald-400 shadow-xl group-hover:rotate-12 transition-transform">
                        <ServerStackIcon class="w-8 h-8" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                            Asset Management Matrix
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-black bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase tracking-widest shadow-sm">Global Tracker</span>
                        </h1>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] mt-1.5 flex items-center gap-2">
                            <CpuChipIcon class="w-4 h-4 text-emerald-500" />
                            Hardware allocation, vendor networks & depreciation protocols
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-4 w-full lg:w-auto">
                    <Link :href="route('admin.assets.bulk-assign')" class="px-6 py-3 bg-white text-slate-500 border border-slate-200 rounded-xl text-sm font-black uppercase tracking-widest hover:text-emerald-600 hover:border-emerald-200 transition-all shadow-sm active:scale-95">Bulk Assignment</Link>
                    <Link :href="route('admin.assets.import.smart')" class="px-6 py-3 bg-white text-slate-500 border border-slate-200 rounded-xl text-sm font-black uppercase tracking-widest hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm active:scale-95">Smart Import</Link>
                    <Link :href="route('admin.assets.create')" class="flex-1 lg:flex-none h-12 px-8 bg-slate-900 text-white rounded-xl text-sm font-black uppercase tracking-[0.3em] shadow-xl hover:bg-emerald-600 transition-all active:scale-95 flex items-center justify-center gap-3 group/add">
                        <CubeIcon class="w-4 h-4 group-hover/add:scale-110 transition-transform" />
                        Initialize Asset
                    </Link>
                </div>
            </div>

            <!-- Enhanced Tactical Tab Bar -->
            <div class="flex items-center gap-2 mt-10 p-1.5 bg-slate-50/80 rounded-2xl border border-slate-100 w-fit overflow-x-auto max-w-full relative z-10 no-scrollbar">
                <button 
                    v-for="t in tabs" 
                    :key="t.id"
                    @click="switchTab(t.id)"
                    class="h-11 px-6 text-sm font-black uppercase tracking-[0.2em] rounded-xl transition-all flex items-center gap-3 relative overflow-hidden group/tab shrink-0"
                    :class="tab === t.id ? 'bg-slate-900 text-white shadow-xl translate-y-[-1px]' : 'text-slate-400 hover:text-slate-600 hover:bg-white transition-all'"
                >
                    <component :is="t.icon" class="w-4 h-4" :class="tab === t.id ? 'text-emerald-400' : 'text-slate-300'" />
                    {{ t.label }}
                    <div v-if="tab === t.id" class="absolute bottom-0 left-0 w-full h-0.5 bg-emerald-400 opacity-50"></div>
                </button>
            </div>
        </div>

        <!-- Dynamic Content Engine -->
        <div class="relative min-h-[500px]">
            
            <!-- Tab 1: Stats & Alerts Overview -->
            <div v-if="tab === 'stats'" class="space-y-8 animate-in fade-in fill-mode-both duration-500">
                <!-- Predictive Maintenance AI Screen -->
                <div v-if="stats.predictive_alerts && stats.predictive_alerts.length" class="bg-indigo-600 rounded-[2.5rem] p-8 md:p-12 text-white shadow-2xl shadow-indigo-500/30 relative overflow-hidden group/ai">
                    <div class="absolute -right-20 -top-20 w-80 h-80 bg-white/10 rounded-full blur-[100px] group-hover/ai:scale-110 transition-transform duration-1000"></div>
                    
                    <div class="flex items-center gap-6 mb-10 relative z-10">
                        <div class="w-16 h-16 bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl flex items-center justify-center text-indigo-200 shadow-xl group-hover/ai:rotate-12 transition-transform">
                            <WrenchScrewdriverIcon class="w-8 h-8" />
                        </div>
                        <div>
                            <h2 class="text-2xl font-black uppercase tracking-tight italic">AI Maintenance Forecast</h2>
                            <p class="text-sm font-black text-indigo-200 uppercase tracking-widest mt-1">Predictive analysis of node deterioration</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 relative z-10">
                        <div v-for="alert in stats.predictive_alerts" :key="alert.asset_id" class="bg-slate-900/50 border border-white/10 backdrop-blur-md p-6 rounded-[2rem] hover:bg-slate-900 transition-colors">
                            <div class="flex justify-between items-start mb-6">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-xs shadow-inner uppercase border border-white/10"
                                    :class="{
                                        'bg-rose-500/20 text-rose-400': alert.risk_level === 'Critical',
                                        'bg-amber-500/20 text-amber-400': alert.risk_level === 'High',
                                        'bg-emerald-500/20 text-emerald-400': alert.risk_level === 'Medium'
                                    }">
                                    <ExclamationTriangleIcon class="w-5 h-5" />
                                </div>
                                <span class="px-3 py-1 rounded-lg text-xs font-black uppercase tracking-widest border border-white/10"
                                    :class="alert.days_until < 0 ? 'bg-rose-500 text-white' : 'bg-white/10 text-slate-300'">
                                    {{ alert.days_until < 0 ? 'Protocol_Overdue' : 'T-Minus ' + alert.days_until + ' Days' }}
                                </span>
                            </div>
                            <h3 class="text-lg font-black text-white uppercase tracking-tight line-clamp-1 mb-2">{{ alert.name }}</h3>
                            <div class="text-sm font-mono text-indigo-300 uppercase tracking-wider">Servicing_ETA: {{ alert.next_service_due }}</div>
                        </div>
                    </div>
                </div>

                <!-- Strategic KPI Matrix -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 group hover:scale-[1.02] transition-all">
                        <span class="text-sm font-black text-slate-400 uppercase tracking-widest block mb-4">Total Assets Network</span>
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center shadow-inner group-hover:rotate-12 transition-transform">
                                <ServerStackIcon class="w-7 h-7" />
                            </div>
                            <span class="text-4xl font-black text-slate-900 tabular-nums">{{ stats.total_assets }}</span>
                        </div>
                    </div>

                    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 group hover:scale-[1.02] transition-all">
                        <span class="text-sm font-black text-slate-400 uppercase tracking-widest block mb-4">Total Network Valuation</span>
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center shadow-inner group-hover:rotate-12 transition-transform">
                                <BanknotesIcon class="w-7 h-7" />
                            </div>
                            <span class="text-3xl font-black text-slate-900 font-mono tracking-tighter">₹{{ (stats.total_value || 0).toLocaleString() }}</span>
                        </div>
                    </div>

                    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 group hover:scale-[1.02] transition-all">
                        <span class="text-sm font-black text-slate-400 uppercase tracking-widest block mb-4">Integrated Vendors</span>
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center shadow-inner group-hover:rotate-12 transition-transform">
                                <LibraryIcon class="w-7 h-7" />
                            </div>
                            <span class="text-4xl font-black text-slate-900 tabular-nums">{{ stats.active_vendors }}</span>
                        </div>
                    </div>

                    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 group hover:scale-[1.02] transition-all relative overflow-hidden">
                        <div v-if="stats.low_health > 0" class="absolute inset-0 bg-amber-500/[0.02] animate-pulse"></div>
                        <span class="text-sm font-black text-slate-400 uppercase tracking-widest block mb-4 relative z-10">Critical Health Nodes</span>
                        <div class="flex items-center gap-4 relative z-10">
                            <div class="w-14 h-14 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center shadow-inner group-hover:rotate-12 transition-transform">
                                <ExclamationTriangleIcon class="w-7 h-7" />
                            </div>
                            <span class="text-4xl font-black text-slate-900 tabular-nums">{{ stats.low_health }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Vendor Directory -->
            <div v-if="tab === 'vendors'" class="animate-in fade-in slide-in-from-right-10 duration-500">
                <div class="flex justify-between items-end mb-8">
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Vendor Syndicate</h2>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-[0.4em] mt-2">External hardware & logistics partners</p>
                    </div>
                    <Link :href="route('admin.vendors.index')" class="text-sm font-black text-emerald-600 uppercase tracking-[0.2em] hover:text-emerald-800 transition-colors flex items-center gap-2">
                        Expand Matrix <span class="text-lg">&rarr;</span>
                    </Link>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="vendor in vendors" :key="vendor.id" class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 relative group overflow-hidden">
                        <div class="absolute -right-12 -top-12 w-40 h-40 bg-slate-50 rounded-full blur-3xl group-hover:bg-indigo-50 transition-colors duration-1000"></div>
                        <div class="relative z-10">
                            <div class="flex items-start justify-between mb-6">
                                <div class="w-14 h-14 bg-slate-900 text-white rounded-2xl flex items-center justify-center font-black text-lg shadow-xl group-hover:bg-indigo-600 transition-colors">
                                    {{ vendor.name[0] }}
                                </div>
                                <span v-if="vendor.msme_reg" class="px-3 py-1 bg-indigo-50 text-indigo-600 text-xs font-black uppercase tracking-[0.2em] rounded border border-indigo-100 shadow-sm">Official_MSME</span>
                            </div>
                            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight mb-1 group-hover:text-indigo-700 transition-colors">{{ vendor.name }}</h3>
                            <p class="text-sm font-black text-slate-400 uppercase tracking-widest opacity-80">{{ vendor.category || 'Standard Node' }}</p>

                            <div class="mt-8 space-y-4">
                                <div class="flex justify-between items-center bg-slate-50 p-3 rounded-xl border border-slate-100">
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-widest">Tax_Vector</span>
                                    <span class="text-base font-mono text-slate-700 font-bold tracking-tighter">{{ vendor.gstin || 'UNVERIFIED' }}</span>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-1 bg-slate-50 p-3 rounded-xl border border-slate-100 text-center">
                                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest block mb-1">TDS Pulse</span>
                                        <span class="text-base font-black text-slate-900 tabular-nums">{{ vendor.tds_rate }}%</span>
                                    </div>
                                    <div class="flex-1 bg-slate-50 p-3 rounded-xl border border-slate-100 text-center">
                                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest block mb-1">Response SLA</span>
                                        <span class="text-base font-black text-emerald-600 tabular-nums">{{ vendor.sla_response_hours }}h</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Asset Inventory List -->
            <div v-if="tab === 'list'" class="animate-in fade-in slide-in-from-left-10 duration-500">
                <!-- Advanced Tactical Filter Array -->
                <div class="bg-slate-900 p-3 rounded-[2rem] shadow-2xl shadow-indigo-500/10 flex flex-col lg:flex-row gap-3 items-center mb-8 relative z-20">
                    <div class="relative w-full lg:w-96 group/search">
                        <MagnifyingGlassIcon class="absolute right-6 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500 group-focus-within/search:text-emerald-400 transition-colors" />
                        <input v-model="filterForm.search" @input="debouncedSearch" type="text" placeholder="Search entity matrices..." class="w-full h-14 bg-white/5 border border-white/10 rounded-2xl pl-6 pr-14 text-base font-black text-white focus:bg-white/10 focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500/50 transition-all uppercase tracking-widest placeholder:text-slate-600">
                    </div>
                    
                    <div class="flex w-full lg:w-auto gap-3">
                        <select v-model="filterForm.category_id" @change="applyFilters" class="h-14 bg-white/5 border border-white/10 text-sm font-black text-slate-300 focus:text-white rounded-2xl px-6 uppercase tracking-widest focus:ring-4 focus:ring-indigo-500/20 appearance-none cursor-pointer hover:bg-white/10 transition-all flex-1">
                            <option value="" class="text-slate-900">Global Categories</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id" class="text-slate-900">{{ cat.name }}</option>
                        </select>
                        <select v-model="filterForm.status" @change="applyFilters" class="h-14 bg-white/5 border border-white/10 text-sm font-black text-slate-300 focus:text-white rounded-2xl px-6 uppercase tracking-widest focus:ring-4 focus:ring-indigo-500/20 appearance-none cursor-pointer hover:bg-white/10 transition-all flex-1">
                            <option value="" class="text-slate-900">All Statuses</option>
                            <option value="Available" class="text-slate-900">Available</option>
                            <option value="Assigned" class="text-slate-900">Assigned</option>
                            <option value="In_Service" class="text-slate-900">In Service</option>
                        </select>
                    </div>
                </div>

                <!-- Strategic Terminal Grid -->
                <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl shadow-slate-200/40 overflow-hidden relative group">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100">
                                    <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Entity Profiling</th>
                                    <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Assigned Syndicate</th>
                                    <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Operational Status</th>
                                    <th class="px-8 py-6 text-right text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Command</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="asset in assets.data" :key="asset.id" class="group/row hover:bg-slate-50/80 transition-all duration-300">
                                    <td class="px-8 py-7">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center text-white shadow-lg group-hover/row:bg-emerald-600 transition-colors">
                                                <CubeIcon class="w-6 h-6" />
                                            </div>
                                            <div>
                                                <div class="text-lg font-black text-slate-900 uppercase tracking-tight group-hover/row:text-emerald-700 transition-colors">{{ asset.name }}</div>
                                                <div class="text-sm font-mono text-slate-400 mt-1 uppercase tracking-widest italic opacity-80">{{ asset.serial_number }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-7">
                                        <div class="text-base font-black text-slate-500 uppercase tracking-widest px-4 py-2 bg-white border border-slate-100 rounded-xl inline-block shadow-sm">
                                            {{ asset.vendor?.name || 'UNLINKED_NODE' }}
                                        </div>
                                    </td>
                                    <td class="px-8 py-7">
                                        <span class="px-4 py-1.5 inline-flex text-xs font-black tracking-[0.2em] rounded border shadow-sm uppercase" 
                                            :class="{
                                                'bg-emerald-50 text-emerald-600 border-emerald-100': asset.status === 'Available',
                                                'bg-indigo-50 text-indigo-600 border-indigo-100': asset.status === 'Assigned',
                                                'bg-amber-50 text-amber-600 border-amber-100': asset.status === 'In_Service' || asset.status === 'Maintenance'
                                            }">
                                            <span v-if="asset.status === 'Available'" class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2 mt-0.5 animate-pulse"></span>
                                            {{ asset.status.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-7 text-right">
                                        <div class="flex justify-end gap-3 opacity-0 group-hover/row:opacity-100 transition-all translate-x-4 group-hover/row:translate-x-0">
                                            <Link :href="route('admin.assets.show', asset.id)" class="h-10 px-6 bg-slate-900 text-white rounded-xl text-sm font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-xl active:scale-95 flex items-center gap-2">
                                                Inspect
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="assets.data.length === 0">
                                    <td colspan="4" class="px-8 py-32 text-center grayscale opacity-30">
                                        <ServerStackIcon class="w-20 h-20 mx-auto text-slate-300 mb-6 animate-pulse" />
                                        <p class="text-sm font-black uppercase tracking-[0.4em]">Zero tracking entities isolated globally</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab 4: Procurement Bridge -->
            <div v-if="tab === 'procurement'" class="animate-in fade-in slide-in-from-bottom-5 duration-500">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Financial Acquisition Stream</h2>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-[0.4em] mt-2">Active purchase orders & resource pipelines</p>
                    </div>
                    <Link :href="route('procurement.index')" class="h-12 px-6 bg-emerald-50 text-emerald-600 rounded-xl text-sm font-black uppercase tracking-widest hover:bg-emerald-600 hover:text-white transition-all shadow-sm flex items-center gap-2 active:scale-95 border border-emerald-100">
                        Synthesize New PO
                    </Link>
                </div>

                <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl shadow-slate-200/40 overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-indigo-950 border-b border-indigo-900">
                                <th class="px-8 py-6 text-sm font-black text-indigo-200/50 uppercase tracking-[0.2em]">Transaction Registry</th>
                                <th class="px-8 py-6 text-sm font-black text-indigo-200/50 uppercase tracking-[0.2em]">Designated Vendor</th>
                                <th class="px-8 py-6 text-sm font-black text-indigo-200/50 uppercase tracking-[0.2em]">Capital Impact</th>
                                <th class="px-8 py-6 text-sm font-black text-indigo-200/50 uppercase tracking-[0.2em]">State</th>
                                <th class="px-8 py-6 text-right text-sm font-black text-indigo-200/50 uppercase tracking-[0.2em]">Protocol</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-indigo-50">
                            <tr v-for="po in procurement" :key="po.id" class="group/prow hover:bg-indigo-50/50 transition-all duration-300">
                                <td class="px-8 py-7">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-500 shadow-inner group-hover/prow:bg-indigo-500 group-hover/prow:text-white transition-colors">
                                            <BanknotesIcon class="w-5 h-5" />
                                        </div>
                                        <span class="font-mono text-base font-black text-slate-900 uppercase tracking-tighter">{{ po.po_number || 'SYSTEM_DRAFT' }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-7 text-base font-black text-slate-600 uppercase tracking-tight">{{ po.vendor?.name }}</td>
                                <td class="px-8 py-7">
                                    <span class="text-lg font-black text-slate-900 font-mono tracking-tighter bg-white px-3 py-1.5 rounded-lg border border-slate-100 shadow-sm inline-block">
                                        ${{ (po.total_cost || 0).toLocaleString() }}
                                    </span>
                                </td>
                                <td class="px-8 py-7">
                                    <span class="px-3 py-1 bg-slate-100 text-xs font-black text-slate-500 uppercase tracking-[0.2em] rounded shadow-sm border border-slate-200">
                                        {{ po.status }}
                                    </span>
                                </td>
                                <td class="px-8 py-7 text-right">
                                    <Link v-if="po.status !== 'Converted'" method="post" as="button" :href="route('procurement.convert', po.id)" class="h-10 px-5 bg-slate-900 text-white rounded-xl text-sm font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-xl active:scale-95 inline-flex items-center gap-2 group/conv">
                                        Compile Assets
                                        <CubeIcon class="w-4 h-4 text-emerald-400 group-hover/conv:scale-110 transition-transform" />
                                    </Link>
                                    <span v-else class="text-sm font-black text-slate-300 uppercase tracking-[0.2em] italic">Transmuted</span>
                                </td>
                            </tr>
                            <tr v-if="!procurement.length">
                                <td colspan="5" class="px-8 py-24 text-center grayscale opacity-30 italic">
                                    <ShoppingCartIcon class="w-16 h-16 mx-auto text-slate-300 mb-6 animate-pulse" />
                                    <p class="text-sm font-black uppercase tracking-[0.4em]">Zero active capital transactions recorded</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 5: Dynamic Configuration Placeholder -->
            <div v-if="tab === 'config'" class="animate-in zoom-in-95 duration-500 bg-white rounded-[3rem] border border-slate-100 p-20 shadow-2xl shadow-slate-200/40 text-center flex flex-col items-center group relative overflow-hidden">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-slate-50 via-transparent to-transparent"></div>
                <div class="relative z-10 flex flex-col items-center">
                    <div class="w-24 h-24 bg-slate-900 rounded-[2rem] flex items-center justify-center text-white shadow-2xl mb-8 group-hover:rotate-180 transition-transform duration-1000">
                        <CogIcon class="w-12 h-12 text-slate-300" />
                    </div>
                    <h3 class="text-3xl font-black text-slate-900 uppercase tracking-tight mb-4">Neural Architecture Config</h3>
                    <div class="h-1.5 w-24 bg-gradient-to-r from-slate-300 to-slate-400 rounded-full mb-8 shadow-sm"></div>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-[0.4em] mb-12 max-w-lg leading-relaxed">
                        Asset categorization nodes, dynamic form properties, and complex depreciation algorithms are administered here. Core integration pending visual sync.
                    </p>
                    
                    <div class="grid gap-3 w-full max-w-sm">
                        <Link v-for="cat in categories" :key="cat.id" :href="route('admin.assets.configurations', { tab: 'attributes', category_id: cat.id })" class="p-5 bg-white border border-slate-100 rounded-2xl flex items-center justify-between shadow-sm hover:shadow-xl hover:border-indigo-400 group/list transition-all active:scale-[0.98]">
                            <div class="flex items-center gap-4">
                                <div class="w-2.5 h-2.5 rounded-full bg-slate-300 group-hover/list:bg-emerald-400 transition-colors shadow-sm"></div>
                                <span class="text-base font-black text-slate-700 uppercase tracking-widest">{{ cat.name }}</span>
                            </div>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] group-hover/list:text-indigo-600 transition-colors">Configure &rarr;</span>
                        </Link>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</template>

<style scoped>
.font-mono {
    font-family: 'JetBrains Mono', monospace;
}
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
