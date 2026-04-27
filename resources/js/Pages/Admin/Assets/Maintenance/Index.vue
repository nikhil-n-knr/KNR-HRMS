<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    WrenchScrewdriverIcon, 
    ClockIcon, 
    CheckCircleIcon, 
    XCircleIcon,
    ExclamationCircleIcon,
    UserIcon,
    InformationCircleIcon,
    ArrowPathIcon,
    BanknotesIcon,
    InboxIcon,
    BoltIcon,
    ShieldCheckIcon,
    WalletIcon,
    ArrowLeftIcon,
    SparklesIcon,
    ArchiveBoxIcon,
    CubeIcon,
    CurrencyRupeeIcon,
    MapPinIcon,
    MagnifyingGlassIcon,
    TagIcon,
    FunnelIcon,
    AdjustmentsHorizontalIcon
} from '@heroicons/vue/24/solid';
import { debounce } from 'lodash';

defineOptions({ layout: MainLayout });

const props = defineProps({
    board: Object, // { 'Reported': [], 'In Repair': [] ... }
    stats: Object,
    filters: Object,
    categories: Array,
    locations: Array
});

const filterForm = ref({
    search: props.filters?.search || '',
    category_id: props.filters?.category_id || '',
    location_id: props.filters?.location_id || ''
});

const applyFilters = () => {
    router.get(route('admin.assets.maintenance.index'), filterForm.value, {
        preserveState: true,
        replace: true,
        preserveScroll: true
    });
};

const debouncedSearch = debounce(() => {
    applyFilters();
}, 500);

const resetFilters = () => {
    filterForm.value = { search: '', category_id: '', location_id: '' };
    applyFilters();
};

const statuses = [
    { key: 'Repair', label: 'Repair', icon: WrenchScrewdriverIcon, shadow: 'shadow-rose-100', color: 'text-rose-600', dot: 'bg-rose-500', bg: 'bg-rose-50' },
    { key: 'Upgrade', label: 'Upgrade', icon: ArrowPathIcon, shadow: 'shadow-amber-100', color: 'text-amber-600', dot: 'bg-amber-500', bg: 'bg-amber-50' },
    { key: 'Routine_Service', label: 'Routine Service', icon: ShieldCheckIcon, shadow: 'shadow-blue-100', color: 'text-blue-600', dot: 'bg-blue-500', bg: 'bg-blue-50' },
];

const draggedItem = ref(null);

const onDragStart = (event, item) => {
    draggedItem.value = item;
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.dropEffect = 'move';
};

const onDrop = (event, type) => {
    const item = draggedItem.value;
    if (!item) return;

    if (item.type === type) return; // No change

    router.post(route('admin.assets.maintenance.update-status', item.id), {
        type: type
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // Success
        }
    });

    draggedItem.value = null;
};
</script>

