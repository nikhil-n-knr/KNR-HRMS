<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { 
    ChartBarIcon, 
    TableCellsIcon as TableIcon, 
    ShoppingCartIcon, 
    QrCodeIcon,
    ArchiveBoxIcon,
    PlusIcon,
    ArrowTrendingUpIcon as ChartBarIconAlt,
    ExclamationTriangleIcon,
    ArrowPathIcon,
    RectangleGroupIcon,
    InboxArrowDownIcon,
    ShieldCheckIcon,
    BoltIcon,
    CubeIcon,
    CurrencyRupeeIcon as CashIcon,
    InformationCircleIcon,
    ClockIcon,
    XMarkIcon
} from '@heroicons/vue/24/solid';

defineOptions({ layout: MainLayout });

const props = defineProps({
    tab: { type: String, default: 'stats' },
    stats: Object,
    items: Object,
    procurement: Array
});

const tabs = [
    { id: 'stats', label: 'Overview', icon: ChartBarIcon },
    { id: 'list', label: 'Items', icon: TableIcon },
    { id: 'procurement', label: 'Shopping List', icon: ShoppingCartIcon },
    { id: 'scanner', label: 'Scanner', icon: QrCodeIcon }
];

const switchTab = (id) => {
    if (id === 'scanner') {
        router.visit(route('admin.inventory.scanner'));
    } else {
        router.get(route('admin.inventory.dashboard'), { view: id }, { 
            preserveState: true, 
            replace: true,
            only: ['items', 'stats', 'procurement', 'tab']
        });
    }
};

// Restock Logic
const showRestockModal = ref(false);
const selectedItem = ref({});
const restockForm = useForm({
    quantity: 10,
    unit_cost: 0
});

const openRestock = (item) => {
    selectedItem.value = item;
    restockForm.reset();
    showRestockModal.value = true;
};

const closeRestock = () => {
    showRestockModal.value = false;
    selectedItem.value = {};
};

const submitRestock = () => {
    restockForm.post(route('admin.inventory.add-stock', selectedItem.value.id), {
        onSuccess: () => closeRestock()
    });
};

// Edit Logic
const showEditModal = ref(false);
const editForm = useForm({
    id: null,
    name: '',
    sku: '',
    category: '',
    current_stock: 0,
    min_stock_level: 0,
    unit_cost: 0,
    unit: ''
});

const openEdit = (item) => {
    editForm.id = item.id;
    editForm.name = item.name;
    editForm.sku = item.sku;
    editForm.category = item.category;
    editForm.current_stock = item.current_stock;
    editForm.min_stock_level = item.min_stock_level;
    editForm.unit_cost = item.unit_cost;
    editForm.unit = item.unit;
    showEditModal.value = true;
};

const closeEdit = () => {
    showEditModal.value = false;
    editForm.reset();
};

const submitEdit = () => {
    editForm.put(route('admin.inventory.update', editForm.id), {
        onSuccess: () => closeEdit()
    });
};

// Delete Logic
const deleteItem = (item) => {
    if (confirm(`Are you sure you want to delete ${item.name}? This action cannot be undone.`)) {
        router.delete(route('admin.inventory.destroy', item.id), {
            onSuccess: () => {
                // Success message handled by flash
            }
        });
    }
};
</script>

