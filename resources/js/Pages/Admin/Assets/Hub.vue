<script setup>
import { ref, computed } from 'vue';
import { router, Head, Link, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import PremiumModal from '@/Components/PremiumModal.vue';
import { 
    Squares2X2Icon, 
    TableCellsIcon, 
    BriefcaseIcon, 
    ShoppingCartIcon, 
    WrenchScrewdriverIcon, 
    Cog6ToothIcon,
    PlusIcon,
    ArrowUpTrayIcon
} from '@heroicons/vue/24/outline';

// Tabs
import TabDashboard from './Tabs/TabDashboard.vue';
import TabInventory from './Tabs/TabInventory.vue';
import TabKits from './Tabs/TabKits.vue';
import TabProcurement from './Tabs/TabProcurement.vue';
import TabMaintenance from './Tabs/TabMaintenance.vue';
import TabConfiguration from './Tabs/TabConfiguration.vue';

const props = defineProps({
    tab: String,
    stats: Object,
    assets: Object,
    kits: Array,
    categories: Array,
    tickets: Object,
    requests: Object,
    vendors: Array
});

const tabs = [
    { id: 'dashboard', label: 'Dashboard', icon: Squares2X2Icon, color: 'text-indigo-600', bg: 'bg-indigo-50' },
    { id: 'inventory', label: 'Inventory', icon: TableCellsIcon, color: 'text-emerald-600', bg: 'bg-emerald-50' },
    { id: 'kits', label: 'Kits & Bundles', icon: BriefcaseIcon, color: 'text-amber-600', bg: 'bg-amber-50' },
    { id: 'requests', label: 'Procurement', icon: ShoppingCartIcon, color: 'text-rose-600', bg: 'bg-rose-50' },
    { id: 'maintenance', label: 'Maintenance', icon: WrenchScrewdriverIcon, color: 'text-blue-600', bg: 'bg-blue-50' },
    { id: 'config', label: 'Configuration', icon: Cog6ToothIcon, color: 'text-slate-600', bg: 'bg-slate-50' },
];

const activeTab = computed(() => props.tab || 'dashboard');

// Create Action
const showCreateModal = ref(false);
const createForm = useForm({
    name: '',
    category_id: '',
    serial_number: '',
    purchase_date: '',
    purchase_cost: '',
    status: 'In Stock'
});

const submitCreate = () => {
    createForm.post(route('admin.assets.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        }
    });
};

// Import Logic
const fileInput = ref(null);
const triggerImport = () => fileInput.value.click();
const handleImport = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    
    const form = useForm({ file: file });
    form.post(route('admin.assets.import'), {
        onSuccess: () => {
             e.target.value = ''; // Reset
        }
    });
};
</script>