<template>
    <Head title="Repair Shop" />

    <div class="h-screen flex flex-col bg-slate-50 font-outfit overflow-hidden -m-8 p-12 relative text-left">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-indigo-500/5 via-transparent to-transparent pointer-events-none"></div>

        <!-- Strategic Header Terminal -->
        <div class="bg-white px-10 py-8 flex flex-shrink-0 justify-between items-center z-10 relative overflow-hidden rounded-3xl border border-slate-200 mb-10 shadow-sm">
            <div class="absolute -right-32 -top-32 w-80 h-80 bg-indigo-50 rounded-full blur-[100px]"></div>
            
            <div class="relative z-10 flex items-center gap-8">
                <Link :href="route('admin.assets.dashboard', { view: 'list' })" class="w-12 h-12 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm active:scale-90 shrink-0">
                    <ArrowLeftIcon class="w-6 h-6" />
                </Link>
                <div class="text-left">
                    <div class="flex items-center gap-6">
                        <h1 class="text-3xl font-black text-slate-900 uppercase tracking-tight leading-none">Repair Shop</h1>
                        <div class="group/tooltip relative flex items-center">
                            <InformationCircleIcon class="w-6 h-6 text-indigo-400 cursor-help opacity-70 hover:opacity-100 transition-opacity" />
                            <div class="absolute left-full ml-6 top-1/2 -translate-y-1/2 w-80 bg-slate-900 text-white text-[11px] font-bold px-6 py-4 rounded-2xl opacity-0 invisible group-hover/tooltip:opacity-100 group-hover/tooltip:visible transition-all shadow-xl z-50 pointer-events-none border border-white/10 leading-relaxed">
                                Global Maintenance & Health Monitoring Terminal. Track damaged resources as they transition through repair stages.
                            </div>
                        </div>
                    </div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2.5 leading-none px-1">Resource Restoration & Lifecycle Management</p>
                </div>
            </div>
            
            <div class="relative z-10 flex gap-6">
                <div class="px-6 py-3 bg-white border border-slate-200 rounded-2xl flex flex-col items-end shadow-sm">
                    <span class="text-slate-400 block text-[9px] font-bold uppercase tracking-widest mb-1 leading-none font-outfit">Active Matrix</span>
                    <span class="text-xl font-black text-slate-900 font-mono tracking-tight leading-none">{{ stats.total_active }} Units</span>
                </div>
                <div class="px-6 py-3 bg-emerald-50 border border-emerald-100 rounded-2xl flex flex-col items-end shadow-sm">
                    <span class="text-emerald-600 block text-[9px] font-bold uppercase tracking-widest mb-1 leading-none font-outfit">Total Spend</span>
                    <span class="text-xl font-black text-slate-900 font-mono tracking-tight leading-none">₹{{ stats.total_cost?.toLocaleString() || 0 }}</span>
                </div>
            </div>
        </div>
        
        <!-- Advanced Tactical Filter Array -->
        <div class="bg-white/80 backdrop-blur-xl p-6 rounded-3xl border border-slate-200 flex flex-col xl:flex-row gap-4 items-center mb-10 z-20 relative shadow-sm">
            <div class="relative w-full xl:w-[400px] group/search">
                <MagnifyingGlassIcon class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within/search:text-indigo-600 transition-colors shrink-0" />
                <input v-model="filterForm.search" @input="debouncedSearch" type="text" placeholder="Search Repair Shop..." class="w-full h-12 bg-slate-50 border border-slate-200 rounded-xl pl-12 pr-4 text-sm font-bold text-slate-900 focus:bg-white focus:ring-8 focus:ring-indigo-500/5 focus:border-indigo-500 transition-all uppercase tracking-wide placeholder:text-slate-300">
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
                    <MapPinIcon class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                    <select v-model="filterForm.location_id" @change="applyFilters" class="h-12 w-full sm:w-48 bg-slate-50 border border-slate-200 text-[10px] font-bold text-slate-600 rounded-xl pl-12 pr-10 uppercase tracking-widest transition-all appearance-none cursor-pointer focus:bg-white focus:border-indigo-500">
                        <option value="">All Locations</option>
                        <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                    </select>
                </div>
            </div>
            
            <button @click="resetFilters" class="h-12 px-6 ml-auto text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-indigo-600 transition-all flex items-center gap-2 active:scale-95">
                <ArrowPathIcon class="w-4 h-4" />
                Reset Protocol
            </button>
        </div>

        <!-- Kanban Board Hub -->
        <div class="flex-1 flex gap-8 overflow-x-auto no-scrollbar pb-8 relative z-10 px-2">
            <div 
                v-for="status in statuses" 
                :key="status.key" 
                @dragover.prevent 
                @drop="onDrop($event, status.key)"
                class="flex-shrink-0 w-88 bg-white/60 backdrop-blur-xl rounded-3xl border border-slate-200 flex flex-col min-h-0 shadow-sm group/col"
            >
                <!-- Stage Header -->
                <div class="p-8 flex items-center justify-between border-b border-slate-100 relative overflow-hidden group-hover/col:pb-10 transition-all text-left">
                    <div class="flex items-center gap-5">
                        <div class="w-12 h-12 bg-white border border-slate-100 rounded-xl flex items-center justify-center shadow-sm group-hover/col:rotate-6 transition-transform" :class="status.color">
                             <component :is="status.icon" class="w-6 h-6" />
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight leading-none">{{ status.label }}</h3>
                            <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-2 leading-none font-outfit">Stage Protocol Active</p>
                        </div>
                    </div>
                    <div class="px-4 py-1.5 bg-slate-50 border border-slate-100 rounded-lg text-[9px] font-bold text-slate-900 shadow-sm">{{ (board[status.key] || []).length }}</div>
                </div>

                <!-- Stage Cards -->
                <div class="flex-1 overflow-y-auto p-6 space-y-6 no-scrollbar text-left">
                    <div 
                        v-for="item in (board[status.key] || [])" 
                        :key="item.id"
                        draggable="true"
                        @dragstart="onDragStart($event, item)"
                        class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-1 cursor-grab active:cursor-grabbing transition-all duration-500 relative overflow-hidden group/card"
                    >
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_right,_var(--tw-gradient-stops))] from-indigo-50/10 via-transparent to-transparent pointer-events-none"></div>
                        
                        <div class="relative z-10">
                            <div class="flex justify-between items-start mb-6 font-mono">
                                <span class="text-[8px] font-bold text-slate-300 uppercase tracking-widest font-mono">UNIT: #{{ item.id }}</span>
                                <div class="w-8 h-8 bg-slate-50 border border-slate-100 rounded-lg flex items-center justify-center text-slate-200 group-hover/card:bg-slate-900 group-hover/card:text-white transition-all">
                                    <ArrowPathIcon class="w-4 h-4 group-hover/card:rotate-180 transition-transform duration-700" />
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-5 mb-6">
                                <div class="w-11 h-11 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-center text-slate-300 shadow-sm group-hover/card:scale-110 transition-transform shrink-0">
                                     <ArchiveBoxIcon class="w-5 h-5 text-indigo-400/50" />
                                </div>
                                <div class="min-w-0">
                                     <h4 class="text-sm font-black text-slate-900 uppercase tracking-tight leading-none mb-2 truncate">{{ item.asset?.name }}</h4>
                                     <span class="px-3 py-1 bg-slate-50 text-[8px] font-bold text-slate-400 uppercase tracking-widest rounded-lg border border-slate-100">{{ item.asset?.category?.name }}</span>
                                </div>
                            </div>

                            <div v-if="item.cost" class="flex items-center gap-3 bg-emerald-50 p-4 rounded-xl border border-emerald-100 shadow-inner mb-5 transition-all group-hover/card:bg-white">
                                <CurrencyRupeeIcon class="w-5 h-5 text-emerald-500" />
                                <div>
                                    <p class="text-[8px] font-bold text-emerald-600 uppercase tracking-widest mb-0.5 leading-none">Repair Net</p>
                                    <span class="text-lg font-black text-slate-900 font-mono tracking-tight leading-none">₹{{ item.cost.toLocaleString() }}</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-slate-300 px-1">
                                <div class="flex items-center gap-2">
                                     <ClockIcon class="w-3 h-3" />
                                     <span class="text-[8px] font-bold uppercase tracking-widest">{{ new Date(item.service_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                     <MapPinIcon class="w-3 h-3" />
                                     <span class="text-[8px] font-bold uppercase tracking-widest">{{ item.asset?.location?.name || 'External' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.shadow-3xl {
    box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.08);
}
.no-scrollbar::-webkit-scrollbar { display: none; }
</style>
