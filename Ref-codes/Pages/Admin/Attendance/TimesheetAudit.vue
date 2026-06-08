<template>
    <component :is="embedded ? 'div' : AttendanceLayout" title="Audit Terminal" activeTab="approvals" v-bind="$props">
        <Head v-if="!embedded" title="Timesheet Audit" />
        
        <div :class="{'max-w-[1600px] mx-auto': !embedded}" class="space-y-6 pb-12">
            <!-- Compact Command Bar -->
            <div class="sticky top-0 z-40 bg-white/80 backdrop-blur-md p-3 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <div class="flex items-center gap-3 px-2">
                    <div class="w-9 h-9 bg-slate-900 rounded-lg flex items-center justify-center text-white shadow-sm">
                        <i class="fas fa-fingerprint text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-800 uppercase tracking-tight leading-none">Audit Trace</h2>
                        <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mt-1.5">Temporal Modification Logs</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 w-full md:w-auto justify-end">
                    <div class="flex items-center gap-2 bg-slate-50 rounded-lg p-1 border border-slate-200 shadow-inner">
                        <div class="relative">
                            <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input v-model="filters.user" type="text" placeholder="SEARCH_UAID..." class="bg-transparent border-transparent rounded-lg text-sm font-bold text-slate-600 focus:ring-0 pl-7 pr-3 py-1 uppercase tracking-widest placeholder:text-slate-300">
                        </div>
                        <div class="h-4 w-px bg-slate-200 mx-1"></div>
                        <div class="relative">
                            <i class="fas fa-calendar-day absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input v-model="filters.date" type="date" class="bg-transparent border-transparent rounded-lg text-sm font-bold text-slate-600 focus:ring-0 pl-7 pr-3 py-1 uppercase tracking-widest">
                        </div>
                    </div>
                    
                    <button @click="refresh" class="w-8 h-8 bg-white text-slate-400 rounded-lg flex items-center justify-center border border-slate-200 shadow-sm hover:text-indigo-600 transition-all active:scale-95" title="Synchronize Matrix">
                        <i class="fas fa-sync-alt text-sm" :class="{'animate-spin text-indigo-600': loading}"></i>
                    </button>
                </div>
            </div>

            <!-- Registry Terminal -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden min-h-[400px]">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-5 py-3 text-left text-sm font-bold text-slate-400 uppercase tracking-widest">Temporal Anchor</th>
                                <th class="px-5 py-3 text-left text-sm font-bold text-slate-400 uppercase tracking-widest">Authorized UAID</th>
                                <th class="px-5 py-3 text-left text-sm font-bold text-slate-400 uppercase tracking-widest">Mutation Event</th>
                                <th class="px-5 py-3 text-left text-sm font-bold text-slate-400 uppercase tracking-widest">Strategic Detail</th>
                                <th class="px-5 py-3 text-right text-sm font-bold text-slate-400 uppercase tracking-widest">IP Link</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-if="logs.length === 0" class="text-center">
                                <td colspan="5" class="p-20 text-center">
                                    <div class="flex flex-col items-center gap-3 opacity-30 grayscale shrink-0">
                                        <i class="fas fa-satellite-dish text-2xl"></i>
                                        <span class="text-sm font-bold text-slate-400 uppercase tracking-widest">No Mutation Records Found</span>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="log in logs" :key="log.id" class="group hover:bg-slate-50/50 transition-all duration-150">
                                <td class="px-5 py-3">
                                    <span class="text-sm font-bold text-slate-500 uppercase tracking-tight whitespace-nowrap">{{ new Date(log.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-7 h-7 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-sm border border-indigo-100 shadow-sm flex-shrink-0 uppercase">
                                            {{ log.causer?.name?.[0] || 'S' }}
                                        </div>
                                        <div class="truncate max-w-[150px]">
                                            <div class="text-base font-bold text-slate-800 uppercase tracking-tight truncate">{{ log.causer?.name || 'SYSTEM_CORE' }}</div>
                                            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest truncate">Authorized Agent</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 text-xs font-bold rounded uppercase tracking-widest text-white" 
                                        :class="{
                                            'bg-emerald-500': log.event === 'created',
                                            'bg-indigo-500': log.event === 'updated',
                                            'bg-rose-500': log.event === 'deleted'
                                        }">
                                        {{ log.event }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex flex-col gap-1">
                                        <div class="text-sm font-bold text-slate-700 uppercase tracking-tight">
                                            {{ log.subject_type?.split('\\').pop() }} 
                                            <span class="text-slate-400 ml-1">#{{ log.subject_id }}</span>
                                        </div>
                                        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-none max-w-[250px] truncate" :title="log.description">
                                            {{ log.description || 'PROTOCOL_MUTATION_EXECUTED' }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <span class="text-xs font-black text-slate-300 uppercase tracking-widest font-mono">{{ log.properties?.ip || '0.0.0.0' }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </component>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { Head, router } from '@inertiajs/vue3';
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { debounce } from 'lodash';

defineOptions({ layout: MainLayout });

const props = defineProps({
    embedded: Boolean,
});

const loading = ref(false);
const logs = ref([]);
const filters = ref({
    user: '',
    date: ''
});

const fetchLogs = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/admin/attendance/timesheets/audit-logs', { params: filters.value });
        logs.value = res.data;
    } catch(e) {
        console.error("Audit fetch failed", e);
    } finally {
        loading.value = false;
    }
};

const refresh = fetchLogs;

watch(filters, debounce(fetchLogs, 500), { deep: true });

onMounted(() => {
    fetchLogs();
});
</script>

