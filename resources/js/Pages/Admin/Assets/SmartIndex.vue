<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref, computed } from 'vue';
import { debounce } from 'lodash';
import { 
    ChartBarIcon, 
    BuildingStorefrontIcon,
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
    BanknotesIcon,
    InformationCircleIcon,
    AdjustmentsHorizontalIcon,
    BoltIcon,
    SparklesIcon,
    ArrowPathIcon,
    ArrowUpRightIcon,
    CheckCircleIcon,
    ArchiveBoxIcon,
    TagIcon,
    PlusIcon,
    MapPinIcon,
    ChevronRightIcon,
    TrashIcon
} from '@heroicons/vue/24/solid';

defineOptions({ layout: MainLayout });

const props = defineProps({
    tab: { type: String, default: 'stats' },
    stats: Object,
    assets: Object,
    vendors: Array,
    procurement: Array,
    requests: Array,
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
    { id: 'stats', label: 'Quick Look', icon: ChartBarIcon },
    { id: 'requests', label: 'Requests', icon: ArchiveBoxIcon },
    { id: 'vendors', label: 'Store List', icon: LibraryIcon },
    { id: 'list', label: 'Master List', icon: TableIcon },
    { id: 'locations', label: 'Locations', icon: MapPinIcon },
    { id: 'procurement', label: 'Order More', icon: ShoppingCartIcon },
    { id: 'config', label: 'Control', icon: CogIcon }
];

const switchTab = (id) => {
    if (id === 'locations') {
        router.get(route('admin.assets.location-nodes.index'));
        return;
    }
    router.get(route('admin.assets.dashboard'), { view: id }, { preserveState: true, replace: true, only: ['tab', 'stats', 'assets', 'vendors', 'procurement', 'categories', 'filters', 'requests'] });
};

const deleteAsset = (asset) => {
    if (confirm(`Delete asset '${asset.name}'?`)) {
        router.delete(route('admin.assets.destroy', asset.id));
    }
};

const rejectRequest = (request) => {
    const reason = prompt('Enter rejection reason:');
    if (!reason) {
        return;
    }

    router.post(route('admin.assets.asset-requests.reject', request.id), {
        rejection_reason: reason
    });
};

</script>

<template>
    <Head title="Master List" />

    <div class="space-y-10 pb-20 font-outfit animate-in fade-in duration-700 bg-slate-50/50 -m-8 p-8 min-h-screen">
        <!-- Strategic Header Terminal -->
        <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm relative overflow-hidden group">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8 relative z-10 text-left">
                <div class="flex items-center gap-6">
                    <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shrink-0">
                        <ServerStackIcon class="w-8 h-8" />
                    </div>
                    <div>
                        <div class="flex items-center gap-4">
                            <h1 class="text-3xl font-black text-slate-900 tracking-tight uppercase leading-none">Asset Matrix</h1>
                            <span class="px-3 py-1 bg-indigo-50 text-indigo-600 text-[10px] font-bold uppercase tracking-widest rounded-lg border border-indigo-100 leading-none">Global View</span>
                        </div>
                        <p class="text-xs font-semibold text-slate-400 mt-2 flex items-center gap-2">
                             Manage IT infrastructure, fixed assets & supply networks
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-4 w-full lg:w-auto">
                    <Link :href="route('admin.assets.bulk-assign')" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-slate-50 transition-all active:scale-95">Mass Assign</Link>
                    <Link :href="route('admin.assets.import.smart')" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-slate-50 transition-all active:scale-95">Smart Import</Link>
                    <Link :href="route('admin.assets.create')" class="h-12 px-8 bg-indigo-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-md hover:bg-indigo-700 transition-all active:scale-95 flex items-center justify-center gap-3">
                        <PlusIcon class="w-4 h-4" />
                        Initialize Node
                    </Link>
                </div>
            </div>

            <div class="mt-8 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 p-5 bg-slate-50 border border-slate-200 rounded-2xl">
                <div class="text-left">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Asset Lifecycle</p>
                    <div class="mt-2 flex flex-wrap items-center gap-2 text-[10px] font-bold uppercase tracking-widest">
                        <span class="px-3 py-1 rounded-lg bg-slate-100 border border-slate-200 text-slate-700">Draft</span>
                        <span class="text-slate-300">-></span>
                        <span class="px-3 py-1 rounded-lg bg-emerald-50 border border-emerald-100 text-emerald-700">Available</span>
                        <span class="text-slate-300">-></span>
                        <span class="px-3 py-1 rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-700">Assigned</span>
                        <span class="text-slate-300">-></span>
                        <span class="px-3 py-1 rounded-lg bg-amber-50 border border-amber-100 text-amber-700">In Service</span>
                        <span class="text-slate-300">-></span>
                        <span class="px-3 py-1 rounded-lg bg-slate-100 border border-slate-200 text-slate-700">Returned</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <Link :href="route('admin.assets.audit.run')" class="h-10 px-4 bg-white border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-600">Blind Audit</Link>
                    <Link :href="route('admin.assets.maintenance.index')" class="h-10 px-4 bg-white border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-600">Maintenance</Link>
                    <Link :href="route('admin.inventory.dashboard', { view: 'list' })" class="h-10 px-4 bg-white border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-600">Store Master List</Link>
                    <Link :href="route('admin.physical-documents.index')" class="h-10 px-4 bg-indigo-600 rounded-lg text-[10px] font-bold uppercase tracking-widest text-white">Physical Docs</Link>
                </div>
            </div>

            <!-- Enhanced Tactical Tab Bar -->
            <div class="flex items-center gap-2 mt-8 p-1.5 bg-slate-100/50 border border-slate-200 rounded-2xl w-fit overflow-x-auto max-w-full relative z-10 no-scrollbar">
                <button 
                    v-for="t in tabs" 
                    :key="t.id"
                    @click="switchTab(t.id)"
                    class="h-10 px-6 text-[10px] font-bold uppercase tracking-widest rounded-xl transition-all flex items-center gap-3 shrink-0"
                    :class="tab === t.id ? 'bg-white text-indigo-600 shadow-sm border border-slate-200' : 'text-slate-500 hover:text-slate-900 hover:bg-white/50'"
                >
                    <component :is="t.icon" class="w-4 h-4" />
                    {{ t.label }}
                </button>
            </div>

            <div v-if="tab === 'requests'" class="animate-in slide-in-from-bottom-4 duration-500">
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-8 py-5 text-[10px] font-bold uppercase tracking-widest text-slate-500">Requester</th>
                                <th class="px-8 py-5 text-[10px] font-bold uppercase tracking-widest text-slate-500">Category</th>
                                <th class="px-8 py-5 text-[10px] font-bold uppercase tracking-widest text-slate-500">Priority</th>
                                <th class="px-8 py-5 text-[10px] font-bold uppercase tracking-widest text-slate-500">Reason</th>
                                <th class="px-8 py-5 text-[10px] font-bold uppercase tracking-widest text-slate-500 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="request in requests" :key="request.id" class="hover:bg-slate-50/50">
                                <td class="px-8 py-5 text-[11px] font-bold text-slate-700">{{ request.user?.name || 'Unknown' }}</td>
                                <td class="px-8 py-5 text-[11px] font-bold text-slate-700">{{ request.asset?.name || request.category?.name || 'N/A' }}</td>
                                <td class="px-8 py-5">
                                    <span class="px-3 py-1 rounded-lg text-[9px] font-bold uppercase tracking-widest bg-slate-100 text-slate-700 border border-slate-200">{{ request.priority || 'Normal' }}</span>
                                </td>
                                <td class="px-8 py-5 text-[11px] text-slate-600">{{ request.reason || 'No reason provided' }}</td>
                                <td class="px-8 py-5 text-right">
                                    <div class="inline-flex gap-2">
                                        <button @click="router.post(route('admin.assets.asset-requests.approve', request.id))" class="h-9 px-3 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-lg text-[9px] font-bold uppercase tracking-widest">Approve</button>
                                        <button @click="rejectRequest(request)" class="h-9 px-3 bg-rose-50 text-rose-700 border border-rose-100 rounded-lg text-[9px] font-bold uppercase tracking-widest">Reject</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!requests || !requests.length">
                                <td colspan="5" class="px-8 py-16 text-center text-slate-400 text-[11px] font-bold uppercase tracking-widest">No pending requests</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Dynamic Content Engine -->
        <div class="relative min-h-[500px]">
            
            <!-- Tab 1: Stats & Alerts -->
            <div v-if="tab === 'stats'" class="space-y-8 animate-in slide-in-from-bottom-4 duration-500">
                <!-- Numbers at a Glance -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="(s, idx) in [
                        { label: 'Resource Fleet', val: stats.total_assets, icon: ServerStackIcon, color: 'text-indigo-600', bg: 'bg-indigo-50' },
                        { label: 'Total Valuation', val: '₹' + (stats.total_value || 0).toLocaleString(), icon: BanknotesIcon, color: 'text-emerald-600', bg: 'bg-emerald-50' },
                        { label: 'Vendor Nodes', val: stats.active_vendors, icon: LibraryIcon, color: 'text-rose-600', bg: 'bg-rose-50' },
                        { label: 'Broken / Lost', val: stats.low_health, icon: ExclamationTriangleIcon, color: 'text-orange-600', bg: 'bg-orange-50' }
                    ]" :key="idx" class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm group hover:border-indigo-200 transition-all flex flex-col justify-between h-48 relative overflow-hidden">
                        <div class="flex justify-between items-start text-left">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 block">{{ s.label }}</span>
                                <span class="text-2xl font-black text-slate-900 tracking-tight tabular-nums truncate block">{{ s.val }}</span>
                            </div>
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center border border-slate-100 shadow-sm" :class="s.bg">
                                <component :is="s.icon" class="w-5 h-5" :class="s.color" />
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-slate-50 flex items-center justify-between">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1.5 leading-none">
                                <div class="w-1 h-1 rounded-full bg-emerald-500"></div>
                                Live Sync
                            </span>
                            <ChevronRightIcon class="w-4 h-4 text-slate-300 group-hover:translate-x-1 transition-transform" />
                        </div>
                    </div>
                </div>

                <!-- AI Repair Forecast -->
                <div v-if="stats.predictive_alerts && stats.predictive_alerts.length" class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-8">
                    <div class="flex items-center gap-6 text-left">
                        <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center shrink-0 border border-indigo-100">
                            <SparklesIcon class="w-8 h-8 text-indigo-600" />
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight leading-none">Forecast</h2>
                            <p class="text-xs font-medium text-slate-500 mt-2">Assets requiring upcoming service attention.</p>
                        </div>
                    </div>
                    <div class="flex gap-4 overflow-x-auto w-full md:w-auto pb-2 no-scrollbar">
                         <div v-for="alert in stats.predictive_alerts" :key="alert.asset_id" class="bg-slate-50 border border-slate-200 px-6 py-4 rounded-2xl flex items-center gap-4 shrink-0 hover:bg-white hover:border-indigo-200 transition-all cursor-pointer group">
                            <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center">
                                <ExclamationTriangleIcon class="w-4 h-4" :class="alert.risk_level === 'Critical' ? 'text-rose-500' : 'text-amber-500'" />
                            </div>
                            <div class="text-left">
                                <div class="text-[10px] font-bold text-slate-900 uppercase tracking-widest truncate w-32 leading-none">{{ alert.name }}</div>
                                <div class="text-[9px] font-medium text-slate-500 mt-1.5 leading-none">Due in {{ alert.days_until }} days</div>
                            </div>
                         </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Vendor List -->
            <div v-if="tab === 'vendors'" class="animate-in slide-in-from-bottom-4 duration-500">
                <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 bg-white p-8 rounded-3xl border border-slate-200 shadow-sm mb-8">
                    <div class="flex items-center gap-6 text-left">
                        <div class="w-14 h-14 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-600 shadow-sm border border-rose-100 shrink-0">
                            <LibraryIcon class="w-8 h-8" />
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight leading-none">Vendor Directory</h2>
                            <p class="text-xs font-semibold text-slate-400 mt-2">Equipment suppliers & verified maintenance partners.</p>
                        </div>
                    </div>
                    <Link :href="route('admin.assets.configurations', { tab: 'vendors' })" class="h-12 px-8 bg-indigo-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-indigo-700 transition-all flex items-center gap-3 active:scale-95 shadow-md">
                        Map New Store
                        <PlusIcon class="w-4 h-4" />
                    </Link>
                </header>
                
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                    <BaseDataTable
                        :data="vendors"
                        :columns="[
                            { key: 'identity', label: 'Store Identity', sortable: true },
                            { key: 'category', label: 'Classification', sortable: true },
                            { key: 'financials', label: 'Financial ID', sortable: false },
                            { key: 'performance', label: 'Performance', sortable: true },
                            { key: 'actions', label: '', sortable: false, align: 'right' }
                        ]"
                        search-placeholder="Search vendors..."
                    >
                        <template #cell-identity="{ row }">
                            <div class="flex items-center gap-4 py-1">
                                <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-400 font-bold text-lg shrink-0">
                                    {{ row.name.charAt(0) }}
                                </div>
                                <div class="text-left">
                                    <div class="text-sm font-black text-slate-950 uppercase tracking-tight leading-none">{{ row.name }}</div>
                                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1 leading-none">Verified Partner</div>
                                </div>
                            </div>
                        </template>

                        <template #cell-category="{ row }">
                            <div class="text-left text-[11px] font-bold text-slate-600 uppercase tracking-widest">
                                {{ row.category || 'General Supplier' }}
                            </div>
                        </template>

                        <template #cell-financials="{ row }">
                            <div class="flex items-center gap-2 text-left">
                                <span class="text-[10px] font-mono font-bold text-slate-500 bg-slate-50 px-3 py-1 rounded-lg border border-slate-100">
                                    {{ row.gstin || 'NO_TAX_ID' }}
                                </span>
                            </div>
                        </template>

                        <template #cell-performance="{ row }">
                            <div class="flex items-center gap-4 justify-start">
                                <div class="flex flex-col items-start gap-1">
                                    <span class="text-xs font-black text-slate-900 tabular-nums leading-none">{{ row.sla_response_hours }}h</span>
                                    <span class="text-[8px] font-bold text-slate-400 uppercase tracking-[0.2em] leading-none mt-1">Response</span>
                                </div>
                                <div class="w-px h-6 bg-slate-100 mx-2"></div>
                                <div class="flex flex-col items-start gap-1">
                                    <span class="text-xs font-black text-emerald-600 uppercase leading-none">Active</span>
                                    <span class="text-[8px] font-bold text-slate-400 uppercase tracking-[0.2em] leading-none mt-1">Status</span>
                                </div>
                            </div>
                        </template>

                        <template #cell-actions="{ row }">
                            <div class="flex justify-end gap-2 pr-4">
                                <button class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest hover:underline">History</button>
                                <button class="text-[10px] font-bold text-slate-400 uppercase tracking-widest hover:text-indigo-600 transition-all">Details</button>
                            </div>
                        </template>
                    </BaseDataTable>
                </div>
            </div>

            <!-- Tab 3: Master List Table -->
            <div v-if="tab === 'list'" class="animate-in slide-in-from-bottom-4 duration-500">
                <!-- Advanced Tactical Filter Array -->
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 flex flex-col xl:flex-row gap-4 items-center mb-8 relative z-20">
                    <div class="relative w-full xl:w-[400px] group/search">
                        <MagnifyingGlassIcon class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within/search:text-indigo-600 transition-colors shrink-0" />
                        <input v-model="filterForm.search" @input="debouncedSearch" type="text" placeholder="Search Master List..." class="w-full h-12 bg-slate-50 border border-slate-200 rounded-xl pl-12 pr-4 text-sm font-bold text-slate-900 focus:bg-white focus:ring-8 focus:ring-indigo-500/5 focus:border-indigo-500 transition-all uppercase tracking-wide placeholder:text-slate-300">
                    </div>
                    
                    <div class="flex flex-col sm:flex-row w-full xl:w-auto gap-4">
                        <div class="relative">
                            <TagIcon class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                            <select v-model="filterForm.category_id" @change="applyFilters" class="h-12 w-full sm:w-48 bg-slate-50 border border-slate-200 text-[10px] font-bold text-slate-600 rounded-xl pl-12 pr-10 uppercase tracking-widest transition-all appearance-none cursor-pointer focus:bg-white focus:border-indigo-500">
                                <option value="">All Categories</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                        <div class="relative">
                            <BoltIcon class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                            <select v-model="filterForm.status" @change="applyFilters" class="h-12 w-full sm:w-48 bg-slate-50 border border-slate-200 text-[10px] font-bold text-slate-600 rounded-xl pl-12 pr-10 uppercase tracking-widest transition-all appearance-none cursor-pointer focus:bg-white focus:border-indigo-500">
                                <option value="">All Statuses</option>
                                <option value="Available">Ready</option>
                                <option value="Assigned">Assigned</option>
                                <option value="In_Service">In Service</option>
                            </select>
                        </div>
                    </div>
                    
                    <button @click="resetFilters" class="h-12 px-6 ml-auto text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-indigo-600 transition-all">Reset Filters</button>
                </div>

                <!-- Strategic Terminal Grid -->
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden min-h-[500px]">
                    <BaseDataTable
                        :data="assets.data"
                        :meta="assets"
                        @page-change="(page) => router.get(route('admin.assets.dashboard', { view: 'list', page }), filterForm, { preserveState: true, preserveScroll: true })"
                        :columns="[
                            { key: 'node', label: 'Matrix Node (Item)', sortable: true },
                            { key: 'location', label: 'Deployment Room', sortable: true },
                            { key: 'source', label: 'Source Channel', sortable: true },
                            { key: 'integrity', label: 'Integrity Status', sortable: true },
                            { key: 'actions', label: '', sortable: false, align: 'right' }
                        ]"
                        search-placeholder="Filter current view..."
                    >
                        <template #cell-node="{ row }">
                            <div class="flex items-center gap-4 py-1 text-left">
                                <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-500 shadow-sm border border-indigo-100 shrink-0">
                                    <CubeIcon class="w-5 h-5" />
                                </div>
                                <div>
                                    <div class="text-sm font-black text-slate-950 uppercase tracking-tight leading-none">{{ row.name }}</div>
                                    <div class="text-[9px] font-mono font-bold text-slate-400 mt-1 uppercase tracking-widest leading-none">SN: {{ row.serial_number || 'UNKNOWN' }}</div>
                                </div>
                            </div>
                        </template>
                        
                        <template #cell-location="{ row }">
                            <div class="flex items-center gap-3 text-left">
                                <MapPinIcon class="w-3.5 h-3.5 text-slate-300" />
                                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-widest leading-none">{{ row.current_location_node?.name || 'Central Store' }}</span>
                            </div>
                        </template>

                        <template #cell-source="{ row }">
                            <div class="flex items-center gap-3 text-left">
                                <BuildingStorefrontIcon class="w-3.5 h-3.5 text-slate-300" />
                                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-widest leading-none">{{ row.vendor?.name || 'In-House' }}</span>
                            </div>
                        </template>

                        <template #cell-integrity="{ row }">
                            <span class="px-4 py-1.5 inline-flex text-[9px] font-bold tracking-widest rounded-lg border uppercase leading-none" 
                                :class="{
                                    'bg-emerald-50 text-emerald-600 border-emerald-100': row.status === 'Available',
                                    'bg-indigo-50 text-indigo-600 border-indigo-100': row.status === 'Assigned',
                                    'bg-amber-50 text-amber-600 border-amber-100': ['In_Service', 'Maintenance', 'In Service'].includes(row.status)
                                }">
                                {{ row.status.replace('_', ' ') }}
                            </span>
                        </template>

                        <template #cell-actions="{ row }">
                            <div class="flex justify-end gap-3 pr-4">
                                <Link :href="route('admin.assets.show', row.id)" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-indigo-700 transition-all active:scale-95 shadow-sm">View</Link>
                                <Link :href="route('admin.assets.label', row.id)" class="px-4 py-2 bg-slate-100 text-slate-700 border border-slate-200 rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-slate-200 transition-all">Label</Link>
                                <button @click="deleteAsset(row)" class="px-4 py-2 bg-rose-50 text-rose-700 border border-rose-100 rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-rose-100 transition-all inline-flex items-center gap-2">
                                    <TrashIcon class="w-3.5 h-3.5" />
                                    Delete
                                </button>
                            </div>
                        </template>
                    </BaseDataTable>
                </div>
            </div>

            <!-- Tab 4: Procurement Pipeline -->
            <div v-if="tab === 'procurement'" class="animate-in fade-in slide-in-from-bottom-5 duration-700">
                <div class="bg-white p-10 rounded-3xl border border-slate-200 shadow-sm mb-10 flex flex-col md:flex-row justify-between items-center gap-10 relative overflow-hidden group">
                    <div class="absolute -right-24 -top-24 w-64 h-64 bg-slate-50 rounded-full blur-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-1000 font-outfit"></div>
                    
                    <div class="flex items-center gap-8 relative z-10">
                        <div class="w-16 h-16 bg-emerald-50 rounded-2xl border border-emerald-100 flex items-center justify-center text-emerald-600 shadow-sm transition-transform group-hover:scale-105 shrink-0">
                            <ShoppingCartIcon class="w-9 h-9" />
                        </div>
                        <div class="text-left">
                            <h2 class="text-3xl font-black text-slate-900 uppercase tracking-tight">Recent Orders</h2>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.4em] mt-3">Verified equipment procurement logs</p>
                        </div>
                    </div>
                    <Link :href="route('procurement.index')" class="h-14 px-10 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.3em] hover:bg-indigo-700 transition-all flex items-center gap-5 active:scale-95 shadow-lg relative z-10 group/btn">
                        <PlusIcon class="w-5 h-5 group-hover/btn:rotate-90 transition-transform" />
                        New Purchase Order
                    </Link>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Order Identifier</th>
                                    <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Store Channel</th>
                                    <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Capital Outlay</th>
                                    <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Protocol Status</th>
                                    <th class="px-10 py-6 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Operation</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="po in procurement" :key="po.id" class="group/prow hover:bg-slate-50 transition-all duration-300">
                                    <td class="px-10 py-8">
                                        <div class="flex items-center gap-6">
                                            <div class="w-12 h-12 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-400 shadow-sm group-hover/prow:bg-indigo-50 group-hover/prow:text-indigo-600 transition-all shrink-0">
                                                <BanknotesIcon class="w-6 h-6" />
                                            </div>
                                            <span class="font-mono text-base font-black text-slate-900 uppercase tracking-tight">{{ po.po_number || 'PLAN_NODE' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-10 py-8 text-sm font-black text-slate-600 uppercase tracking-widest">{{ po.vendor?.name }}</td>
                                    <td class="px-10 py-8 text-center">
                                        <div class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-lg font-black text-slate-900 font-mono tracking-tighter shadow-sm inline-flex items-center gap-3">
                                            <BanknotesIcon class="w-5 h-5 text-emerald-500" />
                                            ₹{{ (po.total_cost || 0).toLocaleString() }}
                                        </div>
                                    </td>
                                    <td class="px-10 py-8 text-center">
                                        <span class="px-4 py-1.5 bg-slate-100 text-[9px] font-black text-slate-500 uppercase tracking-widest rounded-lg border border-slate-200 shadow-sm shrink-0">
                                            {{ po.status }}
                                        </span>
                                    </td>
                                    <td class="px-10 py-8 text-right">
                                        <Link v-if="po.status !== 'Converted'" method="post" as="button" :href="route('procurement.convert', po.id)" class="h-12 px-8 bg-indigo-600 text-white rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-md inline-flex items-center gap-4 group/conv active:scale-95">
                                            Commit to Matrix
                                            <CubeIcon class="w-4 h-4 text-indigo-400 group-hover/conv:scale-125 transition-transform" />
                                        </Link>
                                        <div v-else class="text-[10px] font-black text-emerald-500 uppercase tracking-widest flex items-center justify-end gap-3"><CheckCircleIcon class="w-5 h-5" /> Synchronized</div>
                                    </td>
                                </tr>
                                <tr v-if="!procurement.length">
                                    <td colspan="5" class="px-10 py-32 text-center bg-slate-50/30">
                                        <ShoppingCartIcon class="w-20 h-20 mx-auto text-slate-200 mb-8" />
                                        <p class="text-sm font-black text-slate-400 uppercase tracking-[0.4em]">No active procurement logs found.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab 5: Control Center -->
            <div v-if="tab === 'config'" class="animate-in zoom-in-95 duration-700 bg-white rounded-3xl border border-slate-200 p-12 shadow-sm text-center flex flex-col items-center group relative overflow-hidden">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-indigo-50 via-transparent to-transparent"></div>
                <div class="relative z-10 flex flex-col items-center">
                    <div class="w-20 h-20 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 shadow-sm mb-8 border border-indigo-100 shrink-0">
                        <AdjustmentsHorizontalIcon class="w-10 h-10" />
                    </div>
                    <h3 class="text-3xl font-black text-slate-950 uppercase tracking-tight mb-4">Control Center</h3>
                    <div class="h-1.5 w-20 bg-gradient-to-r from-indigo-400 to-indigo-600 rounded-full mb-8 shadow-inner"></div>
                    <p class="text-sm font-semibold text-slate-500 mb-10 max-w-xl leading-relaxed">
                        Manage categories and item details here. define how items should be grouped and tracked across the company.
                    </p>
                    
                    <div class="grid gap-4 w-full max-w-lg">
                        <Link v-for="cat in categories" :key="cat.id" :href="route('admin.assets.configurations', { tab: 'attributes', category_id: cat.id })" class="p-6 bg-white border border-slate-200 rounded-2xl flex items-center justify-between shadow-sm hover:shadow-md hover:border-indigo-300 group/list transition-all active:scale-[0.98] font-black">
                            <div class="flex items-center gap-4">
                                <div class="w-2.5 h-2.5 rounded-full bg-slate-300 group-hover/list:bg-indigo-500 transition-colors shadow-sm"></div>
                                <span class="text-base font-black text-slate-800 uppercase tracking-tight leading-none">{{ cat.name }}</span>
                            </div>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] group-hover/list:text-indigo-600 transition-colors">Set Up &rarr;</span>
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
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(0,0,0,0.05);
    border-radius: 10px;
}
.shadow-3xl {
    box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.15);
}
</style>
