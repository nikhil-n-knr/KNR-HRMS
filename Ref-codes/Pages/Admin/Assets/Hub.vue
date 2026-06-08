<script setup>
import { ref, computed } from 'vue';
import { router, Head, Link, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import PremiumModal from '@/Components/PremiumModal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import InputError from '@/Components/InputError.vue';
import { 
    PlusIcon, 
    ArrowUpTrayIcon, 
    CubeIcon, 
    ChartBarIcon, 
    ArchiveBoxIcon, 
    BoltIcon, 
    AdjustmentsHorizontalIcon, 
    SparklesIcon,
    UserGroupIcon,
    CheckCircleIcon,
    ArrowPathIcon,
    PresentationChartLineIcon,
    ShoppingCartIcon,
    BriefcaseIcon,
    WrenchScrewdriverIcon,
    ShieldCheckIcon,
    MagnifyingGlassIcon,
    InboxIcon,
    IdentificationIcon,
    CloudArrowUpIcon,
    ArrowRightIcon,
    CommandLineIcon,
    BanknotesIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/solid';

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
    locations: Array,
    statuses: Array,
    tickets: Object,
    requests: Object,
    vendors: Array,
    users: Array,
    filters: Object
});

const tabs = [
    { id: 'dashboard', label: 'Dashboard', icon: ChartBarIcon, color: 'text-indigo-600', bg: 'bg-indigo-50', border: 'border-indigo-100' },
    { id: 'inventory', label: 'All Assets', icon: ArchiveBoxIcon, color: 'text-emerald-600', bg: 'bg-emerald-50', border: 'border-emerald-100' },
    { id: 'kits', label: 'Bundles', icon: BriefcaseIcon, color: 'text-amber-600', bg: 'bg-amber-50', border: 'border-amber-100' },
    { id: 'requests', label: 'Requests', icon: ShoppingCartIcon, color: 'text-rose-600', bg: 'bg-rose-50', border: 'border-rose-100' },
    { id: 'maintenance', label: 'Maintenance', icon: WrenchScrewdriverIcon, color: 'text-blue-600', bg: 'bg-blue-50', border: 'border-blue-100' },
    { id: 'config', label: 'Configuration', icon: AdjustmentsHorizontalIcon, color: 'text-slate-600', bg: 'bg-slate-100', border: 'border-slate-200' },
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
    status: 'Available'
});

const submitCreate = () => {
    createForm.post(route('admin.assets.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        }
    });
};

// Bulk Assign Logic (Actually redirects to BulkAssign.vue or similar, but Hub has a placeholder)
const showBulkModal = ref(false);

// Import Logic
const fileInput = ref(null);
const triggerImport = () => {
    // Redirecting to Smart Import instead of simple flow
    router.get(route('admin.assets.import.smart'));
};
</script>

