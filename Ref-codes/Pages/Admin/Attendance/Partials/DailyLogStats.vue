<template>
    <div class="space-y-10 animate-in fade-in slide-in-from-bottom-5 duration-700">
        <!-- Intelligence KPI Matrix -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
             <div class="group bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:border-emerald-500 transition-all duration-300">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Average Operational Runtime</p>
                        <h3 class="text-2xl font-black text-slate-800 tracking-tight leading-none group-hover:text-emerald-600 transition-colors">08h 42m</h3>
                    </div>
                    <div class="w-10 h-10 bg-emerald-50 rounded-lg flex items-center justify-center text-emerald-600 group-hover:rotate-12 transition-transform">
                        <i class="fas fa-stopwatch text-[14px]"></i>
                    </div>
                </div>
                <div class="flex items-center gap-2 mt-4">
                    <span class="text-emerald-500 text-sm font-black flex items-center bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-100 uppercase tracking-widest">
                        <i class="fas fa-caret-up mr-1"></i> +12% Efficiency
                    </span>
                    <span class="text-xs font-bold text-slate-300 uppercase tracking-widest leading-none">vs baseline</span>
                </div>
             </div>

             <div class="group bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:border-emerald-500 transition-all duration-300">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Tactical Arrival Accuracy</p>
                        <h3 class="text-2xl font-black text-slate-800 tracking-tight leading-none group-hover:text-emerald-600 transition-colors">94.2%</h3>
                    </div>
                    <div class="w-10 h-10 bg-emerald-50 rounded-lg flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-transform">
                        <i class="fas fa-bullseye text-[14px]"></i>
                    </div>
                </div>
                <div class="flex items-center gap-2 mt-4">
                    <span class="text-emerald-500 text-sm font-black flex items-center bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-100 uppercase tracking-widest">
                        <i class="fas fa-caret-up mr-1"></i> +2.1% Sync
                    </span>
                    <span class="text-xs font-bold text-slate-300 uppercase tracking-widest leading-none">stabilized</span>
                </div>
             </div>

             <div class="group bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:border-emerald-500 transition-all duration-300">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Extended Duration Claims</p>
                        <h3 class="text-2xl font-black text-slate-800 tracking-tight leading-none group-hover:text-rose-600 transition-colors">12</h3>
                    </div>
                    <div class="w-10 h-10 bg-rose-50 rounded-lg flex items-center justify-center text-rose-600 group-hover:-rotate-12 transition-transform">
                        <i class="fas fa-bolt-lightning text-[14px]"></i>
                    </div>
                </div>
                <div class="flex items-center gap-2 mt-4">
                    <span class="text-rose-500 text-sm font-black flex items-center bg-rose-50 px-2.5 py-1 rounded-md border border-rose-100 uppercase tracking-widest">
                        <i class="fas fa-caret-up mr-1"></i> Critical Spike
                    </span>
                    <span class="text-xs font-bold text-slate-300 uppercase tracking-widest leading-none">manual audit required</span>
                </div>
             </div>
        </div>

        <!-- Attendance Pulse Generator -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-8">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-slate-900 rounded-lg flex items-center justify-center text-white shadow-sm">
                        <i class="fas fa-chart-line text-[14px]"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-800 tracking-tight leading-none uppercase">Mobilization Lifecycle</h3>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] mt-2 leading-none">Chronological Attendance Intelligence</p>
                    </div>
                </div>
                
                <div class="flex bg-slate-100 p-1 rounded-lg border border-slate-200">
                    <button class="px-4 py-1.5 text-sm font-black uppercase tracking-widest bg-white text-emerald-600 rounded-md shadow-sm transition-all">7-DAY SCAN</button>
                    <button class="px-4 py-1.5 text-sm font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-all">30-DAY SYNC</button>
                </div>
            </div>
            
            <div class="h-80 flex items-end justify-between px-6 gap-6 relative">
                <!-- Data Grid Visualization Lines -->
                <div class="absolute inset-x-0 top-0 bottom-0 flex flex-col justify-between py-2 opacity-10 pointer-events-none">
                    <div v-for="l in 4" :key="l" class="w-full border-t-2 border-slate-900 border-dashed"></div>
                </div>

                <!-- Strategic Data Bars -->
                <div v-for="(val, day) in {'MON': 60, 'TUE': 85, 'WED': 75, 'THU': 95, 'FRI': 88, 'SAT': 35, 'SUN': 25}" :key="day" class="w-full relative group">
                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 opacity-0 group-hover:opacity-100 scale-90 group-hover:scale-100 transition-all duration-300 z-20">
                        <div class="bg-slate-900 text-white px-3 py-1.5 rounded-lg text-sm font-black whitespace-nowrap shadow-xl uppercase tracking-widest">
                            {{ val }}% SYNC
                            <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></div>
                        </div>
                    </div>
                    
                    <div 
                        class="w-full rounded-xl transition-all duration-1000 ease-out relative overflow-hidden shadow-sm group-hover:scale-105 group-hover:brightness-110"
                        :class="val > 40 ? 'bg-gradient-to-t from-emerald-600 to-emerald-400' : 'bg-gradient-to-t from-slate-200 to-slate-100'"
                        :style="{ height: val + '%' }"
                    >
                        <div v-if="val > 40" class="absolute top-0 inset-x-0 h-1/2 bg-gradient-to-t from-transparent to-white/10"></div>
                        <div v-if="val > 40" class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <i class="fas fa-bolt text-white/40 text-sm"></i>
                        </div>
                    </div>
                    
                    <div class="mt-6 text-center">
                        <p class="text-sm font-black text-slate-400 tracking-widest group-hover:text-emerald-600 transition-colors uppercase tabular-nums">{{ day }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
// Interactive Intelligence Logic Ingested
</script>
