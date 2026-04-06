<script setup>
import { 
    ClockIcon, 
    BriefcaseIcon, 
    CalendarDaysIcon,
    ArrowTrendingUpIcon,
    HeartIcon,
    DocumentChartBarIcon,
    ShieldCheckIcon,
    FingerPrintIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    employee: {
        type: Object,
        required: true
    }
});

const formatDate = (d) => d ? new Date(d).toLocaleDateString(undefined, { month: 'long', day: 'numeric', year: 'numeric' }) : 'NOT_INDEXED';
const getTenure = (date) => {
    if(!date) return '0_DAYS';
    const start = new Date(date);
    const end = new Date();
    const diff = end.getTime() - start.getTime();
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    if(days > 365) return Math.floor(days / 365) + 'Y ' + (days % 365) + 'D';
    return days + ' DAYS';
};
</script>

<template>
    <div class="space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-700 font-outfit">
        <!-- Dashboard Summary Terminal -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Strategic Narrative -->
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white rounded-[2.5rem] border border-slate-100 p-8 md:p-10 shadow-2xl shadow-slate-200/40 relative overflow-hidden group">
                    <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-indigo-50 rounded-full opacity-30 group-hover:scale-125 transition-transform duration-1000"></div>
                    
                    <div class="relative z-10">
                        <h3 class="text-xs font-black text-slate-900 uppercase tracking-[0.2em] mb-8 flex items-center gap-3">
                            Executive Summary
                            <DocumentChartBarIcon class="w-5 h-5 text-indigo-500" />
                        </h3>
                        
                        <div class="space-y-6">
                            <p class="text-[14px] md:text-[16px] font-black text-slate-700 uppercase tracking-tight leading-relaxed italic opacity-85">
                                " Operative <span class="text-indigo-600 underline decoration-2 decoration-indigo-100 underline-offset-4">{{ employee.first_name }} {{ employee.last_name }}</span> initiated deployment on <span class="text-emerald-600">{{ formatDate(employee.joining_date) }}</span>. 
                                Holding active designation as <span class="text-slate-900 bg-slate-100 px-3 py-1 rounded-lg">{{ employee.designation }}</span> within the 
                                <span class="text-slate-900 bg-slate-100 px-3 py-1 rounded-lg">{{ employee.department?.name || 'CENTRAL_COMMAND' }}</span> sector, operating from the {{ employee.location?.name || 'GLOBAL_ZONE' }} hub. "
                            </p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6">
                                <div class="bg-slate-50 p-6 rounded-[2rem] border border-slate-100 flex items-center justify-between group/stat">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 group-hover/stat:text-indigo-600 group-hover/stat:border-indigo-100 transition-all">
                                            <ClockIcon class="w-6 h-6" />
                                        </div>
                                        <div>
                                            <span class="text-xs font-black text-slate-400 uppercase tracking-widest block mb-1">Total Tenure</span>
                                            <span class="text-base font-black text-slate-900 uppercase tracking-tight">{{ getTenure(employee.joining_date) }}</span>
                                        </div>
                                    </div>
                                    <ArrowTrendingUpIcon class="w-5 h-5 text-emerald-500 opacity-30 group-hover/stat:opacity-100 transition-opacity" />
                                </div>
                                <div class="bg-slate-50 p-6 rounded-[2rem] border border-slate-100 flex items-center justify-between group/stat">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 group-hover/stat:text-blue-600 group-hover/stat:border-blue-100 transition-all">
                                            <FingerPrintIcon class="w-6 h-6" />
                                        </div>
                                        <div>
                                            <span class="text-xs font-black text-slate-400 uppercase tracking-widest block mb-1">Operative Type</span>
                                            <span class="text-base font-black text-slate-900 uppercase tracking-tight text-blue-600 italic">{{ employee.employment_type?.replace('_', ' ') || 'STANDARD_CORE' }}</span>
                                        </div>
                                    </div>
                                    <ShieldCheckIcon class="w-5 h-5 text-blue-400 opacity-30 group-hover/stat:opacity-100 transition-opacity" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Strategic Intelligence Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div v-for="(val, key) in { 'REPORTS_TO': employee.manager?.name || 'HEAD_OF_OPERATIONS', 'PROBATION_END': employee.probation_end_date || 'COMPLETED', 'HIRE_SOURCE': 'DIRECT_INDEX' }" :key="key" class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/40 group hover:border-indigo-100 transition-all">
                        <span class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] block mb-3">{{ key }}</span>
                        <div class="text-sm font-black text-slate-800 uppercase tracking-tight group-hover:text-indigo-600 transition-colors">{{ val }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Telemetry Sidebars -->
            <div class="space-y-8">
                <!-- Health Matrix Pulse -->
                <div class="bg-slate-900 rounded-[2.5rem] p-8 text-white relative overflow-hidden shadow-2xl shadow-indigo-500/20 group">
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-rose-500/10 rounded-full blur-3xl group-hover:bg-rose-500/20 transition-all duration-1000"></div>
                    
                    <h3 class="text-xs font-black uppercase tracking-[0.3em] text-rose-400 mb-8 flex items-center gap-3">
                        Vitality Scan
                        <HeartIcon class="w-4 h-4 animate-pulse" />
                    </h3>
                    <div class="flex items-center gap-6">
                        <div class="w-16 h-16 rounded-[1.25rem] bg-indigo-500/10 border border-indigo-500/30 flex items-center justify-center text-indigo-400 shadow-xl group-hover:rotate-12 transition-transform">
                            <CalendarDaysIcon class="w-8 h-8" />
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Next Health Audit</p>
                            <p class="text-xl font-black italic tracking-tighter text-white">NOT_SCHEDULED</p>
                            <p class="text-xs font-black text-rose-500 uppercase tracking-[0.2em] mt-1 italic">Vitals: Normal_Sector_Standard</p>
                        </div>
                    </div>
                </div>

                <!-- Identity Verification Node -->
                <div class="bg-white rounded-[2.5rem] border border-slate-100 p-8 shadow-2xl shadow-slate-200/40">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Security Clearance</h3>
                    <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl border border-dashed border-slate-200 opacity-60 hover:opacity-100 transition-opacity group cursor-help">
                        <ShieldCheckIcon class="w-8 h-8 text-slate-300 group-hover:text-emerald-500 transition-colors" />
                        <div>
                            <p class="text-xs font-black text-slate-500 uppercase tracking-widest leading-none mb-1">Auth Index</p>
                            <p class="text-xs font-black text-slate-900 uppercase tracking-tight">LEVEL_04_TRUSTED</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
