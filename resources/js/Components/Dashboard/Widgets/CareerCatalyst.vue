<template>
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl h-full flex flex-col group relative overflow-hidden text-white">
        <!-- Decoration -->
        <div class="absolute -top-12 -right-12 w-48 h-48 bg-emerald-500/5 rounded-full blur-3xl opacity-20"></div>
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10"></div>

        <div class="flex justify-between items-center mb-8 relative z-10">
            <div>
                <h3 class="text-xs font-black uppercase tracking-[0.2em] text-emerald-400 flex items-center gap-2">
                    <RocketLaunchIcon class="w-4 h-4" />
                    Career_Catalyst
                </h3>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-0.5">Professional trajectory audit</p>
            </div>
            <div class="flex items-center gap-2 px-3 py-1 bg-white/5 border border-white/10 rounded-full">
                <span class="text-[9px] font-black uppercase tracking-widest text-emerald-500">Tier_L2</span>
            </div>
        </div>

        <!-- Progress Metrics -->
        <div class="space-y-6 flex-grow relative z-10">
            <div v-for="milestone in milestones" :key="milestone.label" class="space-y-2">
                <div class="flex justify-between items-end">
                    <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">{{ milestone.label }}</span>
                    <span class="text-[10px] font-black text-emerald-400">{{ milestone.progress }}%</span>
                </div>
                <div class="h-1.5 bg-slate-800 rounded-full overflow-hidden border border-white/5">
                    <div class="h-full bg-gradient-to-r from-emerald-600 to-teal-400 transition-all duration-[2000ms] ease-out shadow-[0_0_10px_rgba(16,185,129,0.3)]"
                        :style="{ width: milestone.progress + '%' }"
                    ></div>
                </div>
            </div>

            <!-- Promotion Countdown/Status -->
            <div class="mt-8 p-4 bg-white/5 border border-white/5 rounded-2xl relative overflow-hidden group/card hover:bg-white/10 transition-all">
                <div class="absolute top-0 right-0 w-16 h-16 bg-emerald-500/10 rounded-full -mr-8 -mt-8 blur-2xl"></div>
                <p class="text-[7px] font-black text-slate-500 uppercase tracking-[0.3em] mb-2">Next_Milestone</p>
                <h4 class="text-sm font-black text-white tracking-tighter mb-1">Senior Technical Lead</h4>
                <div class="flex items-center gap-2">
                    <div class="h-1 flex-grow bg-slate-900 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500 w-[72%]"></div>
                    </div>
                    <span class="text-[9px] font-bold text-emerald-400">72%</span>
                </div>
                <p class="text-[8px] font-medium text-slate-500 mt-2 italic">Estimated 4.2 weeks until audit.</p>
            </div>
        </div>

        <!-- Career Insights Footer -->
        <div class="mt-8 grid grid-cols-2 gap-4 border-t border-white/5 pt-6 relative z-10">
            <div class="space-y-1">
                <span class="text-[7px] font-black text-slate-500 uppercase">Growth Score</span>
                <p class="text-xl font-black text-white tracking-tighter">84.2<span class="text-[10px] text-emerald-500 ml-1">+2.1</span></p>
            </div>
            <div class="space-y-1 text-right">
                <span class="text-[7px] font-black text-slate-500 uppercase">Training Hours</span>
                <p class="text-xl font-black text-white tracking-tighter">122h</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { RocketLaunchIcon } from '@heroicons/vue/24/outline';
import { ref } from 'vue';

const milestones = ref([
    { label: 'Technical Proficiency', progress: 85 },
    { label: 'Leadership SoftSkills', progress: 64 },
    { label: 'Project Impact Ratio', progress: 92 },
    { label: 'System Architecture', progress: 45 },
]);
</script>
