<script setup>
import { ref, onMounted, computed } from 'vue';
import { useToastStore } from '@/stores/toast';
import axios from 'axios';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import BaseTextarea from '@/Components/BaseTextarea.vue';
import Modal from '@/Components/Modal.vue';
import { 
    CheckIcon, 
    XMarkIcon, 
    ArrowDownTrayIcon, 
    ClockIcon, 
    CheckCircleIcon, 
    UserMinusIcon, 
    InboxIcon,
    Bars3CenterLeftIcon,
    FunnelIcon,
    CursorArrowRaysIcon,
    MagnifyingGlassIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    filters: Object,
    embedded: Boolean
});

const toast = useToastStore();
const requests = ref([]);
const meta = ref({});
const loading = ref(false);
const leaveTypes = ref([]);
const perPage = ref(10);
const stats = ref({});
const selectedIds = ref([]);

// Filters
const filterForm = ref({
    status: props.filters?.status || 'pending',
});
const filterType = ref('');
const filterStart = ref('');
const filterEnd = ref('');

// Modal
const showModal = ref(false);
const selectedItem = ref(null);
const actionType = ref(''); // approved | rejected
const comment = ref('');
const submitting = ref(false);
const actionContext = ref('single'); // 'single' or 'bulk'

const columns = {
    selection: { label: '', class: 'w-10' },
    employee: { label: 'Operative Profile', class: 'w-1/4' },
    request: { label: 'Logistics Details' },
    reason: { label: 'Justification' },
    status: { label: 'Protocol Status' },
    actions: { label: 'Control Hub', align: 'right' }
};

const formatDate = (d) => new Date(d).toLocaleDateString();

const fetchRequests = async (page = 1) => {
    loading.value = true;
    selectedIds.value = []; 
    try {
        const res = await axios.get('/admin/leaves/approvals', {
            params: {
                page,
                per_page: perPage.value,
                status: filterForm.value.status,
                leave_type_id: filterType.value,
                start_date: filterStart.value,
                end_date: filterEnd.value
            },
            headers: { 'Accept': 'application/json' }
        });
        requests.value = res.data.data.data;
        meta.value = res.data.data;
        stats.value = res.data.stats || {};
    } catch (e) {
        toast.error("Failed to load requests");
    } finally {
        loading.value = false;
    }
};

const handleLimitChange = (val) => {
    perPage.value = val;
    fetchRequests(1);
};

const exportUrl = computed(() => {
    const params = new URLSearchParams();
    if(filterForm.value.status) params.append('status', filterForm.value.status);
    if(filterType.value) params.append('leave_type_id', filterType.value);
    params.append('mode', 'approvals');
    return `/leaves/export?${params.toString()}`;
});

const allSelected = computed(() => {
    if (requests.value.length === 0) return false;
    const pendingCount = requests.value.filter(r => r.status === 'pending').length;
    return pendingCount > 0 && selectedIds.value.length === pendingCount;
});

const toggleSelectAll = () => {
    if (allSelected.value) {
        selectedIds.value = [];
    } else {
        selectedIds.value = requests.value
            .filter(r => r.status === 'pending')
            .map(r => r.id);
    }
};

const initiateAction = (item, type) => {
    selectedItem.value = item;
    actionContext.value = 'single';
    actionType.value = type;
    comment.value = '';
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedItem.value = null;
};

const submitAction = async () => {
    submitting.value = true;
    try {
        if (actionContext.value === 'bulk') {
             await axios.put('/leaves/bulk-action', {
                ids: selectedIds.value,
                action: actionType.value,
                comment: comment.value
            });
            toast.success("Bulk action completed");
            selectedIds.value = []; 
        } else {
             if (!selectedItem.value) return;
             await axios.put(`/admin/leaves/${selectedItem.value.id}/action`, {
                status: actionType.value,
                comment: comment.value
            });
            toast.success(`Request ${actionType.value}`);
        }
        closeModal();
        fetchRequests(meta.value.current_page);
    } catch (e) {
        toast.error("Operation failed");
    } finally {
        submitting.value = false;
    }
};

