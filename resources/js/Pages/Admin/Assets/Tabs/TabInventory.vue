<script setup>
import { ref, watch } from 'vue';
import { router, Link, useForm } from '@inertiajs/vue3';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PremiumModal from '@/Components/PremiumModal.vue';
import { 
    MagnifyingGlassIcon, 
    FunnelIcon, 
    UserPlusIcon, 
    ArrowTopRightOnSquareIcon,
    CubeIcon,
    CheckCircleIcon,
    TagIcon,
    MapPinIcon,
    ArrowUpRightIcon,
    ClockIcon,
    SparklesIcon
} from '@heroicons/vue/24/solid';

const props = defineProps({
    assets: Object, // Paginated
    categories: Array,
    locations: Array,
    statuses: Array,
    filters: Object,
    users: Array
});

// Table State
const searchQuery = ref(props.filters?.search || '');
const selectedCategoryId = ref(props.filters?.category_id || '');
const selectedStatus = ref(props.filters?.status || '');
const selectedLocationId = ref(props.filters?.location_id || '');

const columns = {
    identity: { label: 'Unit Identifier', sortable: false },
    classification: { label: 'Classification', sortable: false },
    assignment: { label: 'Deployment', sortable: false },
    lifecycle: { label: 'Lifecycle', sortable: false }
};

