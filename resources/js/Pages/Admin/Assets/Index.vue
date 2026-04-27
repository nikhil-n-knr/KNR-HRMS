<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref, watch } from 'vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import PremiumModal from '@/Components/PremiumModal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import { 
    InformationCircleIcon, 
    PlusIcon, 
    MagnifyingGlassIcon,
    FunnelIcon,
    CpuChipIcon,
    EyeIcon,
    ArrowPathIcon,
    TagIcon,
    CheckCircleIcon,
    UserCircleIcon,
    WrenchIcon,
    PrinterIcon,
    ArrowLeftIcon,
    InboxStackIcon,
    CubeIcon,
    ArchiveBoxIcon,
    ArrowUpRightIcon,
    BoltIcon,
    SparklesIcon,
    UserIcon,
    FingerPrintIcon,
    CommandLineIcon
} from '@heroicons/vue/24/solid';

const props = defineProps({
    assets: Object,
    filters: Object,
    categories: Array
});

const showModal = ref(false);

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const category = ref(props.filters?.category || '');

// Debounced search
let timeout = null;
watch([search, status, category], () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('admin.assets.index'), { 
            search: search.value, 
            status: status.value, 
            category: category.value 
        }, { preserveState: true, preserveScroll: true });
    }, 300);
});

const columns = [
    { key: 'equipment', label: 'Resource Node', sortable: true },
    { key: 'id_serial', label: 'Identity / Hash', sortable: true },
    { key: 'category', label: 'Classification', sortable: true },
    { key: 'condition', label: 'Health Status', sortable: true },
    { key: 'assignment', label: 'Assigned Liaison', sortable: true },
    { key: 'actions', label: '', sortable: false, align: 'right' }
];

const form = useForm({
    name: '',
    category_id: props.categories?.[0]?.id || '',
    is_serialized: true,
    serial_number: '',
    quantity: 1,
    purchase_date: '',
    purchase_cost: ''
});

const submit = () => {
    form.post(route('admin.assets.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
            form.category_id = props.categories?.[0]?.id || '';
        }
    });
};

const getStatusStyle = (s) => {
    switch(s) {
        case 'Available': return 'bg-emerald-50 text-emerald-600 border-emerald-100 shadow-sm';
        case 'Assigned': return 'bg-indigo-50 text-indigo-600 border-indigo-100 shadow-sm';
        case 'In Service': return 'bg-rose-50 text-rose-600 border-rose-100 shadow-sm animate-pulse';
        default: return 'bg-slate-50 text-slate-400 border-slate-100';
    }
};

const getStatusLabel = (s) => {
    switch(s) {
        case 'Available': return 'READY_TO_USE';
        case 'Assigned': return 'OPS_ALLOCATED';
        case 'In Service': return 'UNDER_REPAIR';
        default: return s?.toUpperCase();
    }
};
</script>

