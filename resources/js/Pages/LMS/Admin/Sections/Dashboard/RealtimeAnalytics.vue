<template>
    <div class="space-y-12 animate-fade-in font-sans pb-24">
        <!-- Pulse View (Real-time) -->
        <div v-if="activeTab === 'realtime' || !activeTab" class="space-y-12">
            <!-- Strategic Header: Pulse Status -->
            <div class="flex items-center justify-between border-b border-emerald-100 pb-8">
                <div>
                    <h2 class="text-2xl font-black text-gray-900 tracking-tight uppercase italic mb-1">COMMAND CENTER PULSE</h2>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Global real-time intelligence and neural streaming diagnostics</p>
                </div>
                <div class="flex items-center gap-4">
                    <button @click="exportStrategicData" class="flex items-center gap-2 px-6 py-3 bg-gray-900 text-white rounded-2xl text-[9px] font-black uppercase tracking-widest shadow-xl hover:bg-black transition-all group">
                        <i class="fas fa-file-export group-hover:rotate-12 transition-transform"></i>
                        Strategic Export
                    </button>
                    <div class="flex items-center gap-2 px-4 py-2 bg-emerald-50 rounded-full border border-emerald-200 uppercase tracking-widest leading-none">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                        <span class="text-[9px] font-black text-emerald-600">Telemetry Active</span>
                    </div>
                </div>
            </div>

            <!-- Metric Command Block -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div v-for="m in liveMetrics" :key="m.label" 
                    @click="$emit('action', { type: 'navigate', id: m.target })"
                    class="bg-white p-8 rounded-[3.5rem] border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-2xl hover:border-emerald-100 transition-all duration-500 cursor-pointer"
                >
                    <div class="relative z-10 flex flex-col h-full justify-between gap-6">
                        <div class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm">
                             <i :class="m.icon"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">{{ m.label }}</p>
                            <h3 class="text-3xl font-black italic tracking-tighter tabular-nums text-gray-900 group-hover:text-emerald-600 transition-colors">{{ m.value }}</h3>
                        </div>
                    </div>
                    <div class="absolute -right-12 -bottom-12 w-32 h-32 bg-emerald-50 rounded-full opacity-40 group-hover:scale-150 transition-transform duration-1000"></div>
                </div>
            </div>

            <!-- Core Intelligence Matrix -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-10">
                <div class="xl:col-span-1 bg-gray-900 p-10 rounded-[4rem] text-white shadow-2xl relative overflow-hidden group">
                     <div class="relative z-10 space-y-8">
                        <div class="flex items-center justify-between border-b border-white/10 pb-6">
                            <h4 class="text-xl font-black italic uppercase tracking-tighter text-white">Sequential Hub Log</h4>
                            <span class="text-[9px] font-black text-emerald-400 uppercase tracking-widest animate-pulse font-sans">Streaming...</span>
                        </div>
                        <div class="space-y-8">
                            <div v-for="i in 5" :key="i" class="flex gap-5 group/item cursor-pointer" @click="$emit('action', { type: 'navigate', id: 'erp' })">
                                <div class="w-12 h-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-[10px] font-black group-hover/item:bg-emerald-600 group-hover/item:border-emerald-600 transition-all duration-500 shrink-0">
                                     {{ i * 3 }}m
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-black tracking-tight leading-none mb-2 group-hover/item:text-emerald-400 truncate uppercase tracking-widest border-b border-white/5 pb-2">Sync: Matrix Event #{{ 800 + i }}</p>
                                    <p class="text-[9px] font-bold text-white/30 uppercase tracking-[0.2em] italic truncate">Node Verification: Cloud Segment Alpha.{{i}}</p>
                                </div>
                            </div>
                        </div>
                     </div>
                </div>

                <div class="xl:col-span-2 bg-white p-12 rounded-[4rem] border border-gray-100 shadow-sm relative overflow-hidden group flex flex-col">
                     <div class="flex items-center justify-between mb-12">
                         <div class="space-y-1">
                             <h4 class="text-2xl font-black italic uppercase tracking-tighter text-gray-900 border-l-4 border-emerald-500 pl-6">Engagement Gradient</h4>
                             <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest pl-6">24-Hour Adaptive Pupil Response Matrix</p>
                         </div>
                         <div class="flex items-center gap-4">
                             <div class="flex items-center gap-2">
                                 <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                                 <span class="text-[9px] font-black uppercase tracking-widest text-gray-400">Peak Activity</span>
                             </div>
                         </div>
                     </div>
                     <div class="flex-1 min-h-[350px] flex items-end justify-between gap-3 overflow-hidden px-4">
                         <div v-for="i in 30" :key="i" 
                            @click="$emit('action', { type: 'navigate', id: 'enrollment', tab: 'batches' })"
                            class="flex-1 bg-emerald-50 rounded-t-2xl group-hover:bg-emerald-600 transition-all duration-[1200ms] origin-bottom hover:scale-x-110 hover:opacity-80 cursor-pointer"
                            :style="{ height: `${Math.random() * 80 + 20}%`, transitionDelay: `${i * 20}ms` }"
                         ></div>
                     </div>
                </div>
            </div>
        </div>

        <!-- Mastery Matrix Section -->
        <div v-else-if="activeTab === 'mastery'" class="space-y-12">
            <h3 class="text-xl font-black text-gray-900 tracking-tight uppercase italic mb-8 border-l-4 border-emerald-500 pl-6">Cognitive Mastery Hub</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div v-for="i in 6" :key="i" class="bg-white p-10 rounded-[3.5rem] border border-gray-100 shadow-md hover:shadow-2xl transition-all group overflow-hidden relative">
                    <div class="relative z-10 flex flex-col items-center gap-8">
                        <div class="w-28 h-28 rounded-full border-[6px] border-emerald-50 flex items-center justify-center text-emerald-600 text-3xl font-black group-hover:border-emerald-500 group-hover:scale-105 transition-all duration-700 relative">
                             {{ 84 + i }}%
                             <div class="absolute inset-0 rounded-full border-[6px] border-emerald-500 border-t-transparent animate-spin-slow"></div>
                        </div>
                        <div class="text-center space-y-2">
                            <h3 class="text-sm font-black text-gray-900 uppercase italic">Intelligence Hub: Quantum HR v.{{i}}</h3>
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest tracking-[0.2em] italic border-t border-gray-50 pt-2">Global Aggregate Pass Matrix</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Diagnostic Section -->
        <div v-else-if="activeTab === 'revenue'" class="space-y-12">
            <h3 class="text-xl font-black text-gray-900 tracking-tight uppercase italic mb-8 border-l-4 border-emerald-500 pl-6">Financial Intelligence Diagnostic</h3>
            <div class="bg-gray-900 p-24 rounded-[4rem] flex flex-col items-center justify-center gap-12 text-white shadow-2xl relative overflow-hidden group">
                <div class="w-24 h-24 rounded-full bg-emerald-600 shadow-xl shadow-emerald-500/20 flex items-center justify-center text-white text-4xl animate-bounce">
                    <i class="fas fa-sack-dollar"></i>
                </div>
                <div class="text-center space-y-6">
                    <p class="text-[10px] font-black uppercase tracking-[0.4em] text-emerald-400">Real-Time Treasury Audit Active</p>
                    <h2 class="text-5xl font-black italic tracking-tighter uppercase leading-none">Initializing Revenue Engine v9.2.0</h2>
                    <p class="text-[11px] font-medium text-white/40 tracking-widest lowercase italic font-sans max-w-lg mx-auto leading-relaxed">Cross-referencing global subscription nodes with stripe-api ledger cluster. Latency: 0.08ms. Parity: 100%.</p>
                </div>
                <div class="w-80 h-1.5 bg-white/10 rounded-full overflow-hidden border border-white/5">
                    <div class="h-full bg-emerald-500 w-[72%] animate-pulse shadow-lg shadow-emerald-500/50"></div>
                </div>
                <!-- Abstract Neural Ornaments -->
                <div class="absolute -right-40 -top-40 w-96 h-96 bg-emerald-500/10 rounded-full blur-[150px] pointer-events-none"></div>
                <div class="absolute -left-40 -bottom-40 w-96 h-96 bg-teal-500/10 rounded-full blur-[150px] pointer-events-none"></div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    stats: Object,
    activeTab: String
});

const liveMetrics = [
    { label: 'Live Hub Minds', value: '428', icon: 'fas fa-users-viewfinder', target: 'enrollment' },
    { label: 'Integration Ticks', value: '8.2k', icon: 'fas fa-bolt-lightning', target: 'erp' },
    { label: 'Neural Throughput', value: '94%', icon: 'fas fa-brain', target: 'analytics' },
    { label: 'CDN Uptime Matrix', value: '99.9%', icon: 'fas fa-shield-halved', target: 'dashboard' },
];

const exportStrategicData = () => {
    const data = [
        ['Course ID', 'Student Name', 'Progress %', 'Last Active', 'Status'],
        ['EV-101', 'Nikhil Soni', '92', '2026-03-22', 'In Progress'],
        ['BMS-202', 'Amit Sharma', '100', '2026-03-21', 'Completed'],
        ['SOLAR-50', 'Priya Das', '45', '2026-03-22', 'At Risk'],
    ];

    const csvContent = "data:text/csv;charset=utf-8," 
        + data.map(e => e.join(",")).join("\n");

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "LMS_Strategic_Progress_Report.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap');
.font-sans { font-family: 'Outfit', sans-serif; }

.animate-spin-slow {
    animation: spin 3s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