<template>
    <Head title="Asset Management" />
    
    <MainLayout>
        <div class="h-full flex flex-col font-outfit -m-8 p-12 bg-slate-50 min-h-screen relative animate-in fade-in duration-1000">
            <!-- Strategic Header Terminal -->
            <header class="bg-white rounded-3xl p-10 border border-slate-200 shadow-sm mb-12 relative overflow-hidden group">
                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-12 relative z-10">
                    <div class="flex items-center gap-8 text-left">
                        <div class="w-20 h-20 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shrink-0">
                            <CubeIcon class="w-10 h-10" />
                        </div>
                        <div>
                            <div class="flex items-center gap-4">
                                <h1 class="text-4xl font-black text-slate-900 uppercase tracking-tight leading-none">Asset Management</h1>
                                <span class="px-3 py-1 bg-indigo-50 text-indigo-600 text-[10px] font-bold uppercase tracking-widest rounded-lg border border-indigo-100 leading-none">Live</span>
                            </div>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.5em] mt-3 leading-none">Track, assign, maintain, and audit assets</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <div v-for="stat in [
                            { label: 'Asset Valuation', value: '₹' + (stats?.total_valuation || 0).toLocaleString(), color: 'text-indigo-600', icon: BanknotesIcon },
                            { label: 'Audit Compliance', value: (stats?.audit_compliance_pct || 0) + '%', color: 'text-emerald-600', icon: ShieldCheckIcon },
                            { label: 'Critical Alerts', value: stats?.critical_alerts || 0, color: 'text-rose-600', icon: ExclamationTriangleIcon },
                            { label: 'Total Nodes', value: stats?.total_assets || 0, color: 'text-slate-900', icon: CubeIcon }
                        ]" :key="stat.label" class="bg-slate-50 border border-slate-100 rounded-2xl px-6 py-4 min-w-[160px] flex items-center gap-4 group/stat hover:border-indigo-100 transition-all">
                            <div class="w-10 h-10 rounded-xl bg-white border border-slate-100 shadow-sm flex items-center justify-center shrink-0">
                                <component :is="stat.icon || CubeIcon" class="w-5 h-5" :class="stat.color" />
                            </div>
                            <div class="text-left">
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none">{{ stat.label }}</p>
                                <p class="text-lg font-black text-slate-900 mt-1.5 tabular-nums leading-none tracking-tight">{{ stat.value }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Ops Launcher & Unified Navigation -->
            <section class="rounded-3xl border border-slate-200 bg-white p-8 mb-12 relative z-20">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8 mb-10">
                    <div class="text-left">
                        <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight leading-none">Operations Workspace</h2>
                        <p class="text-xs font-semibold text-slate-400 mt-2">Manage assets, requests, maintenance, and audits from one place.</p>
                    </div>

                    <nav class="flex items-center bg-slate-100/50 p-1.5 rounded-2xl border border-slate-200 overflow-x-auto no-scrollbar shrink-0">
                         <div v-for="t in tabs" :key="t.id" 
                            @click="router.get(route('admin.assets.hub'), { tab: t.id }, { preserveState: true, preserveScroll: true })"
                            class="px-5 py-2.5 rounded-xl text-[10px] font-bold uppercase tracking-widest transition-all cursor-pointer flex items-center gap-3 whitespace-nowrap active:scale-95"
                            :class="activeTab === t.id ? 'bg-white text-indigo-600 shadow-sm border border-slate-200' : 'text-slate-500 hover:text-slate-900 hover:bg-white/50'">
                            <component :is="t.icon" class="w-4 h-4" />
                            {{ t.label }}
                         </div>
                    </nav>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <button @click="showCreateModal = true" 
                        class="p-6 bg-slate-50 rounded-2xl border border-slate-100 hover:border-indigo-200 hover:bg-white transition-all text-left flex items-start gap-5 grow group">
                        <div class="w-12 h-12 bg-indigo-600 text-white rounded-xl flex items-center justify-center shrink-0 shadow-md group-hover:scale-110 transition-transform">
                            <PlusIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest">Assets</p>
                            <h4 class="text-lg font-black text-slate-900 mt-1 uppercase tracking-tight leading-tight">Add Asset</h4>
                            <p class="text-[10px] font-medium text-slate-400 mt-1.5 uppercase leading-none tracking-widest">Create a new asset record</p>
                        </div>
                    </button>

                    <Link :href="route('admin.assets.import.smart')" 
                        class="p-6 bg-slate-50 rounded-2xl border border-slate-100 hover:border-emerald-200 hover:bg-white transition-all text-left flex items-start gap-5 grow group">
                        <div class="w-12 h-12 bg-emerald-600 text-white rounded-xl flex items-center justify-center shrink-0 shadow-md group-hover:scale-110 transition-transform">
                            <CloudArrowUpIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest">Import</p>
                            <h4 class="text-lg font-black text-slate-900 mt-1 uppercase tracking-tight leading-tight">Smart Import</h4>
                            <p class="text-[10px] font-medium text-slate-400 mt-1.5 uppercase leading-none tracking-widest">Bulk upload and validation</p>
                        </div>
                    </Link>

                    <Link :href="route('admin.assets.audit.run')" 
                        class="p-6 bg-slate-50 rounded-2xl border border-slate-100 hover:border-amber-200 hover:bg-white transition-all text-left flex items-start gap-5 grow group">
                        <div class="w-12 h-12 bg-amber-600 text-white rounded-xl flex items-center justify-center shrink-0 shadow-md group-hover:scale-110 transition-transform">
                            <PresentationChartLineIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-amber-600 uppercase tracking-widest">Blind Audit</p>
                            <h4 class="text-lg font-black text-slate-900 mt-1 uppercase tracking-tight leading-tight">Run Audit</h4>
                            <p class="text-[10px] font-medium text-slate-400 mt-1.5 uppercase leading-none tracking-widest">Scan and verify assets</p>
                        </div>
                    </Link>

                    <Link :href="route('admin.assets.bulk-assign')" 
                        class="p-6 bg-slate-50 rounded-2xl border border-slate-100 hover:border-indigo-200 hover:bg-white transition-all text-left flex items-start gap-5 grow group">
                        <div class="w-12 h-12 bg-indigo-900 text-white rounded-xl flex items-center justify-center shrink-0 shadow-md group-hover:scale-110 transition-transform">
                            <UserGroupIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-indigo-900 uppercase tracking-widest">Assignment</p>
                            <h4 class="text-lg font-black text-slate-900 mt-1 uppercase tracking-tight leading-tight">Bulk Assign</h4>
                            <p class="text-[10px] font-medium text-slate-400 mt-1.5 uppercase leading-none tracking-widest">Assign multiple assets</p>
                        </div>
                    </Link>
                </div>
            </section>

            <!-- Workspace Terminal (Active Tab Content) -->
            <main class="flex-1 relative z-10 min-h-0">
                <Transition name="tab-fade" mode="out-in">
                    <TabDashboard v-if="activeTab === 'dashboard'" :stats="stats" />
                    <TabInventory v-else-if="activeTab === 'inventory'" :assets="assets" :categories="categories" :locations="locations" :statuses="statuses" :filters="filters" :users="users" />
                    <TabKits v-else-if="activeTab === 'kits'" :kits="kits" />
                    <TabProcurement v-else-if="activeTab === 'requests'" :requests="requests" :vendors="vendors" />
                    <TabMaintenance v-else-if="activeTab === 'maintenance'" :tickets="tickets" />
                    <TabConfiguration v-else-if="activeTab === 'config'" :categories="categories" :vendors="vendors" />
                </Transition>
            </main>
        </div>

        <!-- Initialize Asset Registry Modal -->
        <PremiumModal :show="showCreateModal" @close="showCreateModal = false" title="Add Asset" subtitle="Create a new asset record">
            <form @submit.prevent="submitCreate" class="p-8 space-y-10 text-left">
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-4">
                        <InputLabel value="Asset Name" />
                        <TextInput v-model="createForm.name" placeholder="E.G. MACBOOK PRO M3" required />
                        <InputError :message="createForm.errors.name" />
                    </div>
                    <div class="space-y-4">
                        <InputLabel value="Category" />
                        <BaseSelect v-model="createForm.category_id">
                            <option value="">Select Category...</option>
                            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name.toUpperCase() }}</option>
                        </BaseSelect>
                        <InputError :message="createForm.errors.category_id" />
                    </div>
                    <div class="space-y-4">
                        <InputLabel value="Serial Number" />
                        <TextInput v-model="createForm.serial_number" placeholder="Enter serial number" class="font-mono" />
                        <InputError :message="createForm.errors.serial_number" />
                    </div>
                    <div class="space-y-4">
                        <InputLabel value="Initial Status" />
                        <div class="h-16 flex items-center px-10 bg-emerald-50 border border-emerald-100 rounded-2xl shadow-sm">
                             <div class="flex items-center gap-4">
                                 <div class="w-2.5 h-2.5 bg-emerald-500 rounded-full animate-pulse shadow-[0_0_12px_#10b981]"></div>
                                 <span class="text-[10px] font-black text-emerald-700 uppercase tracking-widest">Available</span>
                             </div>
                        </div>
                    </div>
                 </div>

                 <div class="flex items-center justify-between pt-12 border-t border-slate-100 mt-10 pb-4">
                    <button @click="showCreateModal = false" type="button" class="text-[11px] font-black uppercase tracking-[0.4em] text-slate-300 hover:text-rose-500 transition-all">Cancel</button>
                    <button type="submit" :disabled="createForm.processing" class="h-12 px-8 bg-indigo-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-sm hover:bg-indigo-700 transition-all flex items-center gap-3 active:scale-95 disabled:opacity-50 group">
                        <ArrowPathIcon v-if="createForm.processing" class="w-5 h-5 animate-spin" />
                        <CheckCircleIcon v-else class="w-5 h-5 text-white" />
                        <span>{{ createForm.processing ? 'Saving...' : 'Save Asset' }}</span>
                    </button>
                 </div>
            </form>
        </PremiumModal>
    </MainLayout>
</template>

<style scoped>
.tab-fade-enter-active, .tab-fade-leave-active { 
    transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}
.tab-fade-enter-from { opacity: 0; transform: translateY(20px) scale(0.98); }
.tab-fade-leave-to { opacity: 0; transform: translateY(-20px) scale(1.02); }

.no-scrollbar::-webkit-scrollbar { display: none; }

.shadow-3xl {
    box-shadow: 0 50px 100px -20px rgba(0, 0, 0, 0.15);
}

.font-mono {
    font-family: 'JetBrains Mono', monospace;
}
</style>
