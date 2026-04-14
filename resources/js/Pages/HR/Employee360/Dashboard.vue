<template>
    <div class="h-full flex flex-col p-6 space-y-6 overflow-y-auto custom-scrollbar bg-slate-50">
        
        <!-- Header & Config -->
        <div class="flex flex-col md:flex-row justify-between items-end gap-6 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
            <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
                 <div>
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-widest">Select Employee</label>
                    <select v-model="selectedEmployeeId" class="block w-64 mt-2 text-sm border-gray-200 rounded-xl focus:ring-indigo-500 bg-gray-50 p-2.5">
                        <option value="" disabled>Choose an employee...</option>
                        <option v-for="e in employees" :key="e.id" :value="e.id">{{ e.name }} ({{ e.designation }})</option>
                    </select>
                 </div>
                 <div>
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-widest">Date Range</label>
                    <div class="flex gap-2 mt-2">
                        <input v-model="filters.start_date" type="date" class="text-sm border-gray-200 rounded-xl focus:ring-indigo-500 bg-gray-50 p-2.5">
                        <input v-model="filters.end_date" type="date" class="text-sm border-gray-200 rounded-xl focus:ring-indigo-500 bg-gray-50 p-2.5">
                    </div>
                 </div>
            </div>

            <div class="flex gap-3">
                <button 
                    @click="fetchData"
                    :disabled="!selectedEmployeeId || loading"
                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold rounded-xl shadow-sm transition-all flex items-center gap-2"
                >
                    <svg v-if="loading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    Load 360 Profile
                </button>
                <button 
                    @click="exportDossier"
                    :disabled="!data"
                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold rounded-xl shadow-sm transition-all flex items-center gap-2"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    Export Full Excel Dossier
                </button>
            </div>
        </div>

        <div v-if="!data && !loading" class="flex-1 flex flex-col items-center justify-center text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
            <p class="text-lg font-medium">Select an Employee to view their 360° Profile</p>
        </div>

        <!-- 360 Dashboard Content -->
        <div v-if="data" class="space-y-6">
            
            <!-- Hero Profile Card -->
            <div class="bg-gradient-to-r from-indigo-900 to-indigo-800 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
                <div class="absolute right-0 top-0 opacity-10 scale-150 transform translate-x-10 -translate-y-10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-64 w-64" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                </div>
                <div class="flex items-center gap-6 relative z-10">
                    <div class="h-24 w-24 rounded-2xl bg-white/20 backdrop-blur-md overflow-hidden ring-4 ring-white/10 flex items-center justify-center p-2 shadow-2xl">
                        <img v-if="data.profile.avatar" :src="data.profile.avatar" class="h-full w-full object-cover rounded-xl" />
                        <span v-else class="text-3xl font-black text-indigo-100 flex items-center h-full w-full bg-indigo-500/50 rounded-xl justify-center">
                            {{ data.profile.name.charAt(0) }}
                        </span>
                    </div>
                    <div>
                        <h2 class="text-3xl font-black tracking-tight flex items-center gap-3">
                            {{ data.profile.name }}
                            <span v-if="data.metrics.overview.burnout_risk === 'High'" class="bg-red-500/20 text-red-200 text-xs px-3 py-1 rounded-full border border-red-500/30 uppercase tracking-widest font-bold flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.281.436-.506.914-.769 1.36-1.09 1.85-2.264 3.702-4.04 4.885a1.001 1.001 0 00-.77 1.258A6.002 6.002 0 0019 12.5a1 1 0 00-1.996-.289 4.002 4.002 0 01-6.852-2.617 1 1 0 00-1.896-.289 8.002 8.002 0 001.996 9.4 1 1 0 001.449-.385c.345-.23.614-.558.822-.88.281-.436.506-.914.769-1.36 1.09-1.85 2.264-3.702 4.04-4.885z" clip-rule="evenodd" /></svg>
                                High Burnout Risk
                            </span>
                             <span v-else class="bg-emerald-500/20 text-emerald-200 text-xs px-3 py-1 rounded-full border border-emerald-500/30 uppercase tracking-widest font-bold flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 inline-block" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                Optimal Path
                            </span>
                        </h2>
                        <p class="text-indigo-200 font-medium text-lg mt-1">{{ data.profile.designation }} &bull; {{ data.profile.department }}</p>
                    </div>
                </div>
            </div>

            <!-- 4 Column KPI Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                 <!-- Velocity -->
                 <div class="bg-white/80 backdrop-blur-xl p-5 rounded-2xl border border-gray-200 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-transform">
                     <p class="text-xs font-bold text-gray-500 uppercase">Scrum Velocity</p>
                     <h3 class="text-3xl font-black text-indigo-600 mt-2">{{ data.metrics.overview.scrum_velocity }} <span class="text-sm font-medium text-gray-400">Pts</span></h3>
                     <p class="text-xs text-gray-400 mt-2 font-medium bg-gray-50 p-2 rounded-lg inline-block w-full text-center">{{ data.metrics.overview.hours_burned }} hrs burned</p>
                 </div>

                 <!-- Reliability -->
                 <div class="bg-white/80 backdrop-blur-xl p-5 rounded-2xl border border-gray-200 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-transform">
                     <p class="text-xs font-bold text-gray-500 uppercase">Attendance Reliability</p>
                     <h3 class="text-3xl font-black mt-2" :class="data.metrics.overview.reliability_score > 90 ? 'text-emerald-600' : 'text-amber-600'">
                         {{ data.metrics.overview.reliability_score }}%
                     </h3>
                     <p class="text-[10px] text-gray-400 mt-3 font-bold uppercase">{{ data.metrics.timesheet.total_hours }} hrs recorded logged.</p>
                     <p class="text-[10px] text-gray-400 mt-1 font-bold uppercase text-red-500" v-if="data.metrics.timesheet.geo_breaches > 0">{{ data.metrics.timesheet.geo_breaches }} Geo-Location Breaches</p>
                 </div>
                 
                 <!-- Project Success -->
                 <div class="bg-white/80 backdrop-blur-xl p-5 rounded-2xl border border-gray-200 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-transform">
                     <p class="text-xs font-bold text-gray-500 uppercase">Delivery Output</p>
                     <h3 class="text-3xl font-black text-blue-600 mt-2">{{ data.metrics.projects.completed }} <span class="text-sm font-medium text-gray-400">Tasks</span></h3>
                     <p class="text-xs font-medium mt-2 bg-rose-50 text-rose-600 p-2 rounded-lg text-center">{{ data.metrics.projects.overdue }} Overdue Tasks</p>
                 </div>

                  <!-- Quality (Expanded) -->
                  <div class="bg-gradient-to-br from-amber-50 to-orange-50 p-5 rounded-2xl border border-amber-200 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-transform">
                      <p class="text-xs font-bold text-amber-700 uppercase">Quality Deep-Dive</p>
                      <h3 class="text-3xl font-black text-amber-600 mt-2">{{ data.metrics.quality.resolved }} <span class="text-xs font-medium text-amber-500">Fixed</span></h3>
                      <div class="flex justify-between items-center mt-3 text-[10px] font-bold uppercase tracking-tight">
                         <span class="text-red-600">{{ data.metrics.quality.reopened }} Reopened</span>
                         <span class="text-amber-800">{{ data.metrics.quality.sla_breaches }} Breaches</span>
                      </div>
                  </div>
             </div>

             <!-- Performance Deviation & Request Hub -->
             <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                 
                 <!-- Efficiency Matrix -->
                 <div class="lg:col-span-1 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                     <div class="flex items-center justify-between mb-6">
                         <h4 class="font-black text-sm uppercase tracking-widest text-gray-400">Efficiency Matrix</h4>
                         <span class="px-3 py-1 bg-indigo-50 text-indigo-600 text-xs font-black rounded-full">{{ data.metrics.deviation.efficiency }}% Eff</span>
                     </div>
                     <div class="space-y-6">
                         <div>
                             <div class="flex justify-between text-xs font-bold mb-2">
                                 <span class="text-gray-500">Actual Hours</span>
                                 <span class="text-indigo-600">{{ data.metrics.deviation.actual }}h</span>
                             </div>
                             <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                                 <div class="bg-indigo-500 h-full rounded-full transition-all duration-1000" :style="{ width: Math.min(100, (data.metrics.deviation.actual / Math.max(1, data.metrics.deviation.estimated)) * 100) + '%' }"></div>
                             </div>
                             <p class="text-[10px] text-gray-400 mt-1">Allocated Goal: {{ data.metrics.deviation.estimated }}h</p>
                         </div>

                         <div class="pt-4 border-t border-gray-50">
                             <div class="flex justify-between items-center mb-1">
                                 <span class="text-[10px] font-bold text-red-500 uppercase">Unassigned Work</span>
                                 <span class="text-sm font-black">{{ data.metrics.deviation.unassigned }}h</span>
                             </div>
                             <p class="text-[10px] text-gray-400 italic">Shadow effort detected without task linkage</p>
                         </div>
                     </div>
                 </div>

                 <!-- Request & History Hub -->
                 <div class="lg:col-span-2 bg-slate-900 rounded-2xl p-6 text-white shadow-xl relative overflow-hidden group">
                     <div class="absolute right-0 bottom-0 opacity-5 group-hover:opacity-10 transition-opacity">
                         <i class="fas fa-file-signature text-9xl"></i>
                     </div>
                     <div class="flex items-center justify-between mb-8">
                         <h4 class="font-black text-xs uppercase tracking-[0.3em] text-indigo-400">Request & History Hub</h4>
                         <div class="flex items-center gap-2">
                             <div class="flex -space-x-2">
                                 <div v-for="i in 3" :key="i" class="w-6 h-6 rounded-full border-2 border-slate-900 bg-indigo-500/20"></div>
                             </div>
                             <span class="text-[10px] font-bold text-indigo-300">{{ data.metrics.requests.pending_approvals }} Pending Approvals</span>
                         </div>
                     </div>

                     <div class="grid grid-cols-3 gap-6 mb-8">
                         <div class="text-center">
                             <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Leaves</p>
                             <p class="text-2xl font-black text-white">{{ data.metrics.requests.leaves }}</p>
                         </div>
                         <div class="text-center border-x border-slate-800">
                             <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">WFH</p>
                             <p class="text-2xl font-black text-white">{{ data.metrics.requests.wfh }}</p>
                         </div>
                         <div class="text-center">
                             <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Swaps</p>
                             <p class="text-2xl font-black text-white">{{ data.metrics.requests.swaps }}</p>
                         </div>
                     </div>

                     <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                         <h5 class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-3">Recent Leaves</h5>
                         <div v-if="data.metrics.requests.recent_requests.length === 0" class="text-xs text-slate-500 py-2">No recent results found.</div>
                         <div v-else class="space-y-3">
                             <div v-for="req in data.metrics.requests.recent_requests" :key="req.id" class="flex justify-between items-center text-xs">
                                 <span class="font-medium">{{ req.start_date }} → {{ req.end_date }}</span>
                                 <span class="px-2 py-0.5 rounded-md text-[9px] font-black" 
                                    :class="req.status === 'approved' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400'">
                                    {{ req.status.toUpperCase() }}
                                 </span>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>

             <!-- Attendance Detailed Log -->
             <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                 <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                     <h4 class="font-black text-sm uppercase tracking-widest text-gray-500">Operational Precision (Last 7 Days)</h4>
                     <i class="fas fa-microchip text-indigo-100 text-xl"></i>
                 </div>
                 <div class="overflow-x-auto">
                     <table class="w-full text-left text-sm">
                         <thead>
                             <tr class="bg-gray-50 text-[10px] font-black uppercase text-gray-400 tracking-wider">
                                 <th class="px-6 py-4">Date</th>
                                 <th class="px-6 py-4">Protocol</th>
                                 <th class="px-6 py-4">Check In</th>
                                 <th class="px-6 py-4">Check Out</th>
                                 <th class="px-6 py-4">Intensity</th>
                                 <th class="px-6 py-4 text-right">Burn Rate</th>
                             </tr>
                         </thead>
                         <tbody class="divide-y divide-gray-50">
                             <tr v-for="log in data.metrics.attendance_grid" :key="log.date" class="hover:bg-gray-50 transition-colors">
                                 <td class="px-6 py-4 font-bold text-gray-600">{{ log.date }}</td>
                                 <td class="px-6 py-4">
                                     <span class="px-2 py-1 rounded-lg text-[10px] font-bold border" 
                                        :class="log.status === 'present' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100'">
                                        {{ log.status.toUpperCase() }}
                                     </span>
                                 </td>
                                 <td class="px-6 py-4 text-gray-500 font-medium">{{ log.check_in }}</td>
                                 <td class="px-6 py-4 text-gray-500 font-medium">{{ log.check_out }}</td>
                                 <td class="px-6 py-4">
                                     <div class="flex items-center gap-2">
                                         <div class="w-24 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                             <div class="bg-indigo-400 h-full rounded-full" :style="{ width: Math.min(100, (log.duration / 8) * 100) + '%' }"></div>
                                         </div>
                                         <span class="text-[10px] font-bold text-gray-400">{{ log.duration }}h</span>
                                     </div>
                                 </td>
                                 <td class="px-6 py-4 text-right font-black text-gray-400">
                                     {{ log.duration > 9 ? 'OVERTIME' : (log.duration > 0 ? 'NORMAL' : '-') }}
                                 </td>
                             </tr>
                         </tbody>
                     </table>
                 </div>
             </div>

            <!-- Deep Dive Prompts -->
            <div class="bg-blue-50 border border-blue-100 rounded-2xl p-6 flex flex-col md:flex-row items-center justify-between gap-4">
                 <div>
                     <h4 class="font-bold text-blue-900 text-lg">Download Full Analytical Dossier</h4>
                     <p class="text-sm text-blue-800/70 mt-1">Exporting the full dossier generates an extensive multi-page Excel file covering every internal SLA, CRM impact, LMS learning path, and detailed baseline task allocations.</p>
                 </div>
                 <button 
                    @click="exportDossier"
                    class="px-6 py-3 shrink-0 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-600/20 transition-all flex items-center gap-2"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    Download Excel Workbook
                </button>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    employees: Array,
    default_date_start: String,
    default_date_end: String
});

const selectedEmployeeId = ref('');
const filters = ref({
    start_date: props.default_date_start,
    end_date: props.default_date_end,
});

const data = ref(null);
const loading = ref(false);

const fetchData = async () => {
    if (!selectedEmployeeId.value) return;
    loading.value = true;
    try {
        const res = await axios.get(route('hr.employee-360.metrics', selectedEmployeeId.value), { params: filters.value });
        data.value = res.data;
    } catch (e) {
        console.error(e);
        // Add toast system notification logic here if needed
        alert("Failed to load employee metrics. Ensure you have the proper access.");
    } finally {
        loading.value = false;
    }
};

const exportDossier = () => {
    if (!selectedEmployeeId.value) return;
    const params = new URLSearchParams(filters.value).toString();
    window.location.href = route('hr.employee-360.export', selectedEmployeeId.value) + '?' + params;
};
</script>