onMounted(() => {
    fetchRequests();
});
</script>

<template>
    <div class="space-y-12 animate-in fade-in slide-in-from-bottom-5 duration-700 font-outfit">
        <!-- Dashboard Telemetry Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
             <div v-for="stat in [
                { label: 'Pending Terminal', val: stats.pending || 0, icon: ClockIcon, color: 'amber' },
                { label: 'Approved Today', val: stats.approved_today || 0, icon: CheckCircleIcon, color: 'emerald' },
                { label: 'Currently Inactive', val: stats.on_leave_today || 0, icon: UserMinusIcon, color: 'rose' },
                { label: 'Incoming Stream', val: stats.total_today || 0, icon: InboxIcon, color: 'indigo' }
             ]" :key="stat.label" class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-2xl shadow-slate-200/40 group hover:scale-105 transition-all">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">{{ stat.label }}</span>
                    <div :class="`p-3 bg-${stat.color}-50 text-${stat.color}-500 rounded-2xl group-hover:rotate-12 transition-transform`">
                        <component :is="stat.icon" class="w-5 h-5" />
                    </div>
                </div>
                <h3 class="text-3xl font-black text-slate-900 tracking-tighter">{{ stat.val }}</h3>
             </div>
        </div>

        <!-- Intelligent Controls -->
        <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl shadow-slate-200/50 overflow-hidden relative">
            <!-- Toolbar Alpha -->
            <div class="p-8 border-b border-slate-50 flex flex-col xl:flex-row justify-between items-start xl:items-center gap-8">
                <div>
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-[0.2em] mb-2 flex items-center gap-3">
                        Approval Stream
                        <span v-if="selectedIds.length > 0" class="px-2 py-0.5 rounded-lg bg-indigo-50 text-indigo-600 text-xs border border-indigo-100 animate-pulse">{{ selectedIds.length }} NODES_LOCKED</span>
                    </h3>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-widest mt-1">Lifecycle processing for absence protocols</p>
                </div>

                <div class="flex flex-wrap items-center gap-4 w-full xl:w-auto">
                    <!-- Status Filter Controller -->
                    <div class="bg-slate-50 p-1.5 rounded-2xl flex border border-slate-100 w-full sm:w-auto overflow-x-auto no-scrollbar">
                        <button 
                            v-for="st in ['pending', 'approved', 'rejected', 'all']" 
                            :key="st"
                            @click="filterForm.status = st === 'all' ? '' : st; fetchRequests(1)"
                            class="flex-1 px-5 py-2.5 rounded-xl text-sm font-black uppercase tracking-widest transition-all whitespace-nowrap"
                            :class="[
                                (filterForm.status === st || (st === 'all' && !filterForm.status))
                                ? 'bg-white text-slate-900 shadow-xl shadow-slate-200 border border-slate-100 scale-105 z-10'
                                : 'text-slate-400 hover:text-slate-600'
                            ]"
                        >
                            {{ st }}
                        </button>
                    </div>

                    <!-- Strategic Actions -->
                    <div class="flex gap-3 w-full sm:w-auto">
                        <div v-if="selectedIds.length > 0" class="flex gap-2 animate-in slide-in-from-right-5">
                             <button @click="confirmBulkAction('approved')" class="h-12 px-6 bg-emerald-600 text-white text-sm font-black uppercase tracking-widest rounded-2xl shadow-xl shadow-emerald-500/20 hover:scale-105 active:scale-95 transition-all">Bulk Approve</button>
                             <button @click="confirmBulkAction('rejected')" class="h-12 px-6 bg-rose-600 text-white text-sm font-black uppercase tracking-widest rounded-2xl shadow-xl shadow-rose-500/20 hover:scale-105 active:scale-95 transition-all">Bulk Deny</button>
                        </div>
                        <a v-else :href="exportUrl" class="h-12 px-6 bg-white border-2 border-slate-100 text-slate-400 rounded-2xl text-sm font-black uppercase tracking-widest hover:bg-slate-50 hover:text-emerald-600 transition-all flex items-center gap-3">
                            <ArrowDownTrayIcon class="w-4 h-4" />
                            Registry Export
                        </a>
                    </div>
                </div>
            </div>

            <!-- Registry Base -->
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-slate-900 border-b border-slate-800">
                            <th class="px-8 py-5 text-left w-12">
                                <input type="checkbox" :checked="allSelected" @change="toggleSelectAll" class="w-5 h-5 rounded-lg border-slate-700 bg-slate-800 text-indigo-500 focus:ring-indigo-500/20 cursor-pointer">
                            </th>
                            <th class="px-8 py-5 text-left">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Operative Profile</span>
                            </th>
                            <th class="px-8 py-5 text-left">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Absence Protocol</span>
                            </th>
                            <th class="px-8 py-5 text-left">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Justification</span>
                            </th>
                            <th class="px-8 py-5 text-center">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">State</span>
                            </th>
                            <th class="px-8 py-5 text-right w-40">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Process Command</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="item in requests" :key="item.id" class="group hover:bg-slate-50/80 transition-all duration-300">
                            <td class="px-8 py-6">
                                <input v-if="item.status === 'pending'" type="checkbox" :value="item.id" v-model="selectedIds" class="w-5 h-5 rounded-lg border-slate-200 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-indigo-400 shadow-lg group-hover:rotate-6 transition-transform">
                                        <span class="text-xs font-black uppercase">{{ item.employee?.first_name?.[0] }}{{ item.employee?.last_name?.[0] }}</span>
                                    </div>
                                    <div>
                                        <div class="text-base font-black text-slate-900 uppercase tracking-tight">{{ item.employee?.first_name }} {{ item.employee?.last_name }}</div>
                                        <div class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1">{{ item.employee?.designation }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="space-y-1.5">
                                    <span class="inline-block px-2 py-0.5 rounded bg-indigo-50 text-indigo-600 text-xs font-black uppercase tracking-widest border border-indigo-100 shadow-sm">{{ item.leave_type?.name }}</span>
                                    <div class="text-sm font-black text-slate-700 uppercase tracking-tighter">{{ formatDate(item.start_date) }} → {{ formatDate(item.end_date) }}</div>
                                    <div class="text-sm font-black text-emerald-600 uppercase tracking-widest italic">{{ item.total_days }} OPERATIVE_DAYS</div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="max-w-xs xl:max-w-sm">
                                    <p class="text-sm font-black text-slate-500 uppercase tracking-tight leading-relaxed italic line-clamp-2" :title="item.reason">" {{ item.reason }} "</p>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-center">
                                 <span 
                                    class="px-4 py-1.5 rounded-full text-xs font-black border uppercase tracking-[0.2em] shadow-sm transition-all"
                                    :class="{
                                        'bg-amber-50 text-amber-600 border-amber-100': item.status === 'pending',
                                        'bg-emerald-50 text-emerald-600 border-emerald-100': item.status === 'approved',
                                        'bg-rose-50 text-rose-600 border-rose-100': item.status === 'rejected',
                                        'bg-slate-50 text-slate-400 border-slate-100': item.status === 'cancelled',
                                    }"
                                >
                                    {{ item.status }}
                                </span>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div v-if="item.status === 'pending'" class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 translate-x-2 group-hover:translate-x-0 transition-all">
                                    <button @click="initiateAction(item, 'approved')" class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 hover:bg-emerald-600 hover:text-white transition-all shadow-sm active:scale-95" title="Protocol Authorized">
                                        <CheckIcon class="w-5 h-5" />
                                    </button>
                                    <button @click="initiateAction(item, 'rejected')" class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center border border-rose-100 hover:bg-rose-600 hover:text-white transition-all shadow-sm active:scale-95" title="Protocol Denied">
                                        <XMarkIcon class="w-5 h-5" />
                                    </button>
                                </div>
                                <div v-else class="flex justify-end">
                                     <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-300">
                                         <ShieldCheckIcon class="w-5 h-5" />
                                     </div>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="requests.length === 0">
                            <td colspan="6" class="px-8 py-32 text-center grayscale opacity-20 animate-pulse">
                                <Bars3CenterLeftIcon class="h-20 w-20 mx-auto mb-6" />
                                <p class="text-sm font-black italic tracking-[0.4em]">Zero absence protocols detected in current sector</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Nexus -->
             <div class="px-8 py-6 bg-slate-50/80 flex flex-col md:flex-row justify-between items-center gap-6 border-t border-slate-100" v-if="requests.length > 0">
                <div class="text-sm font-black text-slate-400 uppercase tracking-[0.3em]">
                    Log Segment {{ (meta.current_page - 1) * meta.per_page + 1 }} - {{ Math.min(meta.current_page * meta.per_page, meta.total) }} of {{ meta.total }} Records
                </div>
                <div class="flex gap-2">
                     <template v-for="(link, k) in meta.links" :key="k">
                        <button 
                            @click="fetchRequests(link.url ? link.url.split('page=')[1] : 1)"
                            v-if="link.url || link.active"
                            class="h-10 px-4 text-sm font-black uppercase tracking-widest rounded-xl transition-all shadow-sm flex items-center justify-center active:scale-95" 
                            :class="link.active ? 'bg-slate-900 text-white shadow-xl shadow-slate-400' : 'bg-white text-slate-400 border border-slate-200 hover:text-indigo-600 shadow-sm'"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>

        <!-- Alpha Protocol Action Modal -->
        <Modal :show="showModal" @close="closeModal" :title="null">
            <template #header>
                <div class="flex items-center gap-4">
                    <div :class="`w-12 h-12 rounded-2xl flex items-center justify-center text-white shadow-xl ${actionType === 'approved' ? 'bg-emerald-500 shadow-emerald-500/30' : 'bg-rose-500 shadow-rose-500/30'}`">
                        <component :is="actionType === 'approved' ? CheckIcon : XMarkIcon" class="w-6 h-6" />
                    </div>
                    <div>
                         <h3 class="text-xs font-black uppercase tracking-[0.2em]" :class="actionType === 'approved' ? 'text-emerald-600' : 'text-rose-600'">
                            {{ actionType === 'approved' ? 'Protocol Authorization' : 'Protocol Denial' }}
                        </h3>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-widest mt-1">Lifecycle Action Verification REQUIRED</p>
                    </div>
                </div>
            </template>
            
            <div class="space-y-8 py-4">
                <div class="p-6 bg-slate-50 rounded-[2rem] border border-slate-100">
                    <p class="text-base font-black text-slate-700 uppercase tracking-tight leading-relaxed">
                        Authorize <span class="text-indigo-600">{{ actionType }}</span> 
                        <span v-if="actionContext === 'single'">for operative <span class="italic text-slate-900">{{ selectedItem?.employee?.first_name }}</span> and archive record?</span>
                        <span v-else>for {{ selectedIds.length }} batch personnel nodes?</span>
                    </p>
                </div>
                
                <div class="space-y-3 px-2">
                    <label class="text-xs font-black text-slate-400 uppercase tracking-[0.3em] block ml-2">Audit Comment (Optional)</label>
                    <textarea 
                        v-model="comment" 
                        class="w-full bg-slate-50 border-transparent rounded-[1.5rem] p-6 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all uppercase tracking-widest resize-none"
                        placeholder="Log justification or audit notes..." 
                        rows="3"
                    ></textarea>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-3 pt-6 border-t border-slate-50">
                    <button @click="closeModal" class="px-8 py-4 text-sm font-black uppercase tracking-[0.3em] text-slate-400 hover:bg-slate-50 rounded-2xl transition-all">Abort</button>
                    <button 
                        @click="submitAction" 
                        :disabled="submitting"
                        class="px-10 py-4 text-white rounded-2xl text-sm font-black uppercase tracking-[0.4em] shadow-2xl transition-all flex items-center gap-3 active:scale-95"
                        :class="actionType === 'approved' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/30' : 'bg-rose-600 hover:bg-rose-700 shadow-rose-500/30'"
                    >
                        <ArrowPathIcon v-if="submitting" class="animate-spin h-4 w-4 text-white" />
                        <span>Confirm Profile Update</span>
                    </button>
                </div>
            </template>
        </Modal>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>