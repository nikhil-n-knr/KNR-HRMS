<template>
    <div class="max-w-4xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-6 duration-700">
        <!-- Header Card -->
        <div class="bg-white rounded-[3rem] p-10 border border-slate-100 shadow-sm relative overflow-hidden group">
            <div class="flex flex-col md:flex-row items-center gap-8 relative z-10">
                <div class="h-32 w-32 rounded-[2.5rem] bg-slate-900 flex items-center justify-center text-4xl font-black text-white shadow-2xl shadow-slate-900/20 group-hover:scale-105 transition-transform duration-500 border-4 border-white">
                    {{ client?.name?.[0] || 'C' }}
                </div>
                <div class="text-center md:text-left space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-[10px] font-black uppercase tracking-widest mb-2 border border-emerald-100">
                        <ShieldCheckIcon class="h-3 w-3" />
                        Verified Enterprise Partner
                    </div>
                    <h2 class="text-4xl font-black text-slate-900 tracking-tighter">{{ client?.name }}</h2>
                    <p class="text-slate-400 font-medium text-sm">Strategic Stakeholder Terminal • ID: CLN-{{ client?.id?.toString().padStart(4, '0') }}</p>
                </div>
            </div>
            <!-- Decorative BG -->
            <div class="absolute -right-20 -bottom-20 h-64 w-64 bg-slate-50 rounded-full blur-[100px] group-hover:bg-indigo-50/50 transition-all duration-1000"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Security & Auth -->
            <div class="md:col-span-2 space-y-6">
                <div class="bg-white rounded-[2.5rem] border border-slate-100 p-8 space-y-8">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 italic">Security & Credentials</h3>
                        <div class="h-2 w-2 rounded-full bg-emerald-500 shadow-[0_0_10px_#10b981]"></div>
                    </div>

                    <div class="space-y-6">
                        <div class="flex items-center justify-between p-6 bg-slate-50 rounded-2xl border border-slate-100 group hover:border-indigo-200 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 bg-white rounded-xl flex items-center justify-center shadow-sm text-slate-400 group-hover:text-indigo-600 transition-colors">
                                    <KeyIcon class="h-6 w-6" />
                                </div>
                                <div>
                                    <p class="text-xs font-black text-slate-900 uppercase tracking-tight">Access Token Signature</p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Last Rotated: {{ dayjs().subtract(12, 'days').format('MMMM DD, YYYY') }}</p>
                                </div>
                            </div>
                            <button @click="resetPassword" class="px-6 py-2.5 bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-600 transition-all shadow-lg active:scale-95">Rotate</button>
                        </div>

                        <div class="flex items-center justify-between p-6 bg-slate-50 rounded-2xl border border-slate-100 group hover:border-indigo-200 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 bg-white rounded-xl flex items-center justify-center shadow-sm text-slate-400 group-hover:text-indigo-600 transition-colors">
                                    <FingerPrintIcon class="h-6 w-6" />
                                </div>
                                <div>
                                    <p class="text-xs font-black text-slate-900 uppercase tracking-tight">Multi-Factor Integrity</p>
                                    <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest mt-1 italic">Status: Operational</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="text-[10px] font-black text-slate-900 uppercase tracking-widest">Active</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Environmental Presets -->
                <div class="bg-white rounded-[2.5rem] border border-slate-100 p-8 space-y-6">
                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 italic">Telemetry Source Presets</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div v-for="i in 2" :key="i" class="p-6 border-2 border-dashed border-slate-200 rounded-3xl flex flex-col items-center justify-center text-center space-y-4 hover:border-indigo-300 hover:bg-indigo-50/10 transition-all cursor-pointer group">
                             <div class="h-12 w-12 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-300 group-hover:text-indigo-500 transition-colors">
                                <DevicePhoneMobileIcon class="h-6 w-6" />
                             </div>
                             <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Connect Device Preset</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Operational Context -->
            <div class="space-y-6">
                <div class="bg-slate-900 rounded-[2.5rem] p-8 text-white space-y-8 relative overflow-hidden group shadow-2xl shadow-slate-900/40">
                    <div class="relative z-10 space-y-8">
                        <div>
                            <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-6 italic">Project Association</h3>
                            <div class="space-y-3">
                                <div v-for="project in projects" :key="project.id" class="flex items-center gap-4 p-3 bg-white/5 rounded-2xl border border-white/5 group/proj hover:bg-white/10 transition-all">
                                    <div class="h-10 w-10 bg-white/10 rounded-xl flex items-center justify-center text-xs font-black group-hover/proj:bg-emerald-500 group-hover/proj:text-slate-900 transition-all">
                                        {{ project.name[0] }}
                                    </div>
                                    <div class="flex-1 overflow-hidden">
                                        <p class="text-xs font-black truncate">{{ project.name }}</p>
                                        <p class="text-[8px] font-bold text-slate-500 uppercase tracking-widest">PRJ-{{ project.id }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-8 border-t border-white/5 space-y-4">
                            <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-widest">
                                <span class="text-slate-500">Pipeline Load</span>
                                <span class="text-emerald-400">Optimal</span>
                            </div>
                            <div class="h-1.5 bg-white/5 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 w-[65%] rounded-full shadow-[0_0_10px_#10b981]"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Abstract Glow -->
                    <div class="absolute -right-10 -top-10 h-32 w-32 bg-emerald-500/10 rounded-full blur-3xl group-hover:bg-emerald-500/20 transition-all duration-1000"></div>
                </div>

                <div class="bg-indigo-600 rounded-[2.5rem] p-8 text-white group relative overflow-hidden shadow-2xl shadow-indigo-600/20">
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-indigo-200 mb-8 italic relative z-10">Engagement Strategy</h3>
                    <div class="space-y-6 relative z-10">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 bg-white/20 rounded-2xl flex items-center justify-center">
                                <StarIcon class="h-6 w-6 text-white" />
                            </div>
                            <div>
                                <p class="text-xs font-black leading-none uppercase">Priority Support</p>
                                <p class="text-[9px] font-bold text-indigo-100 mt-1 uppercase opacity-60">SLA: 2-Hour Response</p>
                            </div>
                        </div>
                        <button class="w-full py-4 bg-white text-indigo-600 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-900 hover:text-white transition-all active:scale-95 shadow-xl">Contact Sucess Manager</button>
                    </div>
                     <!-- Abstract Glow -->
                     <div class="absolute -left-10 -bottom-10 h-32 w-32 bg-white/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-1000"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { 
    ShieldCheckIcon, 
    KeyIcon, 
    FingerPrintIcon, 
    DevicePhoneMobileIcon,
    StarIcon
} from '@heroicons/vue/24/outline';
import dayjs from 'dayjs';
import { router } from '@inertiajs/vue3';

const props = defineProps(['client', 'projects']);

const resetPassword = () => {
    if (confirm("Initiate credential rotation signature? A temporary token will be generated and broadcasted to your vault.")) {
        router.post(route('clients.users.reset-password', props.client.id));
    }
};
</script>
