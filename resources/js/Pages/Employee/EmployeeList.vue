<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import BankDetailImportModal from '@/Components/Modals/BankDetailImportModal.vue';
import debounce from 'lodash/debounce';
import { 
    MagnifyingGlassIcon, 
    FunnelIcon, 
    UserPlusIcon, 
    ArrowUpTrayIcon,
    TableCellsIcon,
    Squares2X2Icon,
    ChevronRightIcon,
    IdentificationIcon,
    BriefcaseIcon,
    MapPinIcon,
    CalendarDaysIcon,
    ArrowPathRoundedSquareIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    employees: Object,
    filters: Object,
    departments: Array
});

// Search & Filter State
const search = ref(props.filters.search || '');
const departmentId = ref(props.filters.department_id || '');
const status = ref(props.filters.status || '');
const viewType = ref('table'); // 'table' or 'grid'

const showImportModal = ref(false);
const reloadData = () => {
    router.reload({ only: ['employees'] });
};

// Debounced Search
watch([search, departmentId, status], debounce(() => {
    router.get(route('admin.employees.index'), { 
        search: search.value, 
        department_id: departmentId.value,
        status: status.value
    }, { 
        preserveState: true, 
        replace: true 
    });
}, 300));

const getStatusStyles = (status) => {
    switch (status) {
        case 'active': return 'bg-emerald-50 text-emerald-600 border-emerald-100 shadow-emerald-500/5';
        case 'probation': return 'bg-blue-50 text-blue-600 border-blue-100 shadow-blue-500/5';
        case 'notice_period': return 'bg-amber-50 text-amber-600 border-amber-100 shadow-amber-500/5';
        case 'terminated':
        case 'resigned': return 'bg-rose-50 text-rose-600 border-rose-100 shadow-rose-500/5';
        case 'on_leave': return 'bg-indigo-50 text-indigo-600 border-indigo-100 shadow-indigo-500/5';
        default: return 'bg-slate-50 text-slate-400 border-slate-100';
    }
};

const formatStatus = (s) => s?.replace(/_/g, ' ').toUpperCase();
</script>