<template>
    <Head title="Store Management" />

    <div class="h-screen flex flex-col bg-slate-50 font-outfit overflow-hidden -m-8 p-12 relative text-left">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-indigo-500/5 via-transparent to-transparent pointer-events-none"></div>

        <!-- Header -->
        <div class="bg-white px-10 py-8 flex flex-shrink-0 justify-between items-center z-10 relative overflow-hidden rounded-3xl border border-slate-200 mb-10 shadow-sm">
            <div class="absolute -right-32 -top-32 w-80 h-80 bg-indigo-50 rounded-full blur-[100px]"></div>
            
            <div class="relative z-10 flex items-center gap-8">
                <div class="w-12 h-12 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-400 shadow-sm shrink-0">
                    <ArchiveBoxIcon class="w-6 h-6 text-indigo-600" />
                </div>
                <div class="text-left">
                    <div class="flex items-center gap-6">
                        <h1 class="text-3xl font-black text-slate-900 uppercase tracking-tight leading-none">Store Management</h1>
                        <div class="group/tooltip relative flex items-center">
                            <InformationCircleIcon class="w-6 h-6 text-indigo-400 cursor-help opacity-70 hover:opacity-100 transition-opacity" />
                            <div class="absolute left-full ml-6 top-1/2 -translate-y-1/2 w-80 bg-white text-slate-700 text-[11px] font-bold px-6 py-4 rounded-2xl opacity-0 invisible group-hover/tooltip:opacity-100 group-hover/tooltip:visible transition-all shadow-xl z-50 pointer-events-none border border-slate-200 leading-relaxed">
                                Manage small items like pens, paper, and water. Track if you're running low and create lists of what to buy next.
                            </div>
                        </div>
                    </div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2.5 leading-none px-1">Track and manage all small items in the store</p>
                </div>
            </div>

            <div class="flex items-center gap-6 z-10">
                <!-- Tactical Tab Bar -->
                <div class="flex items-center gap-2 p-1.5 bg-slate-50 border border-slate-100 rounded-2xl">
                    <button 
                        v-for="t in tabs" 
                        :key="t.id"
                        @click="switchTab(t.id)"
                        class="h-10 px-6 text-[10px] font-bold uppercase tracking-widest rounded-xl transition-all flex items-center gap-3"
                        :class="tab === t.id ? 'bg-white text-indigo-600 shadow-sm border border-slate-200' : 'text-slate-400 hover:text-slate-600'"
                    >
                        <component :is="t.icon" class="w-4 h-4" />
                        {{ t.label }}
                    </button>
                </div>

                <div class="w-px h-10 bg-slate-100"></div>

                <Link :href="route('admin.inventory.create')" class="h-14 px-8 bg-indigo-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-lg hover:bg-indigo-700 transition-all flex items-center gap-4 active:scale-95">
                    <PlusIcon class="w-4 h-4 text-white" />
                    New Item
                </Link>
            </div>
        </div>

        <section class="bg-white rounded-3xl border border-slate-200 p-6 mb-8 relative z-10">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div class="text-left">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Store Lifecycle</p>
                    <div class="mt-3 flex flex-wrap items-center gap-2 text-[10px] font-bold uppercase tracking-widest">
                        <span class="px-3 py-1 rounded-lg bg-slate-100 text-slate-700 border border-slate-200">Item Created</span>
                        <span class="text-slate-300">-></span>
                        <span class="px-3 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100">Stock Added</span>
                        <span class="text-slate-300">-></span>
                        <span class="px-3 py-1 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100">Consumed</span>
                        <span class="text-slate-300">-></span>
                        <span class="px-3 py-1 rounded-lg bg-rose-50 text-rose-700 border border-rose-100">Low Stock</span>
                        <span class="text-slate-300">-></span>
                        <span class="px-3 py-1 rounded-lg bg-amber-50 text-amber-700 border border-amber-100">Restock Queue</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <Link :href="route('admin.inventory.scanner')" class="h-10 px-4 bg-slate-50 border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-600">Scanner Mode</Link>
                    <Link :href="route('admin.inventory.procurement.restock')" class="h-10 px-4 bg-slate-50 border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-600">Restock Queue</Link>
                    <Link :href="route('admin.assets.dashboard', { view: 'list' })" class="h-10 px-4 bg-slate-50 border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-600">All Assets</Link>
                    <Link :href="route('admin.physical-documents.index')" class="h-10 px-4 bg-indigo-600 rounded-lg text-[10px] font-bold uppercase tracking-widest text-white">Physical Docs</Link>
                </div>
            </div>
        </section>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto no-scrollbar pb-12 relative z-10">
            <!-- Tab 1: Stats Pulse -->
            <div v-if="tab === 'stats'" class="space-y-10 animate-in fade-in duration-500">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div v-for="stat in [
                        { label: 'Different Items', value: stats.total_items, icon: RectangleGroupIcon, color: 'text-indigo-600', bg: 'bg-indigo-50' },
                        { label: 'Weekly Usage', value: stats.velocity + ' Units', icon: ChartBarIconAlt, color: 'text-emerald-600', bg: 'bg-emerald-50' },
                        { label: 'Low Stock', value: stats.low_stock, icon: ExclamationTriangleIcon, color: 'text-rose-600', bg: 'bg-rose-50', pulse: stats.low_stock > 0 }
                    ]" :key="stat.label" class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm group relative overflow-hidden text-left">
                        <div v-if="stat.pulse" class="absolute inset-0 bg-rose-500/[0.02] animate-pulse"></div>
                        <div class="flex items-center justify-between relative z-10">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-3">{{ stat.label }}</span>
                                <span class="text-4xl font-black text-slate-900 tabular-nums leading-none">{{ stat.value }}</span>
                            </div>
                            <div :class="[stat.bg, stat.color]" class="w-14 h-14 rounded-2xl flex items-center justify-center shadow-sm group-hover:scale-105 transition-all">
                                <component :is="stat.icon" class="w-7 h-7" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Strategic Reorder Deck -->
                <div class="bg-white rounded-3xl p-10 shadow-sm border border-slate-200 relative overflow-hidden group/deck text-left">
                    <div class="absolute -right-32 -top-32 w-80 h-80 bg-indigo-50 rounded-full blur-[100px]"></div>
                    
                    <div class="flex items-center justify-between mb-8 relative z-10 border-b border-slate-100 pb-6">
                        <div class="flex items-center gap-6">
                            <div class="w-12 h-12 bg-rose-50 border border-rose-100 rounded-xl flex items-center justify-center text-rose-500">
                                <ExclamationTriangleIcon class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Stock Alerts</h3>
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-2">Items that need immediate restock attention</p>
                            </div>
                        </div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.3em]">Priority Alert View</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 relative z-10">
                        <div v-for="item in stats.alerts" :key="item.id" class="bg-slate-50 border border-slate-200 rounded-2xl p-6 group/item hover:bg-white hover:border-indigo-200 transition-all">
                            <div class="flex flex-col h-full justify-between">
                                <div class="flex items-start justify-between mb-8">
                                    <div class="w-10 h-10 bg-white rounded-xl border border-slate-200 flex items-center justify-center text-slate-500">
                                        <CubeIcon class="w-5 h-5" />
                                    </div>
                                    <div class="text-right flex flex-col items-end gap-2">
                                        <div class="text-[8px] font-bold text-rose-400 tracking-widest uppercase mb-1">Low Stock</div>
                                        <div class="text-sm font-black text-slate-900 tabular-nums">{{ item.current_stock }} / <span class="opacity-40">{{ item.min_stock_level }}</span></div>
                                        <div class="flex gap-2">
                                            <button @click="openEdit(item)" class="text-slate-500 hover:text-indigo-600 transition-colors">
                                                <ArchiveBoxIcon class="w-3.5 h-3.5" />
                                            </button>
                                            <button @click="deleteItem(item)" class="text-slate-500 hover:text-rose-500 transition-colors">
                                                <XMarkIcon class="w-3.5 h-3.5" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-sm font-black text-slate-900 uppercase tracking-tight mb-6 line-clamp-1 truncate">{{ item.name }}</h4>
                                    <button @click="openRestock(item)" class="w-full h-10 bg-indigo-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-indigo-700 transition-all active:scale-95 shadow-sm">
                                        Quick Restock
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-if="!stats.alerts.length" class="col-span-full py-20 flex flex-col items-center gap-6 opacity-20">
                            <ShieldCheckIcon class="w-16 h-16 text-emerald-400 animate-pulse" />
                            <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">All stock levels are healthy</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Master List -->
            <div v-if="tab === 'list'" class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden animate-in fade-in duration-500">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 text-slate-400 uppercase border-b border-slate-100">
                            <tr>
                                <th class="px-8 py-5 text-[10px] font-bold tracking-widest">Item</th>
                                <th class="px-8 py-5 text-[10px] font-bold tracking-widest">Stock Level</th>
                                <th class="px-8 py-5 text-[10px] font-bold tracking-widest">Utilization Velocity</th>
                                <th class="px-8 py-5 text-[10px] font-bold tracking-widest">Unit Specification</th>
                                <th class="px-8 py-5 text-right text-[10px] font-bold tracking-widest">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="item in items.data" :key="item.id" class="group/row hover:bg-slate-50/50 transition-all duration-300">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-5">
                                        <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-300 group-hover/row:bg-indigo-50 group-hover/row:text-indigo-600 transition-all border border-slate-100 shadow-sm">
                                            <CubeIcon class="w-5 h-5" />
                                        </div>
                                        <div>
                                            <div class="text-sm font-black text-slate-900 uppercase tracking-tight group-hover/row:text-indigo-600 transition-colors leading-none mb-2">{{ item.name }}</div>
                                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest font-mono">SKU-{{ String(item.id).padStart(5, '0') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col gap-2.5">
                                        <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-widest pr-4 leading-none mb-1">
                                            <span :class="item.current_stock <= item.min_stock_level ? 'text-rose-600' : 'text-slate-500'">{{ item.current_stock }} Units</span>
                                            <span class="text-slate-300">Min: {{ item.min_stock_level }}</span>
                                        </div>
                                        <div class="w-40 h-1.5 bg-slate-100 rounded-full overflow-hidden border border-slate-200/50">
                                            <div class="h-full rounded-full transition-all duration-1000" 
                                                :style="`width: ${Math.min((item.current_stock / (item.min_stock_level * 2)) * 100, 100)}%`"
                                                :class="item.current_stock <= item.min_stock_level ? 'bg-rose-500' : 'bg-emerald-500'"
                                            ></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div v-if="item.days_remaining < 999">
                                        <div class="px-3 py-1 bg-slate-50 border border-slate-100 rounded-lg text-[9px] font-bold tracking-widest uppercase inline-flex items-center gap-2"
                                            :class="{
                                                'text-rose-600 bg-rose-50 border-rose-100': item.days_remaining <= 7,
                                                'text-amber-600 bg-amber-50 border-amber-100': item.days_remaining > 7 && item.days_remaining <= 30,
                                                'text-indigo-600': item.days_remaining > 30
                                            }">
                                            <ClockIcon class="w-3 h-3" />
                                            {{ item.days_remaining }} Days Remaining
                                        </div>
                                    </div>
                                    <span v-else class="text-[9px] font-bold text-slate-300 uppercase tracking-widest opacity-40">Infinite Supply</span>
                                </td>
                                <td class="px-8 py-6 uppercase text-[10px] font-bold text-slate-500 tracking-widest">{{ item.unit }}</td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end gap-3 opacity-0 group-hover/row:opacity-100 transition-all group-hover/row:translate-x-0 translate-x-4">
                                        <button @click="openRestock(item)" class="h-8 px-4 bg-slate-50 text-slate-400 hover:text-emerald-600 rounded-lg text-[10px] font-bold uppercase tracking-widest transition-all border border-slate-200">Restock</button>
                                        <button @click="openEdit(item)" class="h-8 px-4 bg-slate-50 text-slate-400 hover:text-indigo-600 rounded-lg text-[10px] font-bold uppercase tracking-widest transition-all border border-slate-200">Edit</button>
                                        <button @click="deleteItem(item)" class="h-8 px-4 bg-slate-50 text-slate-400 hover:text-rose-600 rounded-lg text-[10px] font-bold uppercase tracking-widest transition-all border border-slate-200">Delete</button>
                                        <Link :href="route('admin.inventory.show', item.id)" class="h-8 px-4 bg-indigo-600 text-white rounded-lg text-[10px] font-bold uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-sm flex items-center">Logs</Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 3: Restock Queue -->
            <div v-if="tab === 'procurement'" class="animate-in fade-in duration-500">
                <div v-if="procurement && procurement.length > 0" class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 text-slate-400 uppercase border-b border-slate-100">
                            <tr>
                                <th class="px-8 py-5 text-[10px] font-bold tracking-widest">Requirement</th>
                                <th class="px-8 py-5 text-[10px] font-bold tracking-widest">Spec</th>
                                <th class="px-8 py-5 text-[10px] font-bold tracking-widest">Available</th>
                                <th class="px-8 py-5 text-[10px] font-bold tracking-widest">Acquisition Delta</th>
                                <th class="px-8 py-5 text-right text-[10px] font-bold tracking-widest">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="item in procurement" :key="item.id" class="group/prow hover:bg-slate-50/50 transition-all duration-300">
                                <td class="px-8 py-7">
                                    <div class="flex items-center gap-5">
                                        <div class="w-12 h-12 bg-rose-50 rounded-xl flex items-center justify-center text-rose-500 border border-rose-100">
                                            <ArchiveBoxIcon class="w-6 h-6" />
                                        </div>
                                        <div>
                                            <div class="text-sm font-black text-slate-900 uppercase tracking-tight leading-none mb-2">{{ item.name }}</div>
                                            <div class="text-[9px] font-bold text-rose-600 uppercase tracking-widest opacity-60">{{ item.category ?? 'ESSENTIAL' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-7 text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ item.unit }}</td>
                                <td class="px-8 py-7">
                                    <div class="px-3 py-1.5 bg-rose-50 border border-rose-100 text-rose-600 rounded-lg inline-flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></div>
                                        <span class="text-xs font-black">{{ item.current_stock }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-7">
                                    <div class="flex flex-col gap-1 pr-8">
                                        <span class="text-sm font-black text-rose-600 leading-none mb-1">+{{ (item.min_stock_level - item.current_stock).toFixed(1) }} Units</span>
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Min Threshold: {{ item.min_stock_level }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-7 text-right">
                                    <div class="flex items-center justify-end gap-4">
                                        <button @click="openEdit(item)" class="w-10 h-10 flex items-center justify-center text-slate-300 hover:text-indigo-600 transition-colors">
                                            <ArchiveBoxIcon class="w-5 h-5" />
                                        </button>
                                        <button @click="openRestock(item)" class="h-10 px-6 bg-indigo-600 text-white text-[10px] font-bold uppercase tracking-widest rounded-xl shadow-lg hover:bg-indigo-700 transition-all flex items-center gap-3">
                                            <InboxArrowDownIcon class="w-4 h-4 text-white" />
                                            Authorize
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="bg-white rounded-[2.5rem] border border-slate-200 p-24 text-center group hover:bg-indigo-50/30 transition-all duration-700 shadow-sm relative overflow-hidden">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-indigo-500/5 via-transparent to-transparent pointer-events-none"></div>
                    <div class="relative z-10">
                        <div class="w-20 h-20 bg-emerald-50 border border-emerald-100 rounded-3xl flex items-center justify-center shadow-inner mx-auto mb-10 group-hover:scale-110 transition-transform">
                            <ShieldCheckIcon class="w-10 h-10 text-emerald-500" />
                        </div>
                        <h3 class="text-3xl font-black text-slate-900 uppercase tracking-tight mb-4 px-1">Stock Levels Healthy</h3>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.4em] max-w-sm mx-auto leading-loose mb-12">All active units are compliant with minimum safety levels.</p>
                        
                        <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
                            <Link :href="route('admin.inventory.create')" class="h-16 px-10 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.3em] shadow-xl hover:bg-indigo-700 transition-all flex items-center gap-4 active:scale-95 group/btn">
                                <PlusIcon class="w-5 h-5 text-white group-hover/btn:rotate-90 transition-transform duration-500" />
                                Add Item
                            </Link>
                            <button @click="switchTab('list')" class="text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-indigo-600 transition-colors">Open Master List</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Unit Modal -->
        <Modal :show="showEditModal" @close="closeEdit" max-width="4xl">
             <div class="bg-white rounded-3xl overflow-hidden text-left">
                <div class="p-8 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Edit Resource</h3>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1.5">Update item details</p>
                    </div>
                    <button @click="closeEdit" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-rose-500 transition-colors">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <form @submit.prevent="submitEdit" class="p-10 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="col-span-2 space-y-4">
                            <InputLabel value="Name" class="px-2" />
                            <TextInput v-model="editForm.name" type="text" class="w-full" required />
                            <InputError :message="editForm.errors.name" />
                        </div>
                        <div class="space-y-4">
                            <InputLabel value="SKU / ID" class="px-2" />
                            <TextInput v-model="editForm.sku" type="text" class="w-full" />
                            <InputError :message="editForm.errors.sku" />
                        </div>
                        <div class="space-y-4">
                            <InputLabel value="Category" class="px-2" />
                            <TextInput v-model="editForm.category" type="text" class="w-full" />
                            <InputError :message="editForm.errors.category" />
                        </div>
                        <div class="space-y-4">
                            <InputLabel value="Initial Presence (Stock)" class="px-2" />
                            <TextInput v-model="editForm.current_stock" type="number" class="w-full" />
                            <InputError :message="editForm.errors.current_stock" />
                        </div>
                        <div class="space-y-4">
                            <InputLabel value="Safety Threshold (Min)" class="px-2" />
                            <TextInput v-model="editForm.min_stock_level" type="number" class="w-full" />
                            <InputError :message="editForm.errors.min_stock_level" />
                        </div>
                        <div class="space-y-4">
                            <InputLabel value="Unit Cost (INR)" class="px-2" />
                            <TextInput v-model="editForm.unit_cost" type="number" step="0.01" class="w-full" />
                            <InputError :message="editForm.errors.unit_cost" />
                        </div>
                        <div class="space-y-4">
                            <InputLabel value="Unit" class="px-2" />
                            <TextInput v-model="editForm.unit" type="text" class="w-full" />
                            <InputError :message="editForm.errors.unit" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-8 border-t border-slate-100 mt-6">
                        <button type="button" @click="closeEdit" class="text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-rose-500 transition-colors">Discard</button>
                        <div class="flex items-center gap-6">
                            <PrimaryButton type="submit" :disabled="editForm.processing" class="h-14 px-10">
                                <ArrowPathIcon v-if="editForm.processing" class="w-5 h-5 animate-spin mr-3" />
                                <CheckCircleIcon v-else class="w-5 h-5 mr-3" />
                                {{ editForm.processing ? 'Syncing...' : 'Update Resource' }}
                            </PrimaryButton>
                        </div>
                    </div>
                </form>
             </div>
        </Modal>

        <!-- Restoration Authorization Modal (Restock) -->
        <Modal :show="showRestockModal" @close="closeRestock" max-width="2xl">
             <div class="bg-white rounded-3xl overflow-hidden text-left">
                <div class="p-8 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Add Stock</h3>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1.5">Add quantity to this item</p>
                    </div>
                    <button @click="closeRestock" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-rose-500 transition-colors">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <form @submit.prevent="submitRestock" class="p-10 space-y-10">
                    <div class="bg-slate-50 rounded-2xl p-8 border border-slate-200 relative overflow-hidden group/modalcard">
                        <div class="absolute -right-32 -top-32 w-80 h-80 bg-indigo-50 rounded-full blur-[80px]"></div>
                        <div class="flex items-center gap-6 relative z-10">
                            <div class="w-12 h-12 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-indigo-600">
                                <CubeIcon class="w-6 h-6" />
                            </div>
                            <div>
                                <span class="text-[9px] font-bold text-indigo-400 uppercase tracking-widest block mb-2">Item Identity</span>
                                <h4 class="text-xl font-black text-slate-900 uppercase tracking-tight leading-none mb-2">{{ selectedItem.name }}</h4>
                                <div class="flex items-center gap-3 mt-1.5 opacity-60">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Current Presence:</span>
                                    <span class="text-base font-black text-slate-900 tabular-nums">{{ selectedItem.current_stock }} Units</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-4">
                            <InputLabel value="Quantity to Add" class="px-2" />
                            <TextInput v-model="restockForm.quantity" type="number" min="1" class="w-full" required />
                        </div>
                        <div class="space-y-4">
                            <InputLabel value="Unit Cost (Net)" class="px-2" />
                            <TextInput v-model="restockForm.unit_cost" type="number" step="0.01" class="w-full" placeholder="0.00" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-8 border-t border-slate-100 mt-6">
                        <button type="button" @click="closeRestock" class="text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-rose-500 transition-colors">Discard</button>
                        <div class="flex items-center gap-6">
                            <PrimaryButton type="submit" :disabled="restockForm.processing" class="h-14 px-10">
                                <ArrowPathIcon v-if="restockForm.processing" class="w-5 h-5 animate-spin mr-3" />
                                <ShieldCheckIcon v-else class="w-5 h-5 mr-3" />
                                {{ restockForm.processing ? 'Updating...' : 'Save Stock' }}
                            </PrimaryButton>
                        </div>
                    </div>
                </form>
             </div>
        </Modal>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
</style>

<style scoped>
/* No scroll bars on numeric inputs */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.font-mono {
    font-family: 'JetBrains Mono', monospace;
}
</style>
