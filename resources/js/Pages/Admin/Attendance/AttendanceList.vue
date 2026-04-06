<template>
  <component :is="embedded ? 'div' : AttendanceLayout" title="Daily Monitor" activeTab="daily_log" v-bind="$props">
    <Head v-if="!embedded" title="Attendance Portal" />
    
    <div class="space-y-8 pb-12 px-4 md:px-0 font-outfit">
        <!-- Specialized Command Header -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <div class="flex items-center gap-5">
                <div class="p-4 bg-slate-900 border border-slate-800 rounded-2xl text-emerald-400 shadow-xl shadow-slate-200/50 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <svg class="w-7 h-7 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <div>
                    <h2 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                        Attendance Matrix
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase tracking-widest">Live Registry</span>
                    </h2>
                    <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] mt-1">Enterprise vital signal monitoring hub</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3 w-full lg:w-auto">
                <button @click="exportData" class="flex-1 lg:flex-none flex items-center justify-center gap-3 bg-slate-900 text-white h-12 px-8 rounded-2xl hover:bg-emerald-600 transition-all text-sm font-black uppercase tracking-[0.2em] shadow-xl shadow-slate-200 active:scale-95 group">
                    <svg class="w-4 h-4 text-emerald-400 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    <span>Export Matrix Report</span>
                </button>
            </div>
        </div>

        <!-- Intelligence Filter Array -->
        <div class="bg-white/40 backdrop-blur-xl border border-white p-1.5 rounded-3xl shadow-sm">
            <EmployeeFilterBar 
                :departments="departments" 
                :locations="locations || []"
                @update="handleFilterUpdate"
            >
                <template #extra>
                    <div class="flex flex-col lg:flex-row gap-3 items-stretch lg:items-center flex-1 lg:flex-none">
                        <div class="relative group flex-1 lg:w-48">
                            <select v-model="filterForm.status" class="w-full h-11 bg-white border-none rounded-xl pl-4 pr-10 text-sm font-black uppercase tracking-widest text-slate-700 focus:ring-4 focus:ring-emerald-500/10 appearance-none cursor-pointer shadow-sm">
                                <option value="">ALL_STATUS</option>
                                <option value="Present">PRESENT</option>
                                <option value="Absent">ABSENT</option>
                                <option value="Late">LATENCY</option>
                                <option value="Half Day">PARTIAL_SYNC</option>
                            </select>
                            <svg class="w-3 h-3 absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>

                        <div class="flex items-center gap-3 bg-white rounded-xl px-4 h-11 shadow-sm border border-transparent focus-within:ring-4 focus-within:ring-emerald-500/10 transition-all">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v12a2 2 0 002 2z"></path></svg>
                            <div class="flex items-center gap-2">
                                <input v-model="filterForm.date_from" type="date" class="bg-transparent border-none p-0 text-sm font-black text-slate-700 focus:ring-0 uppercase h-full w-28">
                                <span class="text-slate-200 text-sm font-black mx-1 opacity-50">/</span>
                                <input v-model="filterForm.date_to" type="date" class="bg-transparent border-none p-0 text-sm font-black text-slate-700 focus:ring-0 uppercase h-full w-28">
                            </div>
                        </div>
                    </div>
                </template>
            </EmployeeFilterBar>
        </div>

        <!-- Telemetry Table -->
        <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-2xl shadow-slate-200/50 overflow-hidden min-h-[400px] relative">
            <div v-if="loading" class="absolute inset-0 bg-white/60 backdrop-blur-md flex items-center justify-center z-50">
                <div class="flex flex-col items-center gap-4">
                    <div class="w-12 h-12 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin shadow-xl"></div>
                    <span class="text-sm font-black text-emerald-600 uppercase tracking-[0.3em]">Syncing Matrix Protocol...</span>
                </div>
            </div>

            <!-- Desktop Table View -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-slate-900 border-b border-slate-800">
                            <th class="px-6 py-5 text-left w-32">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Anchor</span>
                            </th>
                            <th class="px-6 py-5 text-left">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Operative Registry</span>
                            </th>
                            <th class="px-6 py-5 text-left">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Deployment</span>
                            </th>
                            <th class="px-6 py-5 text-left">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Telemetry Window</span>
                            </th>
                            <th class="px-6 py-5 text-left">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Work Metrics</span>
                            </th>
                            <th class="px-6 py-5 text-right w-32">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Validation</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-if="logs && logs.data.length === 0" class="text-center">
                            <td colspan="6" class="p-32">
                                <div class="flex flex-col items-center gap-4 opacity-20 grayscale">
                                    <svg class="w-16 h-16 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.3em]">No Intelligence Records Found</span>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="log in logs?.data || []" :key="log.id" class="group hover:bg-slate-50 transition-all duration-300">
                            <td class="px-6 py-6">
                                <span class="text-base font-black text-slate-600 uppercase tracking-tighter tabular-nums">{{ new Date(log.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) }}</span>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-11 h-11 rounded-2xl bg-slate-900 flex items-center justify-center text-white font-black text-xs border-2 border-white shadow-lg flex-shrink-0 group-hover:bg-emerald-600 transition-all overflow-hidden">
                                        {{ log.employee?.first_name?.[0] }}{{ log.employee?.last_name?.[0] }}
                                    </div>
                                    <div class="truncate max-w-[180px]">
                                        <div class="text-base font-black text-slate-900 uppercase tracking-tight truncate leading-none group-hover:text-emerald-700 transition-colors">{{ log.employee?.first_name }} {{ log.employee?.last_name }}</div>
                                        <div class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] truncate mt-2.5 leading-none">{{ log.employee?.department?.name || 'CORE_UNIT' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex flex-col gap-1.5">
                                    <span class="text-sm font-black text-slate-600 uppercase tracking-widest leading-none">{{ log.shift?.name || 'STD_SYNC_PROTOCOL' }}</span>
                                    <span v-if="log.sessions?.some(s => s.is_regularized)" class="text-xs font-black text-emerald-600 uppercase italic tracking-widest bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-100 self-start">Override Detected</span>
                                </div>
                            </td>
                            <td class="px-6 py-6 font-black text-base text-slate-600 uppercase tabular-nums tracking-tighter">
                                <div v-if="log.sessions && log.sessions.length > 0" class="flex flex-col gap-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></div>
                                        <span>IN: {{ formatTime(log.sessions[0].in_time?.split(' ')?.[1]) }}</span>
                                    </div>
                                    <div v-if="log.sessions[log.sessions.length-1].out_time" class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.5)]"></div>
                                        <span>OUT: {{ formatTime(log.sessions[log.sessions.length-1].out_time?.split(' ')?.[1]) }}</span>
                                    </div>
                                </div>
                                <span v-else class="text-sm font-black text-slate-300 uppercase tracking-widest italic opacity-40 leading-none">Signal Offline</span>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex flex-col gap-2">
                                    <div class="text-base font-black text-slate-900 tracking-tighter leading-none tabular-nums">{{ (log.total_work_minutes / 60).toFixed(2) }} HRS</div>
                                    <div class="flex gap-2">
                                        <span v-if="log.overtime_minutes > 0" class="text-xs font-black text-emerald-600 uppercase tracking-tighter bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-100">+{{ log.overtime_minutes }}M OT</span>
                                        <span v-if="log.late_minutes > 0" class="text-xs font-black text-rose-600 uppercase tracking-tighter bg-rose-50 px-1.5 py-0.5 rounded border border-rose-100">{{ log.late_minutes }}M LAT</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6 text-right">
                                <span class="px-4 py-1.5 text-xs font-black rounded-full border uppercase tracking-widest shadow-sm transition-all" 
                                    :class="getStatusColor(log.status)">
                                    {{ log.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="lg:hidden divide-y divide-gray-50 bg-slate-50/50">
                <div v-if="logs && logs.data.length === 0" class="p-20 text-center animate-in fade-in zoom-in duration-700">
                    <svg class="w-12 h-12 mx-auto text-slate-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-[0.3em]">No Temporal Nodes Located</p>
                </div>
                <div v-for="log in logs?.data || []" :key="log.id" class="p-5 space-y-5 group relative overflow-hidden bg-white hover:bg-slate-50 transition-all active:scale-[0.98]">
                    <div class="flex items-start justify-between relative z-10">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="w-12 h-12 rounded-2xl bg-slate-900 border-2 border-white flex items-center justify-center text-white font-black text-xs shrink-0 shadow-lg group-hover:bg-emerald-600 transition-all">
                                {{ log.employee?.first_name?.[0] }}{{ log.employee?.last_name?.[0] }}
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight leading-none truncate">{{ log.employee?.first_name }} {{ log.employee?.last_name }}</h4>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-widest">#OP-{{ String(log.employee_id).padStart(3, '0') }} &bull; {{ log.employee?.department?.name || 'CORE_UNIT' }}</span>
                                </div>
                            </div>
                        </div>
                        <span class="px-3 py-1 text-xs font-black rounded-full border uppercase tracking-widest shadow-sm" 
                            :class="getStatusColor(log.status)">
                            {{ log.status }}
                        </span>
                    </div>

                    <div class="bg-slate-50/50 p-4 rounded-2xl border border-gray-100 group-hover:bg-white transition-all relative z-10 shadow-inner">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="border-r border-slate-200/50">
                                <span class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] block mb-3">Temporal Logs</span>
                                <div class="text-sm font-black text-slate-700 uppercase truncate mb-3 leading-none">{{ log.shift?.name || 'GEN_SHIFT_PROTO' }}</div>
                                <div v-if="log.sessions && log.sessions.length > 0" class="space-y-2">
                                    <div class="flex items-center gap-3 text-sm font-black tabular-nums">
                                        <span class="text-emerald-500 uppercase tracking-widest">IN:</span>
                                        <span class="text-slate-600">{{ formatTime(log.sessions[0].in_time?.split(' ')?.[1]) }}</span>
                                    </div>
                                    <div v-if="log.sessions[log.sessions.length-1].out_time" class="flex items-center gap-3 text-sm font-black tabular-nums">
                                        <span class="text-rose-500 uppercase tracking-widest">OUT:</span>
                                        <span class="text-slate-600">{{ formatTime(log.sessions[log.sessions.length-1].out_time?.split(' ')?.[1]) }}</span>
                                    </div>
                                </div>
                                <span v-else class="text-sm font-black text-slate-300 italic opacity-50">NODE_OFFLINE</span>
                            </div>
                            <div class="pl-2">
                                <span class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] block mb-3">Work Metrics</span>
                                <div class="text-[16px] font-black text-slate-900 tabular-nums leading-none mb-3">{{ (log.total_work_minutes / 60).toFixed(2) }} HR</div>
                                <div class="flex flex-wrap gap-2">
                                    <div v-if="log.overtime_minutes > 0" class="px-2 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-xs font-black border border-emerald-100 shadow-sm">+{{ log.overtime_minutes }}m</div>
                                    <div v-if="log.late_minutes > 0" class="px-2 py-1 bg-rose-50 text-rose-600 rounded-lg text-xs font-black border border-rose-100 shadow-sm">-{{ log.late_minutes }}m</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="absolute bottom-0 right-0 p-4 opacity-5 translate-y-2 group-hover:translate-y-0 group-hover:opacity-10 transition-all">
                        <svg class="w-12 h-12 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Neural Pagination -->
        <div class="px-8 py-6 bg-slate-50/80 rounded-[2rem] flex flex-col md:flex-row justify-between items-center gap-6 border border-gray-100 shadow-sm" v-if="logs?.data?.length > 0">
            <div class="text-sm font-black text-slate-400 uppercase tracking-[0.3em]">
                SYNC_PULSE {{ logs.current_page }} / {{ logs.last_page }}
            </div>
            <div class="flex gap-2">
                   <template v-for="(link, k) in logs.links" :key="k">
                        <div v-if="link.url === null" class="h-10 px-4 text-sm font-black uppercase tracking-widest bg-white text-slate-300 border border-gray-100 rounded-xl opacity-50 cursor-not-allowed flex items-center justify-center shadow-inner" v-html="link.label"></div>
                        <Link v-else 
                            :href="link.url" 
                            class="h-10 px-4 text-sm font-black uppercase tracking-widest rounded-xl transition-all shadow-sm flex items-center justify-center active:scale-95" 
                            :class="{'bg-slate-900 text-white shadow-xl': link.active, 'bg-white text-slate-400 border border-gray-200 hover:text-emerald-600 hover:border-emerald-100': !link.active}"
                            v-html="link.label"
                        />
                   </template>
            </div>
        </div>
    </div>
  </component>
