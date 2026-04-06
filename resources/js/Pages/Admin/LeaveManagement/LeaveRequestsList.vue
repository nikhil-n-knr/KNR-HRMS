<template>
    <div class="space-y-10 font-outfit animate-in fade-in slide-in-from-bottom-5 duration-700">
        <!-- Premium Initializing Screen -->
        <div class="bg-white rounded-[2.5rem] p-20 border border-slate-100 shadow-2xl shadow-slate-200/40 flex flex-col items-center text-center relative overflow-hidden group">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-indigo-50/50 via-transparent to-transparent"></div>
            
            <div class="relative mb-10">
                <div class="w-32 h-32 bg-slate-900 rounded-[2.5rem] flex items-center justify-center text-white shadow-2xl group-hover:rotate-12 transition-transform duration-700">
                    <InboxStackIcon class="w-16 h-16 text-indigo-400" />
                </div>
                <div class="absolute -right-4 -bottom-4 w-12 h-12 bg-emerald-500 rounded-2xl flex items-center justify-center text-white shadow-xl border-4 border-white animate-bounce">
                    <BoltIcon class="w-6 h-6" />
                </div>
            </div>

            <h2 class="text-3xl font-black text-slate-900 uppercase tracking-tight mb-4">Leave Application Matrix</h2>
            <div class="h-1.5 w-24 bg-gradient-to-r from-indigo-500 to-violet-600 rounded-full mb-8 shadow-sm"></div>
            
            <p class="text-sm font-black text-slate-400 uppercase tracking-[0.5em] max-w-md leading-relaxed mb-12">
                Operational protocol for personnel absence tracking is being finalized. Enhanced tactical queue incoming.
            </p>

            <div class="flex items-center gap-6">
                <div class="flex items-center gap-2 px-4 py-2 bg-slate-50 rounded-xl border border-slate-100">
                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Logic_Verified</span>
                </div>
                <div class="flex items-center gap-2 px-4 py-2 bg-slate-50 rounded-xl border border-slate-100">
                    <div class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse delay-75"></div>
                    <span class="text-xs font-black text-slate-500 uppercase tracking-widest">UI_Synthesis</span>
                </div>
            </div>
        </div>

        <!-- Projected Metrics (Skeleton) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 opacity-40 grayscale group-hover:grayscale-0 transition-all duration-1000">
            <div v-for="i in 3" :key="i" class="h-32 bg-white/50 border border-dashed border-slate-200 rounded-[2rem] flex items-center justify-center">
                <div class="w-12 h-2 bg-slate-100 rounded-full"></div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { InboxStackIcon, BoltIcon } from '@heroicons/vue/24/outline';
</script>