// Filter Logic
const applyFilters = () => {
    router.get(route('admin.assets.dashboard'), {
        view: 'list',
        search: searchQuery.value,
        category_id: selectedCategoryId.value,
        status: selectedStatus.value,
        location_id: selectedLocationId.value,
        tab: 'inventory'
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

// Debounced search is handled by BaseDataTable automatically via 'search' emit
const handleSearch = (val) => {
    searchQuery.value = val;
    applyFilters();
};

const handlePageChange = (page) => {
    router.get(route('admin.assets.dashboard'), {
        view: 'list',
        page: page,
        search: searchQuery.value,
        category_id: selectedCategoryId.value,
        status: selectedStatus.value,
        location_id: selectedLocationId.value,
        tab: 'inventory'
    }, { preserveState: true, preserveScroll: true });
};

// Assign Logic
const showAssignModal = ref(false);
const selectedAssetForAssign = ref(null);
const assignForm = useForm({
    user_id: ''
});

const openAssign = (asset) => {
    selectedAssetForAssign.value = asset;
    assignForm.reset();
    showAssignModal.value = true;
};

const submitAssign = () => {
    assignForm.post(route('admin.assets.assign', selectedAssetForAssign.value.id), {
        onSuccess: () => {
            showAssignModal.value = false;
        }
    });
};

const getStatusStyles = (status) => {
    switch (status) {
        case 'Available': 
        case 'In_Stock': return 'bg-emerald-50 text-emerald-600 border-emerald-100';
        case 'Assigned': 
        case 'Deployed': return 'bg-indigo-50 text-indigo-600 border-indigo-100';
        case 'In_Service': 
        case 'Repair': return 'bg-rose-50 text-rose-600 border-rose-100';
        default: return 'bg-slate-50 text-slate-400 border-slate-100';
    }
};
</script>

<template>
    <div class="space-y-6 font-outfit animate-in fade-in slide-in-from-bottom-5 duration-700 pb-24">
        
        <!-- Filter Panel -->
        <section class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-4 gap-4">
                <div class="space-y-1.5">
                    <InputLabel value="Category" class="px-2" />
                    <BaseSelect v-model="selectedCategoryId" @change="applyFilters">
                        <option value="">All Categories</option>
                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name.toUpperCase() }}</option>
                    </BaseSelect>
                </div>
                <div class="space-y-1.5">
                    <InputLabel value="Location" class="px-2" />
                    <BaseSelect v-model="selectedLocationId" @change="applyFilters">
                        <option value="">All Locations</option>
                        <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name.toUpperCase() }}</option>
                    </BaseSelect>
                </div>
                <div class="space-y-1.5">
                    <InputLabel value="Status" class="px-2" />
                    <BaseSelect v-model="selectedStatus" @change="applyFilters">
                        <option value="">All Statuses</option>
                        <option v-for="s in statuses" :key="s" :value="s">{{ s.toUpperCase() }}</option>
                    </BaseSelect>
                </div>
                <div class="flex items-end pb-1 xl:justify-end">
                    <button @click="router.get(route('admin.assets.dashboard'), { view: 'list', tab: 'inventory' })" class="h-10 px-4 rounded-xl border border-slate-200 text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-slate-900 transition-colors">Clear Filters</button>
                </div>
            </div>
        </section>

        <!-- Asset List -->
        <BaseDataTable
            :columns="columns"
            :data="assets.data"
            :meta="assets"
            :search-placeholder="'Scan serial or unit name...'"
            @search="handleSearch"
            @page-change="handlePageChange"
        >
            <template #cell-identity="{ item }">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-center text-slate-400 shrink-0 group-hover:bg-indigo-50 group-hover:border-indigo-100 transition-colors">
                        <CubeIcon class="w-5 h-5 group-hover:text-indigo-600" />
                    </div>
                    <div>
                        <p class="text-[13px] font-black text-slate-900 uppercase tracking-tight leading-none">{{ item.name }}</p>
                        <div class="flex items-center gap-2 mt-1.5">
                             <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest font-mono">{{ item.asset_code }}</span>
                             <span v-if="item.serial_number" class="w-1 h-1 bg-slate-200 rounded-full"></span>
                             <span v-if="item.serial_number" class="text-[9px] font-bold text-slate-400 uppercase tracking-widest font-mono">SN: {{ item.serial_number }}</span>
                        </div>
                    </div>
                </div>
            </template>

            <template #cell-classification="{ item }">
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2">
                        <TagIcon class="w-3.5 h-3.5 text-indigo-500" />
                        <span class="text-[10px] font-bold text-slate-600 uppercase tracking-widest">{{ item.category?.name || 'Uncategorized' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <MapPinIcon class="w-3.5 h-3.5 text-slate-300" />
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ item.location?.name || 'Not Set' }}</span>
                    </div>
                </div>
            </template>

            <template #cell-assignment="{ item }">
                <div v-if="item.assignment?.user" class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-indigo-50 border border-indigo-100 rounded-lg flex items-center justify-center text-[10px] font-bold text-indigo-700">
                        {{ item.assignment.user.name.charAt(0) }}
                    </div>
                    <div>
                        <p class="text-[11px] font-bold text-slate-900 uppercase tracking-tight leading-none">{{ item.assignment.user.name }}</p>
                        <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-1">Assigned</p>
                    </div>
                </div>
                <div v-else class="flex flex-col gap-1">
                    <span class="text-[10px] font-bold text-slate-300 uppercase tracking-widest">Available</span>
                    <div class="h-0.5 w-5 bg-slate-100 rounded-full"></div>
                </div>
            </template>

            <template #cell-lifecycle="{ item }">
                <div class="px-2.5 py-1.5 rounded-lg border text-[9px] font-bold uppercase tracking-widest inline-flex items-center gap-2" :class="getStatusStyles(item.status)">
                    <div class="w-1.5 h-1.5 rounded-full" :class="item.status === 'Available' ? 'bg-emerald-500 animate-pulse' : 'bg-current'"></div>
                    {{ item.status.replace('_', ' ') }}
                </div>
            </template>

            <template #rowActions="{ item }">
                <div class="flex items-center justify-end gap-2 text-right">
                    <button 
                        v-if="item.status === 'Available'" 
                        @click="openAssign(item)" 
                        class="h-8 px-4 bg-indigo-600 text-white rounded-xl text-[9px] font-bold uppercase tracking-widest hover:bg-indigo-700 transition-all flex items-center gap-2 shadow-sm shrink-0 active:scale-95"
                    >
                        <UserPlusIcon class="w-3.5 h-3.5" />
                        Assign
                    </button>
                    <Link 
                        :href="route('admin.assets.show', item.id)" 
                        class="h-8 w-8 bg-white border border-slate-200 text-slate-400 rounded-xl flex items-center justify-center hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm shrink-0"
                    >
                        <ArrowUpRightIcon class="w-4 h-4" />
                    </Link>
                </div>
            </template>
        </BaseDataTable>

        <!-- Assign Asset Modal -->
        <PremiumModal 
            :show="showAssignModal" 
            @close="showAssignModal = false" 
            :title="'Assign Asset'" 
            :subtitle="'Assign ' + selectedAssetForAssign?.name + ' to a user'"
        >
            <form @submit.prevent="submitAssign" class="space-y-8 pt-6">
                <div class="bg-indigo-50 border border-indigo-100 p-6 rounded-2xl flex items-center gap-6 shadow-inner">
                    <div class="w-14 h-14 bg-white border border-indigo-200 rounded-xl flex items-center justify-center text-indigo-600 shadow-sm shrink-0">
                        <CubeIcon class="w-7 h-7" />
                    </div>
                    <div>
                        <h4 class="text-lg font-black text-slate-900 uppercase tracking-tight leading-none">{{ selectedAssetForAssign?.name }}</h4>
                        <p class="text-[9px] font-bold text-indigo-400 uppercase tracking-[0.2em] mt-2 leading-none">Asset Code: {{ selectedAssetForAssign?.asset_code }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <InputLabel value="Select User" class="px-2" />
                    <BaseSelect v-model="assignForm.user_id">
                         <option value="" disabled>Select user</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name.toUpperCase() }}</option>
                    </BaseSelect>
                </div>

                <div class="flex items-center justify-between pt-10 border-t border-slate-50">
                    <button @click="showAssignModal = false" type="button" class="text-[10px] font-bold uppercase tracking-widest text-slate-300 hover:text-rose-500 transition-all">Cancel</button>
                    <button type="submit" :disabled="assignForm.processing" class="h-14 px-8 bg-indigo-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-xl hover:bg-indigo-700 transition-all flex items-center gap-4 active:scale-95 disabled:opacity-50">
                        <CheckCircleIcon class="w-5 h-5" />
                        Confirm Assignment
                    </button>
                </div>
            </form>
        </PremiumModal>
    </div>
</template>

<style scoped>
.shadow-3xl {
    box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.08);
}
</style>