<template>
    <Head title="Asset Matrix Master" />
    
    <MainLayout>
        <div class="space-y-8 font-outfit animate-in fade-in slide-in-from-bottom-10 duration-1000 -m-8 p-12 bg-slate-50 min-h-screen relative overflow-hidden text-left">
            
            <!-- Strategic Command Header -->
            <header class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-8 bg-white rounded-3xl p-10 shadow-sm relative overflow-hidden group border border-slate-200 text-left">
                <div class="absolute -right-32 -top-32 w-[600px] h-[600px] bg-indigo-50 rounded-full blur-[140px] group-hover:bg-indigo-100/50 transition-all duration-1000"></div>
                <div class="absolute -left-32 bottom-0 w-96 h-96 bg-emerald-50 rounded-full blur-[120px] group-hover:bg-emerald-100/50 transition-all duration-1000"></div>
                
                <div class="flex items-center gap-8 relative z-10 text-left">
                    <div class="w-16 h-16 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-center text-indigo-500 shadow-sm group-hover:rotate-6 transition-transform duration-700 shrink-0">
                        <ArchiveBoxIcon class="w-8 h-8" />
                    </div>
                    <div class="text-left">
                        <div class="flex items-center gap-4">
                            <h1 class="text-3xl font-black text-slate-900 uppercase tracking-tight leading-none">Global Matrix</h1>
                            <div class="group/tooltip relative flex items-center">
                                <InformationCircleIcon class="w-6 h-6 text-indigo-400 cursor-help opacity-70 hover:opacity-100 transition-opacity" />
                            </div>
                        </div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2.5 drop-shadow-sm leading-none">
                            CENTRAL_INTELLIGENCE_REPOSITORY_v2.0
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-4 relative z-10 w-full xl:w-auto">
                   <Link :href="route('admin.assets.procurement.index')" class="h-12 px-6 bg-slate-50 border border-slate-200 text-slate-600 rounded-xl text-[9px] font-black uppercase tracking-widest shadow-sm hover:bg-white hover:border-indigo-200 transition-all active:scale-95 flex items-center justify-center gap-3 whitespace-nowrap group/buy shrink-0">
                        <PlusIcon class="w-4 h-4 text-emerald-500 group-hover/buy:scale-125 transition-transform" />
                        INITIATE_PROCUREMENT
                    </Link>
                    <button @click="showModal = true"
                        class="h-14 px-8 bg-slate-900 text-white rounded-2xl shadow-lg hover:bg-indigo-600 font-black text-[10px] uppercase tracking-widest transition-all flex items-center gap-4 active:scale-95 group border border-slate-800">
                        <CubeIcon class="w-6 h-6 group-hover:rotate-90 transition-transform duration-700" />
                        REGISTER_NEW_NODE
                    </button>
                    <Link :href="route('admin.assets.dashboard')" class="w-14 h-14 bg-white border border-slate-200 text-slate-400 rounded-2xl flex items-center justify-center hover:bg-slate-50 hover:text-indigo-600 transition-all shadow-sm shrink-0 group/cfg active:scale-90">
                        <EyeIcon class="w-6 h-6 group-hover/cfg:scale-110 transition-transform duration-700" />
                    </Link>
                </div>
            </header>

            <!-- Intelligence Filters -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 relative z-10 text-left">
                <div class="lg:col-span-2 relative group uppercase font-black">
                    <MagnifyingGlassIcon class="absolute left-8 top-1/2 -translate-y-1/2 w-6 h-6 text-slate-400 group-focus-within:text-indigo-500 transition-all duration-500" />
                    <input v-model="search" placeholder="SEARCH_BY_IDENTITY_OR_HASH..."
                        class="w-full h-16 bg-white border border-slate-200 rounded-2xl pl-20 pr-10 text-slate-900 placeholder:text-slate-300 focus:bg-white focus:ring-8 focus:ring-indigo-500/5 focus:border-indigo-400 transition-all uppercase tracking-widest text-[11px] shadow-sm group-hover:shadow-md duration-500 font-bold">
                </div>
                <div class="relative group">
                    <FunnelIcon class="absolute left-8 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-indigo-500 transition-colors" />
                    <select v-model="status" class="w-full h-16 bg-white border border-slate-200 rounded-2xl pl-18 pr-10 text-[10px] font-bold text-slate-400 uppercase tracking-widest focus:ring-8 focus:ring-indigo-500/5 focus:border-indigo-400 transition-all appearance-none cursor-pointer shadow-sm group-hover:shadow-md duration-500">
                        <option value="">SHOW_ALL_STATUS</option>
                        <option value="Available">READY_TO_USE</option>
                        <option value="Assigned">OPS_ALLOCATED</option>
                        <option value="In Service">UNDER_REPAIR</option>
                    </select>
                </div>
                <div class="relative group">
                    <InboxStackIcon class="absolute left-8 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-indigo-500 transition-colors" />
                    <select v-model="category" class="w-full h-16 bg-white border border-slate-200 rounded-2xl pl-18 pr-10 text-[10px] font-bold text-slate-400 uppercase tracking-widest focus:ring-8 focus:ring-indigo-500/5 focus:border-indigo-400 transition-all appearance-none cursor-pointer shadow-sm group-hover:shadow-md duration-500">
                        <option value="">ALL_CLASSIFICATIONS</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name.toUpperCase() }}</option>
                    </select>
                </div>
            </div>

            <!-- Main Data Terminal -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden relative group z-10 transition-all duration-700">
                <BaseDataTable
                    :rows="assets.data"
                    :columns="columns"
                    selectable
                    search-placeholder="SEARCH_MATRIX_RECORDS..."
                    class="min-h-[500px]"
                >
                    <template #cell-equipment="{ row }">
                        <div class="flex items-center gap-5">
                            <div class="w-12 h-12 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-center text-slate-400 group-hover:bg-slate-900 group-hover:text-indigo-400 transition-all duration-700 shadow-sm shrink-0">
                                <CpuChipIcon class="w-6 h-6" />
                            </div>
                            <div class="flex flex-col gap-0.5 text-left">
                                <span class="text-lg font-black text-slate-900 uppercase tracking-tight leading-none group-hover:text-indigo-600 transition-colors">{{ row.name }}</span>
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none mt-1">NODE_IDENTIFIER</span>
                            </div>
                        </div>
                    </template>

                    <template #cell-id_serial="{ row }">
                        <div v-if="row.is_serialized" class="flex flex-col gap-1.5 text-left">
                            <span class="text-[8px] font-bold text-slate-300 uppercase tracking-widest leading-none flex items-center gap-1.5">
                                <FingerPrintIcon class="w-2.5 h-2.5" /> UNIQUE_HASH
                            </span>
                            <span class="text-sm font-mono font-black text-slate-900 tracking-tight group-hover:text-indigo-700 transition-colors">{{ row.serial_number }}</span>
                        </div>
                        <div v-else class="flex flex-col gap-1.5 text-left">
                            <span class="text-[8px] font-bold text-slate-300 uppercase tracking-widest leading-none">BULK_QUANTITY</span>
                            <span class="text-xl font-black text-slate-900 tracking-tight leading-none">{{ row.quantity }} <span class="text-[9px] text-slate-400 ml-1">UNITS</span></span>
                        </div>
                    </template>

                    <template #cell-category="{ row }">
                        <div class="flex items-center gap-3 w-fit px-4 h-8 border border-slate-100 bg-slate-50 rounded-lg text-[9px] font-bold text-slate-500 uppercase tracking-widest shadow-sm">
                            <TagIcon class="w-3.5 h-3.5 text-emerald-500" />
                            {{ row.category?.name?.toUpperCase() }}
                        </div>
                    </template>

                    <template #cell-condition="{ row }">
                        <div :class="getStatusStyle(row.status)" class="px-4 py-1.5 border rounded-xl text-[8px] font-bold uppercase tracking-widest shadow-sm flex items-center gap-2.5 w-fit">
                            <div class="w-1.5 h-1.5 rounded-full" :class="row.status === 'Available' ? 'bg-emerald-500' : (row.status === 'In Service' ? 'bg-rose-500 animate-pulse' : 'bg-indigo-500')"></div>
                            {{ getStatusLabel(row.status) }}
                        </div>
                    </template>

                    <template #cell-assignment="{ row }">
                        <div v-if="row.assignment?.user" class="flex items-center gap-4 group/user text-left">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 border border-slate-200 shadow-sm overflow-hidden shrink-0 group-hover/user:scale-105 transition-transform">
                               <img v-if="row.assignment.user.profile_photo_url" :src="row.assignment.user.profile_photo_url" class="w-full h-full object-cover">
                               <UserIcon v-else class="w-5 h-5" />
                            </div>
                            <div class="flex flex-col text-left">
                                <span class="text-[10px] font-black text-slate-900 uppercase tracking-tight leading-none">{{ row.assignment.user.name }}</span>
                                <span class="text-[8px] font-bold text-emerald-600 uppercase tracking-widest mt-1 leading-none">{{ row.assignment.user.department?.name || 'OPERATIONAL_UNIT' }}</span>
                            </div>
                        </div>
                        <div v-else class="flex items-center gap-2.5">
                             <div class="w-1 h-1 bg-emerald-400 rounded-full animate-pulse"></div>
                             <span class="text-[9px] font-bold text-slate-300 uppercase tracking-widest leading-none">AVAILABLE_FOR_OPS</span>
                        </div>
                    </template>

                    <template #cell-actions="{ row }">
                        <div class="flex items-center justify-end gap-3 opacity-0 group-hover/row:opacity-100 transition-all -translate-x-2 group-hover:translate-x-0">
                            <Link :href="route('admin.assets.show', row.id)" class="w-10 h-10 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-400 hover:bg-slate-900 hover:text-white transition-all shadow-sm active:scale-75 group/view">
                                <EyeIcon class="h-5 w-5 group-hover/view:scale-110 transition-transform" />
                            </Link>
                            <Link :href="route('admin.assets.label', row.id)" class="w-10 h-10 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-400 hover:bg-indigo-600 hover:text-white transition-all shadow-sm active:scale-75 group/print">
                                <PrinterIcon class="h-5 w-5 group-hover/print:scale-110 transition-transform" />
                            </Link>
                        </div>
                    </template>
                </BaseDataTable>
            </div>
        </div>

        <!-- Registration Portal Modal -->
        <PremiumModal 
            :show="showModal" 
            @close="showModal = false" 
            title="Registry Initiation" 
            subtitle="Register new hardware resource to the global matrix"
        >
            <form @submit.prevent="submit" class="p-6 space-y-8 text-left">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-left">
                    <div class="space-y-3">
                        <InputLabel value="Resource Classification" />
                        <BaseSelect v-model="form.category_id">
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name.toUpperCase() }}</option>
                        </BaseSelect>
                    </div>
                    
                    <div class="space-y-3">
                        <InputLabel value="Full Resource Identifier (Name)" />
                        <TextInput v-model="form.name" placeholder="E.G. MACBOOK PRO M3 MAX" required />
                    </div>

                    <div class="col-span-full">
                        <div class="flex items-center gap-6 bg-slate-900 p-6 rounded-2xl text-white transition-all group/toggle cursor-pointer hover:bg-indigo-900 shadow-lg scale-[1.01] border border-slate-800" @click="form.is_serialized = !form.is_serialized">
                            <div class="w-12 h-12 rounded-xl border flex items-center justify-center transition-all shadow-md shrink-0" :class="form.is_serialized ? 'bg-white border-white text-indigo-600 rotate-6' : 'bg-white/10 border-white/10 text-white/20'">
                                <FingerPrintIcon class="w-6 h-6" />
                            </div>
                            <div class="flex flex-col gap-1.5 text-left">
                                <span class="text-lg font-black uppercase tracking-tight leading-none">Sequential Identity?</span>
                                <p class="text-[9px] font-bold text-indigo-400/60 uppercase tracking-widest leading-none">Activate for nodes requiring unique hash tracking (Serial #).</p>
                            </div>
                            <div class="ml-auto">
                                 <div class="w-12 h-6 bg-white/10 rounded-full relative p-1 transition-all duration-500" :class="form.is_serialized ? 'bg-indigo-500' : ''">
                                    <div class="w-4 h-4 bg-white rounded-full transition-all duration-500" :class="form.is_serialized ? 'translate-x-6' : 'translate-x-0'"></div>
                                 </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="form.is_serialized" class="space-y-3 col-span-full">
                        <InputLabel value="Primary Identity Hash (Serial Number)" />
                        <TextInput v-model="form.serial_number" placeholder="UNIQUE_S_N_XXXXXX" class="font-mono uppercase tracking-widest" />
                    </div>
                    <div v-else class="space-y-3 col-span-full">
                         <InputLabel value="Bulk Node Volume (Quantity)" />
                        <TextInput v-model="form.quantity" type="number" min="1" class="font-mono" />
                    </div>

                    <div class="space-y-3">
                        <InputLabel value="Acquisition Timestamp" />
                        <TextInput v-model="form.purchase_date" type="date" />
                    </div>
                    <div class="space-y-3">
                        <InputLabel value="Capital Outlay (Cost)" />
                        <div class="relative group">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 font-black transition-colors group-focus-within:text-indigo-400">₹</div>
                            <TextInput v-model="form.purchase_cost" type="number" step="0.01" class="pl-10" placeholder="0.00" />
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-10 border-t border-slate-100 mt-8 pb-4">
                    <button type="button" @click="showModal = false"
                        class="text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-rose-500 transition-all">Abort Registry</button>
                    <button type="submit" :disabled="form.processing"
                        class="h-14 px-10 bg-slate-900 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-xl hover:bg-indigo-600 transition-all flex items-center gap-4 active:scale-95 disabled:opacity-50 group/submitbtn">
                        <ArrowPathIcon v-if="form.processing" class="w-5 h-5 animate-spin" />
                        <CheckCircleIcon v-else class="w-5 h-5 text-indigo-400 group-hover/submitbtn:scale-125 transition-transform duration-500" />
                        <span>{{ form.processing ? 'SYNCING_MATRIX...' : 'COMMIT_TO_MATRIX' }}</span>
                    </button>
                </div>
            </form>
        </PremiumModal>
    </MainLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 10px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(15, 23, 42, 0.05);
    border-radius: 20px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(15, 23, 42, 0.1);
}
</style>
