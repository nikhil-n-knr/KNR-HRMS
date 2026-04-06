<script setup>
import { ref } from 'vue';
import { 
    ClockIcon, 
    ArrowPathIcon, 
    BoltIcon,
    CpuChipIcon 
} from '@heroicons/vue/24/outline';

const loading = ref(false);
</script>

<template>
    <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-slate-100 p-12 lg:p-20 text-center shadow-2xl shadow-slate-200/40 relative overflow-hidden group font-outfit">
        <!-- Background Decorative Elements -->
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-slate-50 rounded-full blur-3xl opacity-50 group-hover:bg-indigo-50 transition-colors duration-1000"></div>
        <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-slate-50 rounded-full blur-3xl opacity-50 group-hover:bg-emerald-50 transition-colors duration-1000"></div>

        <div class="relative z-10 max-w-2xl mx-auto">
            <!-- Icon Cluster -->
            <div class="relative w-24 h-24 mx-auto mb-10">
                <div class="absolute inset-0 bg-slate-100 rounded-[2rem] border border-slate-200 shadow-inner group-hover:rotate-12 transition-transform duration-700"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <ClockIcon class="w-10 h-10 text-slate-300 group-hover:text-indigo-500 transition-colors" />
                </div>
                <!-- Mini Floating Icons -->
                <div class="absolute -top-4 -right-4 w-10 h-10 bg-white rounded-xl shadow-lg border border-slate-100 flex items-center justify-center text-emerald-500 animate-bounce">
                    <BoltIcon class="w-5 h-5" />
                </div>
            </div>

            <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight mb-4">Daily Attendance Registry</h3>
            <p class="text-sm font-black text-slate-400 uppercase tracking-[0.4em] mb-12 flex items-center justify-center gap-3">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                Module Finalization In Progress
            </p>
            
            <div class="bg-slate-950 rounded-[2rem] p-8 text-left border border-slate-800 shadow-2xl shadow-indigo-500/10 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <CpuChipIcon class="w-20 h-20 text-white" />
                </div>
                
                <div class="space-y-6 relative z-10">
                    <div class="flex items-center gap-4">
                        <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center border border-white/10">
                            <span class="text-sm font-mono text-indigo-400">01</span>
                        </div>
                        <p class="text-base font-black text-slate-400 uppercase tracking-widest leading-none">Initializing Timesheet Matrix...</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center border border-white/10">
                            <span class="text-sm font-mono text-indigo-400">02</span>
                        </div>
                        <p class="text-base font-black text-slate-400 uppercase tracking-widest leading-none">Syndicating Bulk-Edit Protocol...</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center border border-white/10 opacity-30">
                            <span class="text-sm font-mono text-indigo-400">03</span>
                        </div>
                        <p class="text-base font-black text-slate-600 uppercase tracking-widest leading-none italic">Awaiting Final Core Injection</p>
                    </div>

                    <!-- Fake Progress Bar -->
                    <div class="pt-6">
                        <div class="h-1 bg-white/5 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-500 w-2/3 rounded-full animate-[pulse_2s_infinite]"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-12 flex flex-col items-center gap-6">
                <p class="text-base font-black text-slate-400 uppercase tracking-widest max-w-md italic leading-relaxed">
                    " The Timesheet Grid interface is currently being compiled. Enhanced tactical punch correction capabilities will be deployed shortly. "
                </p>
                <button @click="$emit('refresh')" class="px-8 py-3 bg-slate-100 text-slate-400 rounded-xl text-sm font-black uppercase tracking-[0.3em] hover:bg-slate-900 hover:text-white transition-all active:scale-95 flex items-center gap-3">
                    <ArrowPathIcon class="w-4 h-4" />
                    Check Synchronization Status
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Any custom typography */
</style>