<template>
    <Head title="Asset Command Center" />
    <MainLayout>
        <div class="h-full flex flex-col font-outfit">
            <!-- Strategic Header -->
            <div class="mb-8 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 px-1">
                <div class="flex items-center gap-5">
                    <div class="p-4 bg-slate-900 border border-slate-800 rounded-2xl text-indigo-400 shadow-xl shadow-slate-200/50 relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <Squares2X2Icon class="w-7 h-7 relative z-10" />
                    </div>
                    <div>
                        <h1 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                            Asset Command Center
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-indigo-50 text-indigo-600 border border-indigo-100 uppercase tracking-widest">Enterprise Core</span>
                        </h1>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] mt-1">Lifecycle monitoring & strategic inventory management</p>
                    </div>
                </div>

                <!-- Global Actions -->
                <div class="flex items-center gap-3 w-full lg:w-auto">
                    <button @click="triggerImport" class="h-12 w-12 bg-white text-slate-400 rounded-2xl flex items-center justify-center border border-slate-200 shadow-sm hover:text-indigo-600 hover:border-indigo-100 transition-all active:scale-90 shrink-0">
                        <ArrowUpTrayIcon class="w-5 h-5" />
                    </button>
                    <button @click="showCreateModal = true" class="flex-1 lg:flex-none flex items-center justify-center gap-3 bg-slate-900 text-white h-12 px-8 rounded-2xl hover:bg-indigo-600 transition-all text-sm font-black uppercase tracking-[0.2em] shadow-xl shadow-slate-200 active:scale-95 group">
                        <PlusIcon class="w-4 h-4 text-indigo-400 group-hover:rotate-90 transition-transform" />
                        <span>Initialize Asset Node</span>
                    </button>
                    <input type="file" ref="fileInput" class="hidden" @change="handleImport" accept=".csv,.xlsx" />
                </div>
            </div>

            <!-- Dashboard Neural Interface -->
            <div class="bg-white/40 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/50 flex flex-col overflow-hidden min-h-[600px]">
                <!-- Tab Terminal -->
                <div class="flex items-center px-6 pt-6 border-b border-slate-100 overflow-x-auto hide-scrollbar gap-2 shrink-0">
                    <Link 
                        v-for="t in tabs" 
                        :key="t.id"
                        :href="route('admin.assets.hub', { tab: t.id })" 
                        preserve-state
                        class="px-6 py-4 text-sm font-black uppercase tracking-[0.15em] border-b-2 transition-all flex items-center gap-3 whitespace-nowrap active:scale-95 group"
                        :class="activeTab === t.id 
                            ? 'border-indigo-600 text-indigo-600 font-black' 
                            : 'border-transparent text-slate-400 hover:text-slate-600'"
                    >
                        <div 
                            class="w-6 h-6 rounded-lg flex items-center justify-center transition-all group-hover:scale-110"
                            :class="activeTab === t.id ? t.bg + ' ' + t.color : 'bg-slate-50 text-slate-400'"
                        >
                            <component :is="t.icon" class="h-3.5 w-3.5" />
                        </div>
                        {{ t.label }}
                    </Link>
                </div>

                <!-- Content Area -->
                <div class="p-6 md:p-10 flex-1 bg-slate-50/30 overflow-y-auto custom-scrollbar overflow-x-hidden">
                    <Transition name="fade-slide" mode="out-in">
                        <div :key="activeTab">
                            <TabDashboard v-if="activeTab === 'dashboard'" :stats="stats" />
                            <TabInventory v-if="activeTab === 'inventory'" :assets="assets" :categories="categories" />
                            <TabKits v-if="activeTab === 'kits'" :kits="kits" :categories="categories" />
                            <TabProcurement v-if="activeTab === 'requests'" :requests="requests" />
                            <TabMaintenance v-if="activeTab === 'maintenance'" :tickets="tickets" />
                            <TabConfiguration v-if="activeTab === 'config'" :categories="categories" :vendors="vendors" />
                        </div>
                    </Transition>
                </div>
            </div>
        </div>

        <!-- Initialize Asset Modal -->
        <PremiumModal 
            :show="showCreateModal" 
            @close="showCreateModal = false" 
            title="Initialize Asset" 
            subtitle="Register New Hardware/Software Resource"
            icon="fa-laptop-code"
            maxWidth="3xl"
        >
            <form @submit.prevent="submitCreate" class="space-y-6 pt-4 px-2">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2.5">
                        <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1">Asset Designation</label>
                        <div class="relative group">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors pointer-events-none font-black text-base">@</div>
                            <input v-model="createForm.name" type="text" class="w-full h-12 bg-slate-50 border-2 border-slate-100 rounded-2xl pl-11 pr-4 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all placeholder:text-slate-300 uppercase tracking-widest shadow-sm" required placeholder="ASSET_NAME...">
                        </div>
                    </div>
                     <div class="space-y-2.5">
                        <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1">Category Node</label>
                        <div class="relative group">
                            <select v-model="createForm.category_id" class="w-full h-12 bg-slate-50 border-2 border-slate-100 rounded-2xl pl-4 pr-10 text-sm font-black uppercase text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all appearance-none cursor-pointer tracking-widest shadow-sm" required>
                                <option value="">SELECT_CLASS</option>
                                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                            <svg class="w-3 h-3 absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2.5">
                        <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1">Unique Serial Log</label>
                        <input v-model="createForm.serial_number" type="text" class="w-full h-12 bg-slate-50 border-2 border-slate-100 rounded-2xl px-4 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all placeholder:text-slate-300 uppercase tracking-widest shadow-sm" placeholder="S/N CODE...">
                    </div>
                     <div class="space-y-2.5">
                        <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1">Acquisition Anchor</label>
                        <input v-model="createForm.purchase_date" type="date" class="w-full h-12 bg-slate-50 border-2 border-slate-100 rounded-2xl px-4 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all uppercase tracking-widest shadow-sm">
                    </div>
                </div>
                
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2.5">
                        <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1">Procurement Cost</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-black text-slate-400">INR</span>
                            <input v-model="createForm.purchase_cost" type="number" step="0.01" class="w-full h-12 bg-slate-50 border-2 border-slate-100 rounded-2xl pl-12 pr-4 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all uppercase tracking-widest shadow-sm" placeholder="0.00">
                        </div>
                    </div>
                    <div class="space-y-2.5">
                        <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1">Initial Protocol</label>
                        <select v-model="createForm.status" class="w-full h-12 bg-slate-50 border-2 border-slate-100 rounded-2xl px-4 text-sm font-black uppercase text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all appearance-none cursor-pointer tracking-widest shadow-sm">
                            <option value="In Stock">IN_STORAGE</option>
                            <option value="In Use">ACTIVE_SERVICE</option>
                            <option value="Under Maintenance">UNDER_REPAIR</option>
                            <option value="Disposed">DECOMMISSIONED</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-8 border-t border-slate-100 mt-8">
                    <button @click="showCreateModal = false" type="button" class="text-sm font-black uppercase tracking-[0.2em] text-slate-400 hover:text-slate-600 transition-colors">Abort Initialization</button>
                    <button type="submit" :disabled="createForm.processing" class="h-14 px-12 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.2em] hover:bg-indigo-600 transition-all flex items-center gap-3 shadow-xl shadow-slate-200 active:scale-95 group">
                        <PlusIcon class="w-4 h-4 text-indigo-400 group-hover:rotate-90 transition-transform" />
                        <span>{{ createForm.processing ? 'Syncing...' : 'Confirm Registration' }}</span>
                    </button>
                </div>
            </form>
        </PremiumModal>
    </MainLayout>
</template>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.fade-slide-enter-active, .fade-slide-leave-active { 
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.fade-slide-enter-from { 
    opacity: 0; 
    transform: translateX(20px);
}
.fade-slide-leave-to { 
    opacity: 0; 
    transform: translateX(-20px);
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}

input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(0.4) sepia(1) saturate(5) hue-rotate(240deg);
    cursor: pointer;
}
</style>
