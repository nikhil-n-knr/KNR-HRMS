<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref, computed } from 'vue';
import PremiumModal from '@/Components/PremiumModal.vue';
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
    CurrencyRupeeIcon as CashIcon
} from '@heroicons/vue/24/outline';

defineOptions({ layout: MainLayout });

const props = defineProps({
    tab: { type: String, default: 'stats' },
    stats: Object,
    items: Object,
    procurement: Array
});

const tabs = [
    { id: 'stats', label: 'Store Pulse', icon: ChartBarIcon },
    { id: 'list', label: 'Inventory List', icon: TableIcon },
    { id: 'procurement', label: 'Procurement Queue', icon: ShoppingCartIcon },
    { id: 'scanner', label: 'Scanner Mode', icon: QrCodeIcon }
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
</script>

<template>
    <Head title="Strategic Inventory Command" />

    <div class="space-y-10 pb-20 font-outfit animate-in fade-in slide-in-from-bottom-5 duration-700">
        <!-- Smart Header Terminal -->
        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-slate-100 p-8 shadow-2xl shadow-slate-200/40 relative overflow-hidden group">
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-indigo-50 rounded-full blur-2xl group-hover:bg-indigo-100 transition-colors duration-1000"></div>
            
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8 relative z-10">
                <div class="flex items-center gap-6">
                    <div class="w-14 h-14 bg-slate-900 rounded-2xl flex items-center justify-center text-indigo-400 shadow-xl group-hover:rotate-12 transition-transform">
                        <ArchiveBoxIcon class="w-8 h-8" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                            Resource Command Center
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-black bg-indigo-50 text-indigo-600 border border-indigo-100 uppercase tracking-widest shadow-sm">Tactical Ops</span>
                        </h1>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] mt-1.5 flex items-center gap-2">
                            <BoltIcon class="w-4 h-4 text-indigo-500" />
                            Active Stock Intelligence & Global Store Synchronization
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4 w-full lg:w-auto">
                    <Link :href="route('admin.inventory.create')" class="flex-1 lg:flex-none h-14 px-10 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.3em] shadow-2xl shadow-slate-300 hover:bg-indigo-600 transition-all active:scale-95 flex items-center justify-center gap-4 group">
                        <PlusIcon class="w-5 h-5 group-hover:scale-125 transition-transform" />
                        Initialize SKU
                    </Link>
                </div>
            </div>

            <!-- Enhanced Tactical Tab Bar -->
            <div class="flex items-center gap-4 mt-10 p-1.5 bg-slate-50/80 rounded-2xl border border-slate-100 w-fit relative z-10">
                <button 
                    v-for="t in tabs" 
                    :key="t.id"
                    @click="switchTab(t.id)"
                    class="h-11 px-6 text-sm font-black uppercase tracking-[0.2em] rounded-xl transition-all flex items-center gap-3 relative overflow-hidden group/tab"
                    :class="tab === t.id ? 'bg-slate-900 text-white shadow-xl translate-y-[-1px]' : 'text-slate-400 hover:text-slate-600 hover:bg-white transition-all'"
                >
                    <component :is="t.icon" class="w-4 h-4" :class="tab === t.id ? 'text-indigo-400' : 'text-slate-300'" />
                    {{ t.label }}
                    <div v-if="tab === t.id" class="absolute bottom-0 left-0 w-full h-0.5 bg-indigo-400 opacity-50"></div>
                </button>
            </div>
        </div>

        <!-- Dynamic Intelligence Content -->
        <div class="relative min-h-[500px]">
            <!-- Tab 1: Stats Pulse -->
            <div v-if="tab === 'stats'" class="space-y-8 animate-in fade-in fill-mode-both duration-500">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div v-for="stat in [
                        { label: 'Total Catalog Nodes', value: stats.total_items, icon: RectangleGroupIcon, color: 'text-indigo-600', bg: 'bg-indigo-50' },
                        { label: 'Consumption Velocity', value: stats.velocity + ' Units', icon: ChartBarIconAlt, color: 'text-emerald-600', bg: 'bg-emerald-50' },
                        { label: 'Critical Breaches', value: stats.low_stock, icon: ExclamationTriangleIcon, color: 'text-rose-600', bg: 'bg-rose-50', pulse: stats.low_stock > 0 }
                    ]" :key="stat.label" class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 group relative overflow-hidden">
                        <div v-if="stat.pulse" class="absolute inset-0 bg-rose-500/[0.02] animate-pulse"></div>
                        <div class="flex items-center justify-between relative z-10">
                            <div>
                                <span class="text-sm font-black text-slate-400 uppercase tracking-widest block mb-2">{{ stat.label }}</span>
                                <span class="text-4xl font-black text-slate-900 tabular-nums">{{ stat.value }}</span>
                            </div>
                            <div :class="[stat.bg, stat.color]" class="w-16 h-16 rounded-[1.5rem] flex items-center justify-center shadow-inner group-hover:scale-110 group-hover:rotate-6 transition-all duration-500">
                                <component :is="stat.icon" class="w-8 h-8" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Strategic Reorder Deck -->
                <div class="bg-slate-900 rounded-[3rem] p-10 shadow-2xl shadow-indigo-500/10 relative overflow-hidden group/deck">
                    <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-indigo-500/10 rounded-full blur-[100px] group-hover/deck:bg-indigo-500/20 transition-all duration-1000"></div>
                    
                    <div class="flex items-center justify-between mb-8 relative z-10">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-rose-500/20 rounded-2xl flex items-center justify-center text-rose-400 border border-rose-500/30">
                                <ExclamationTriangleIcon class="w-7 h-7" />
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-white uppercase tracking-tight">Reorder Tactical Matrix</h3>
                                <p class="text-sm font-black text-slate-400 uppercase tracking-widest mt-1 opacity-60">High-priority replenishment nodes</p>
                            </div>
                        </div>
                        <span class="text-sm font-black text-slate-500 uppercase tracking-[0.3em]">Matrix_Status: Critical</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 relative z-10">
                        <div v-for="item in stats.alerts" :key="item.id" class="bg-white/5 border border-white/5 backdrop-blur-md rounded-[2rem] p-6 group/item hover:bg-white/10 hover:border-white/10 transition-all">
                            <div class="flex flex-col h-full justify-between">
                                <div class="flex items-start justify-between mb-6">
                                    <div class="w-12 h-12 bg-slate-800 rounded-2xl flex items-center justify-center text-slate-400 group-hover/item:text-emerald-400 transition-colors">
                                        <CubeIcon class="w-6 h-6 text-slate-500" />
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-bold text-rose-400 font-mono tracking-widest uppercase">STOCK_DEFICIT</div>
                                        <div class="text-[14px] font-black text-white tabular-nums mt-1">{{ item.current_stock }} / <span class="opacity-40">{{ item.min_stock_level }}</span></div>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-lg font-black text-white uppercase tracking-tight mb-6 line-clamp-1 italic">{{ item.name }}</h4>
                                    <button @click="openRestock(item)" class="w-full h-12 bg-white text-slate-900 rounded-xl text-sm font-black uppercase tracking-[0.2em] hover:bg-emerald-500 hover:text-white transition-all active:scale-95 shadow-xl">
                                        Initialize Restock
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-if="!stats.alerts.length" class="col-span-full py-20 flex flex-col items-center gap-6 opacity-30 grayscale">
                            <ShieldCheckIcon class="w-20 h-20 text-emerald-400 animate-pulse" />
                            <p class="text-sm font-black text-white uppercase tracking-[0.5em]">Depository integrity verified</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Master Ledger -->
            <div v-if="tab === 'list'" class="animate-in fade-in slide-in-from-right-10 duration-500">
                <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl shadow-slate-200/40 overflow-hidden relative group">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-900 border-b border-slate-800">
                                    <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Active Entity</th>
                                    <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Depletion Gauge</th>
                                    <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Temporal Burn Rate</th>
                                    <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">UOM Metric</th>
                                    <th class="px-8 py-6 text-right text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Command</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="item in items.data" :key="item.id" class="group/row hover:bg-slate-50/80 transition-all duration-300">
                                    <td class="px-8 py-7">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-300 group-hover/row:bg-slate-900 group-hover/row:text-indigo-400 transition-all shadow-inner border border-slate-100">
                                                <CubeIcon class="w-6 h-6" />
                                            </div>
                                            <div>
                                                <div class="text-lg font-black text-slate-900 uppercase tracking-tight group-hover/row:text-indigo-700 transition-colors">{{ item.name }}</div>
                                                <div class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1.5 opacity-60">SKU_REF #{{ String(item.id).padStart(5, '0') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-7">
                                        <div class="flex flex-col gap-2.5">
                                            <div class="flex items-center justify-between text-sm font-bold uppercase tracking-widest pr-4 leading-none">
                                                <span :class="item.current_stock <= item.min_stock_level ? 'text-rose-500' : 'text-slate-400'">{{ item.current_stock }} Units Available</span>
                                                <span class="text-slate-300 opacity-50">{{ item.min_stock_level }} Min</span>
                                            </div>
                                            <div class="w-48 h-1.5 bg-slate-100 rounded-full overflow-hidden shadow-inner border border-slate-200/50">
                                                <div class="h-full rounded-full transition-all duration-1000" 
                                                    :style="`width: ${Math.min((item.current_stock / (item.min_stock_level * 2)) * 100, 100)}%`"
                                                    :class="item.current_stock <= item.min_stock_level ? 'bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.4)]' : 'bg-indigo-500'"
                                                ></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-7">
                                        <div v-if="item.days_remaining < 999">
                                            <div class="px-3 py-1.5 rounded-xl text-sm font-black tracking-widest uppercase inline-flex items-center gap-2 border shadow-sm"
                                                :class="{
                                                    'bg-rose-50 text-rose-600 border-rose-100': item.days_remaining <= 7,
                                                    'bg-amber-50 text-amber-600 border-amber-100': item.days_remaining > 7 && item.days_remaining <= 30,
                                                    'bg-indigo-50 text-indigo-600 border-indigo-100': item.days_remaining > 30
                                                }">
                                                <ClockIcon class="w-3.5 h-3.5" />
                                                {{ item.days_remaining }} Operational Days
                                            </div>
                                        </div>
                                        <span v-else class="text-sm font-black text-slate-300 uppercase tracking-[0.2em] italic opacity-50 underline decoration-dotted decoration-2">Infinite_Pulse</span>
                                    </td>
                                    <td class="px-8 py-7 uppercase text-base font-black text-slate-500 tracking-widest">{{ item.unit }}</td>
                                    <td class="px-8 py-7 text-right">
                                        <div class="flex justify-end gap-3 opacity-0 group-hover/row:opacity-100 transition-all translate-x-4 group-hover/row:translate-x-0">
                                            <button @click="openRestock(item)" class="h-10 px-5 bg-slate-900 text-white rounded-xl text-sm font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-xl active:scale-95">Supply</button>
                                            <Link :href="route('admin.inventory.show', item.id)" class="h-10 px-5 bg-white text-slate-400 border border-slate-200 rounded-xl text-sm font-black uppercase tracking-widest hover:bg-slate-50 hover:text-slate-900 transition-all shadow-sm flex items-center">Profile</Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Procurement Stream -->
            <div v-if="tab === 'procurement'" class="animate-in fade-in slide-in-from-left-10 duration-500">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Procurement Stream</h2>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-[0.4em] mt-2">Inventory restoration & dynamic sourcing queue</p>
                    </div>
                    <div class="h-px bg-slate-100 flex-1 mx-12 hidden lg:block"></div>
                </div>

                <div v-if="procurement && procurement.length > 0" class="bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl shadow-slate-200/40 overflow-hidden relative group">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-amber-950 border-b border-amber-900">
                                <th class="px-8 py-6 text-sm font-black text-amber-500/50 uppercase tracking-[0.2em]">Urgent Resource</th>
                                <th class="px-8 py-6 text-sm font-black text-amber-500/50 uppercase tracking-[0.2em]">UOM</th>
                                <th class="px-8 py-6 text-sm font-black text-amber-500/50 uppercase tracking-[0.2em]">Current State</th>
                                <th class="px-8 py-6 text-sm font-black text-amber-500/50 uppercase tracking-[0.2em]">Restoration Deficit</th>
                                <th class="px-8 py-6 text-right text-sm font-black text-amber-500/50 uppercase tracking-[0.2em]">Initiate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-amber-50">
                            <tr v-for="item in procurement" :key="item.id" class="group/prow hover:bg-amber-50/50 transition-all duration-300">
                                <td class="px-8 py-8">
                                    <div class="flex items-center gap-5">
                                        <div class="w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-500 shadow-inner group-hover/prow:bg-amber-500 group-hover/prow:text-white transition-all">
                                            <ArchiveBoxIcon class="w-8 h-8" />
                                        </div>
                                        <div>
                                            <div class="text-[14px] font-black text-slate-900 uppercase tracking-tight">{{ item.name }}</div>
                                            <div class="text-sm font-black text-amber-600 uppercase tracking-widest mt-1.5">{{ item.category ?? 'INTERNAL_STORE' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-8 text-base font-black text-slate-500 uppercase tracking-widest opacity-60">{{ item.unit }}</td>
                                <td class="px-8 py-8">
                                    <div class="px-4 py-2 bg-rose-50 border border-rose-100 text-rose-600 rounded-2xl inline-flex items-center gap-2 shadow-sm">
                                        <div class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></div>
                                        <span class="text-base font-black tabular-nums">{{ item.current_stock }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-8">
                                    <div class="flex flex-col gap-1.5 pr-8">
                                        <span class="text-lg font-black text-rose-600 tabular-nums leading-none">-{{ (item.min_stock_level - item.current_stock).toFixed(1) }} Deficit</span>
                                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest leading-none">Min Threshold: {{ item.min_stock_level }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-8 text-right">
                                    <button @click="openRestock(item)" class="h-14 px-8 bg-slate-900 text-white text-sm font-black uppercase tracking-[0.2em] rounded-2xl shadow-xl hover:bg-amber-600 transition-all active:scale-95 group/pbtn flex items-center gap-3 ml-auto">
                                        <InboxArrowDownIcon class="w-5 h-5 text-amber-400 group-hover/pbtn:translate-y-1 transition-transform" />
                                        Execute Restoration
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="bg-white rounded-[3rem] border border-slate-100 shadow-2xl shadow-slate-200/40 p-24 text-center grayscale group hover:grayscale-0 transition-all duration-700">
                    <div class="relative inline-block mb-10">
                        <ShieldCheckIcon class="w-24 h-24 text-emerald-500/20 group-hover:text-emerald-500 transition-colors duration-700" />
                        <div class="absolute inset-0 bg-emerald-500/10 blur-[40px] rounded-full group-hover:bg-emerald-500/20 transition-all"></div>
                    </div>
                    <h3 class="text-3xl font-black text-slate-900 uppercase tracking-tight mb-3 italic">Pristine Resource State</h3>
                    <p class="text-base font-black text-slate-400 uppercase tracking-[0.5em] max-w-sm mx-auto leading-relaxed">System diagnostics confirm all SKUs are above critical minimum synchronization floors.</p>
                </div>
            </div>
        </div>

        <!-- Restoration Authorization Modal -->
        <PremiumModal 
            :show="showRestockModal" 
            @close="closeRestock" 
            title="Resource Restoration" 
            subtitle="Authorize Critical Stock Injection Protocol"
            icon="fa-box-open"
            maxWidth="xl"
            @closed="closeRestock"
        >
            <form @submit.prevent="submitRestock" class="space-y-10 pt-6">
                <div class="bg-slate-900 rounded-[2rem] p-8 text-white relative overflow-hidden group/modalcard shadow-2xl shadow-indigo-500/20">
                    <div class="absolute -right-8 -top-8 w-32 h-32 bg-indigo-500/20 rounded-full blur-3xl opacity-50 group-hover/modalcard:opacity-100 transition-opacity"></div>
                    <div class="flex items-center gap-6 relative z-10">
                        <div class="w-16 h-16 bg-white/10 backdrop-blur-xl border border-white/10 rounded-[1.5rem] flex items-center justify-center text-indigo-400 shadow-xl group-hover/modalcard:rotate-6 transition-transform">
                            <CubeIcon class="w-9 h-9" />
                        </div>
                        <div>
                            <span class="text-sm font-black text-indigo-400 uppercase tracking-[0.3em] block mb-1">Target Resource</span>
                            <h4 class="text-2xl font-black uppercase tracking-tight italic">{{ selectedItem.name }}</h4>
                            <div class="flex items-center gap-3 mt-1.5 opacity-60">
                                <span class="text-sm font-black uppercase tracking-widest text-slate-400">Current Node State:</span>
                                <span class="text-base font-black text-white tabular-nums">{{ selectedItem.current_stock }} Units</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 px-2">
                    <div class="space-y-3.5">
                        <label class="px-2 text-sm font-black text-slate-500 uppercase tracking-[0.2em] leading-none">Injection Quantum (Quantity)</label>
                        <div class="relative group/input">
                            <PlusIcon class="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-300 group-focus-within/input:text-emerald-500 transition-colors" />
                            <input v-model="restockForm.quantity" type="number" min="1" class="w-full h-16 bg-slate-50 border-2 border-slate-100 rounded-[1.5rem] pl-16 pr-6 text-[15px] font-black text-slate-900 focus:bg-white focus:ring-8 focus:ring-emerald-500/5 focus:border-emerald-500 transition-all font-mono" required>
                        </div>
                    </div>
                    <div class="space-y-3.5">
                        <label class="px-2 text-sm font-black text-slate-500 uppercase tracking-[0.2em] leading-none">Resource Cost Valuation (Unit Cost)</label>
                        <div class="relative group/input">
                            <CashIcon class="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-300 group-focus-within/input:text-indigo-500 transition-colors" />
                            <input v-model="restockForm.unit_cost" type="number" step="0.01" class="w-full h-16 bg-slate-50 border-2 border-slate-100 rounded-[1.5rem] pl-16 pr-6 text-[15px] font-black text-slate-900 focus:bg-white focus:ring-8 focus:ring-indigo-500/5 focus:border-indigo-500 transition-all font-mono" placeholder="0.00">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-10 border-t border-slate-50 px-2 mt-4">
                    <button type="button" @click="closeRestock" class="text-sm font-black uppercase tracking-[0.34em] text-slate-400 hover:text-slate-600 transition-colors">Abort_Protocol</button>
                    <button type="submit" :disabled="restockForm.processing" class="h-16 px-12 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.2em] shadow-2xl shadow-emerald-500/10 hover:bg-emerald-600 transition-all flex items-center gap-4 active:scale-95 group/smit">
                        <ArrowPathIcon v-if="restockForm.processing" class="w-5 h-5 animate-spin" />
                        <ShieldCheckIcon v-else class="w-6 h-6 text-emerald-400 group-hover/smit:scale-110 transition-transform" />
                        Finalize Injection
                    </button>
                </div>
            </form>
        </PremiumModal>
    </div>
</template>

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