<template>
    <Head title="Workforce Management" />
    <MainLayout>
        <div class="min-h-screen flex flex-col font-outfit px-4 md:px-8">
            <!-- Strategic Command Header -->
            <div class="py-8 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                <div class="flex items-center gap-5">
                    <div class="p-4 bg-slate-900 border border-slate-800 rounded-3xl text-indigo-400 shadow-2xl shadow-slate-200/50 relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <UserGroupIcon class="w-8 h-8 relative z-10" />
                    </div>
                    <div>
                        <h1 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                            Workforce Terminal
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-indigo-50 text-indigo-600 border border-indigo-100 uppercase tracking-widest">Live Registry</span>
                        </h1>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] mt-1">Personnel management & operative deployment index</p>
                    </div>
                </div>

                <!-- Strategic Actions -->
                <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                    <button 
                        v-if="$page.props.auth.user.role?.name === 'Admin' || $page.props.auth.user.role?.name === 'Super Admin'" 
                        @click="showImportModal = true"
                        class="h-12 px-6 bg-white border-2 border-slate-100 text-slate-500 rounded-2xl text-sm font-black uppercase tracking-[0.2em] hover:bg-slate-50 hover:text-indigo-600 transition-all shadow-sm active:scale-95 flex items-center gap-3"
                    >
                        <ArrowUpTrayIcon class="w-4 h-4" />
                        Bulk Sync
                    </button>
                    <a :href="route('admin.employees.export', { search, department_id: departmentId, status })" class="h-12 px-6 bg-white border-2 border-slate-100 text-slate-500 rounded-2xl text-sm font-black uppercase tracking-[0.2em] hover:bg-slate-50 hover:text-emerald-600 transition-all shadow-sm active:scale-95 flex items-center gap-3">
                        <TableCellsIcon class="w-4 h-4" />
                        Export
                    </a>
                    <Link :href="route('admin.employees.create')" class="flex-1 lg:flex-none h-12 px-8 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.2em] shadow-2xl shadow-slate-200 hover:bg-indigo-600 transition-all active:scale-95 flex items-center justify-center gap-3 group">
                        <UserPlusIcon class="w-4 h-4 text-indigo-400 group-hover:rotate-12 transition-transform" />
                        <span>Onboard Operative</span>
                    </Link>
                </div>
            </div>

            <!-- Intelligent Intelligence Bar -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-8">
                <!-- Predictive Search -->
                <div class="md:col-span-6 relative group">
                    <MagnifyingGlassIcon class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors" />
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="SCAN_BY_OPERATIVE_NAME_CODE_OR_ALIAS..." 
                        class="w-full h-14 bg-white border-none rounded-[1.25rem] pl-12 pr-4 text-base font-black uppercase tracking-widest text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-sm transition-all"
                    >
                </div>
                
                <!-- Filter Matrix -->
                <div class="md:col-span-3 relative">
                    <select v-model="departmentId" class="w-full h-14 bg-white border-none rounded-[1.25rem] pl-12 pr-10 text-sm font-black uppercase tracking-widest text-slate-600 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer shadow-sm shadow-slate-100">
                        <option value="">ALL_DEPARTMENTS</option>
                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name.toUpperCase() }}</option>
                    </select>
                    <BriefcaseIcon class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-300" />
                    <FunnelIcon class="w-3.5 h-3.5 absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                </div>

                <div class="md:col-span-3 relative">
                    <select v-model="status" class="w-full h-14 bg-white border-none rounded-[1.25rem] pl-12 pr-10 text-sm font-black uppercase tracking-widest text-slate-600 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer shadow-sm shadow-slate-100">
                        <option value="">ALL_PROTOCOLS</option>
                        <option value="active">ACTIVE_SERVICE</option>
                        <option value="probation">PROBATION_MODE</option>
                        <option value="on_leave">TEMPORARY_IDLE</option>
                        <option value="resigned">OFFBOARDED</option>
                        <option value="terminated">DECOMMISSIONED</option>
                    </select>
                    <ArrowPathRoundedSquareIcon class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-300" />
                    <FunnelIcon class="w-3.5 h-3.5 absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                </div>
            </div>

            <!-- Registry Terminal -->
            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl shadow-slate-200/50 overflow-hidden relative min-h-[600px] mb-12">
                <!-- Desktop Tabular View -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-slate-900 border-b border-slate-800">
                                <th class="px-8 py-6 text-left w-24">
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Rank</span>
                                </th>
                                <th class="px-8 py-6 text-left">
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Operative Profile</span>
                                </th>
                                <th class="px-8 py-6 text-left">
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Deployment</span>
                                </th>
                                <th class="px-8 py-6 text-center">
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Status protocol</span>
                                </th>
                                <th class="px-8 py-6 text-left">
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Temporal Entry</span>
                                </th>
                                <th class="px-8 py-6 text-right">
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Control Hub</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="(emp, idx) in employees.data" :key="emp.id" class="group hover:bg-slate-50 transition-all duration-300">
                                <td class="px-8 py-6">
                                    <span class="text-sm font-black text-slate-300 font-mono tracking-tighter">#{{ (employees.current_page - 1) * employees.per_page + idx + 1 }}</span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-5">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-900 flex items-center justify-center text-indigo-400 shadow-xl border-2 border-white group-hover:bg-indigo-600 group-hover:text-white transition-all transform group-hover:rotate-6">
                                            <span class="text-sm font-black uppercase">{{ emp.first_name[0] }}{{ emp.last_name[0] }}</span>
                                        </div>
                                        <div>
                                            <div class="text-lg font-black text-slate-900 uppercase tracking-tight group-hover:text-indigo-600 transition-colors">{{ emp.first_name }} {{ emp.last_name }}</div>
                                            <div class="flex items-center gap-2 mt-2">
                                                <IdentificationIcon class="w-3.5 h-3.5 text-slate-300" />
                                                <span class="text-sm font-black text-slate-400 uppercase tracking-widest font-mono">{{ emp.employee_code }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-start gap-4">
                                        <div class="space-y-1.5 border-l-2 border-slate-100 pl-4 group-hover:border-indigo-100 transition-colors">
                                            <div class="text-sm font-black text-slate-800 uppercase tracking-tight">{{ emp.department?.name || 'LOGISTICS_NODE' }}</div>
                                            <div class="text-xs font-black text-slate-400 uppercase tracking-widest">{{ emp.designation }}</div>
                                        </div>
                                        <div class="flex items-center gap-1 text-slate-300 group-hover:text-amber-500 transition-colors">
                                            <MapPinIcon class="w-3 h-3" />
                                            <span class="text-xs font-black uppercase tracking-widest">{{ emp.location?.name }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <span class="px-4 py-1.5 rounded-full text-xs font-black border uppercase tracking-[0.2em] shadow-sm transition-all group-hover:scale-105 inline-block" :class="getStatusStyles(emp.status)">
                                        {{ formatStatus(emp.status) }}
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-3">
                                        <CalendarDaysIcon class="w-4 h-4 text-slate-200" />
                                        <span class="text-sm font-black text-slate-500 uppercase tracking-widest">{{ new Date(emp.joining_date).toLocaleDateString() }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 translate-x-2 group-hover:translate-x-0 transition-all">
                                        <Link :href="route('admin.employees.show', emp.id)" class="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-sm font-black uppercase tracking-[0.2em] hover:bg-indigo-600 transition-all shadow-xl shadow-slate-200 active:scale-95">Access Profile</Link>
                                        <Link v-if="!['terminated', 'resigned'].includes(emp.status)" :href="route('hr.settlement.create', emp.id)" class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 hover:border-rose-100 transition-all active:scale-95" title="Initiate Offboarding">
                                             <ArrowPathRoundedSquareIcon class="w-4 h-4" />
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="employees.data.length === 0">
                                <td colspan="6" class="px-8 py-32 text-center grayscale opacity-20 animate-pulse">
                                    <UserGroupIcon class="h-20 w-20 mx-auto mb-6" />
                                    <p class="text-base font-black text-slate-400 uppercase tracking-[0.4em]">Zero personnel detected in current sector</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Adaptive Registry -->
                <div class="lg:hidden divide-y divide-slate-50 bg-slate-50/50">
                     <div v-for="emp in employees.data" :key="'mb-'+emp.id" class="p-6 space-y-6 group relative bg-white hover:bg-slate-50 active:scale-[0.98] transition-all overflow-hidden">
                        <!-- Card Background Accent -->
                        <div class="absolute -right-4 top-0 w-1.5 h-full opacity-0 group-hover:opacity-100 bg-indigo-500 transition-all"></div>
                        
                        <div class="flex justify-between items-start relative z-10">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-16 h-16 rounded-3xl bg-slate-900 border-2 border-white flex items-center justify-center text-white shadow-xl shadow-slate-200 shrink-0 group-hover:bg-indigo-600 group-hover:rotate-3 transition-all font-black">
                                    <span class="text-lg">{{ emp.first_name[0] }}{{ emp.last_name[0] }}</span>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-sm font-black text-slate-900 uppercase tracking-tight leading-none mb-3 truncate">{{ emp.first_name }} {{ emp.last_name }}</h4>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="text-xs font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100 font-mono">{{ emp.employee_code }}</span>
                                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest opacity-60 truncate">{{ emp.designation }}</span>
                                    </div>
                                </div>
                            </div>
                            <span class="px-3 py-1 text-xs font-black rounded-full border uppercase tracking-widest shadow-sm shrink-0" :class="getStatusStyles(emp.status)">
                                {{ formatStatus(emp.status) }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-4 relative z-10">
                            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 space-y-2 group-hover:bg-white transition-colors h-full">
                                <span class="text-xs font-black text-slate-400 uppercase tracking-widest block">Deployment</span>
                                <span class="block text-sm font-black text-slate-700 uppercase tracking-tight leading-tight">{{ emp.department?.name || 'GENERAL_SECTOR' }}</span>
                            </div>
                            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 space-y-2 group-hover:bg-white transition-colors h-full">
                                <span class="text-xs font-black text-slate-400 uppercase tracking-widest block">Regional Sector</span>
                                <span class="block text-sm font-black text-slate-700 uppercase tracking-tight leading-tight">{{ emp.location?.name || 'CENTRAL_HUB' }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 relative z-10 pt-2">
                             <Link :href="route('admin.employees.show', emp.id)" class="flex-[3] h-12 bg-slate-900 text-white rounded-2xl flex items-center justify-center gap-3 text-sm font-black uppercase tracking-[0.2em] shadow-xl shadow-slate-200 active:scale-95 transition-all group-hover:bg-indigo-600">
                                 PROFILE_DEEP_LINK
                                 <ChevronRightIcon class="h-4 w-4" />
                             </Link>
                             <Link v-if="!['terminated', 'resigned'].includes(emp.status)" :href="route('hr.settlement.create', emp.id)" class="flex-1 h-12 bg-white border-2 border-slate-100 text-slate-300 rounded-2xl flex items-center justify-center hover:bg-rose-50 hover:text-rose-600 hover:border-rose-100 transition-all active:scale-95 shadow-sm">
                                 <ArrowPathRoundedSquareIcon class="w-5 h-5" />
                             </Link>
                        </div>
                     </div>
                </div>

                <!-- Neural Pagination -->
                 <div class="px-8 py-6 bg-slate-50/80 flex flex-col md:flex-row justify-between items-center gap-6 border-t border-slate-100" v-if="employees.data?.length > 0">
                    <div class="text-sm font-black text-slate-400 uppercase tracking-[0.3em]">
                        Operative Stream {{ (employees.current_page - 1) * employees.per_page + 1 }} - {{ Math.min(employees.current_page * employees.per_page, employees.total) }} of {{ employees.total }} Nodes
                    </div>
                    <div class="flex gap-2">
                         <template v-for="(link, k) in employees.links" :key="k">
                            <Link 
                                v-if="link.url"
                                :href="link.url" 
                                class="h-10 px-4 text-sm font-black uppercase tracking-widest rounded-xl transition-all shadow-sm flex items-center justify-center active:scale-95" 
                                :class="link.active ? 'bg-slate-900 text-white shadow-xl shadow-slate-400' : 'bg-white text-slate-400 border border-slate-200 hover:text-indigo-600 shadow-sm'"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <BankDetailImportModal 
            :show="showImportModal" 
            @close="showImportModal = false" 
            @success="reloadData" 
        />
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
</style>