</template>

<script setup>
import { ref, watch } from 'vue';
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import EmployeeFilterBar from '@/Components/EmployeeFilterBar.vue';
import { useToastStore } from '@/stores/toast';

const toast = useToastStore();
const props = defineProps({
    embedded: Boolean,
    logs: Object,
    departments: Array,
    locations: Array,
    filters: Object
});

const loading = ref(false);

const filterForm = ref({
    date_from: props.filters?.date_from || new Date().toISOString().slice(0, 10),
    date_to: props.filters?.date_to || new Date().toISOString().slice(0, 10),
    search: props.filters?.search || '',
    department_id: props.filters?.department_id || '',
    location_id: props.filters?.location_id || '',
    status: props.filters?.status || ''
});

const handleFilterUpdate = (newFilters) => {
    reload({
        ...filterForm.value,
        ...newFilters
    });
};

watch(() => [filterForm.value.date_from, filterForm.value.date_to, filterForm.value.status], () => {
     reload(filterForm.value);
});

const reload = (query) => {
    loading.value = true;
    router.visit(route('admin.attendance.monitoring'), {
        method: 'get',
        data: query,
        preserveState: true,
        preserveScroll: true,
        only: ['logs', 'filters'],
        onFinish: () => { loading.value = false; }
    });
};

const exportData = () => {
    const params = new URLSearchParams(filterForm.value).toString();
    window.location.href = `/admin/attendance/export?${params}`;
    toast.success("EXPORT_INITIALIZED");
};

const formatTime = (timeStr) => {
    if (!timeStr) return '-';
    const [h, m] = timeStr.split(':');
    return `${h}:${m}`;
};

const getStatusColor = (status) => {
    switch (status) {
        case 'Present': return 'bg-emerald-50 text-emerald-600 border-emerald-100';
        case 'Absent': return 'bg-rose-50 text-rose-600 border-rose-100';
        case 'Late': return 'bg-amber-50 text-amber-600 border-amber-100';
        case 'Half Day': return 'bg-orange-50 text-orange-600 border-orange-100';
        default: return 'bg-slate-50 text-slate-400 border-gray-100';
    }
};
</script>

<style scoped>
input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(0.4) sepia(1) saturate(5) hue-rotate(200deg);
    cursor: pointer;
}
</style>
