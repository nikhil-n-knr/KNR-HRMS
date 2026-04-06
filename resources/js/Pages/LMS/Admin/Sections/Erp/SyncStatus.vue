<template>
    <div class="space-y-12 animate-fade-in font-sans">
        <!-- Conditional View Rendering -->
        <div v-if="activeTab === 'sync' || activeTab === 'overview' || !activeTab" class="space-y-12">
            <!-- Strategic Header: Integration Bridge -->
            <div class="bg-gray-900 p-12 rounded-[4rem] text-white shadow-2xl relative overflow-hidden group">
                <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-12 text-center lg:text-left">
                    <div class="space-y-8 flex-1">
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-500/10 rounded-full border border-emerald-500/20">
                            <span class="text-[9px] font-black text-emerald-400 uppercase tracking-widest leading-none">Bridge Active v2.0</span>
                        </div>
                        <h2 class="text-4xl md:text-6xl font-black tracking-tighter italic uppercase border-l-4 border-emerald-600 pl-8 leading-none">ERP FORGE SYNC</h2>
                        <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4 pt-4">
                            <button class="px-8 py-4 bg-emerald-600 text-white rounded-2xl text-[10px] font-black uppercase shadow-xl hover:bg-emerald-700 transition-all flex items-center gap-3">
                                <i class="fas fa-sync animate-spin-slow"></i> Initiate Global Pulse
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Table -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div v-for="m in syncStats" :key="m.label" class="bg-white p-8 rounded-[3rem] border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-2xl transition-all duration-500">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1.5 font-sans">{{ m.label }}</p>
                    <h3 class="text-3xl font-black text-gray-900 group-hover:text-emerald-600 tabular-nums italic tracking-tighter">{{ m.value }}</h3>
                </div>
            </div>
        </div>

        <!-- Audit Stream Section -->
        <div v-else-if="activeTab === 'logs'" class="bg-white rounded-[4rem] border border-gray-100 shadow-sm overflow-hidden flex flex-col">
            <div class="p-10 border-b border-gray-50 bg-gray-50/10">
                <h3 class="text-xl font-black text-gray-900 tracking-tight uppercase italic mb-1">Audit Diagnostic Stream</h3>
            </div>
            <div class="divide-y divide-gray-50 overflow-y-auto max-h-[600px] custom-scrollbar">
                <div v-for="i in 8" :key="i" class="p-8 flex items-center justify-between hover:bg-emerald-50/30 transition-all group">
                    <div class="flex items-center gap-8">
                        <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-300 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm">
                             <i class="fas fa-database text-lg"></i>
                        </div>
                        <div>
                             <p class="text-xs font-black text-gray-800 uppercase italic">Sequence: Profile Replication #{{ 20400 + i }}</p>
                             <p class="text-[8px] font-medium text-gray-400 uppercase tracking-widest mt-1">ORACLE CLOUD ENDPOINT B-{{i}}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-12">
                         <div class="text-right"><p class="text-xs font-black text-emerald-600 tabular-nums mb-1">Success</p></div>
                         <div class="text-right text-[10px] text-gray-300 font-black italic">10:48 PM</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bridge Config Section -->
        <div v-else-if="activeTab === 'gateway'" class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
            <div v-for="i in 3" :key="i" class="bg-white p-12 rounded-[4rem] border border-gray-100 shadow-lg hover:shadow-2xl transition-all group cursor-alias">
                <div class="w-20 h-20 rounded-[2.5rem] bg-emerald-50 flex items-center justify-center text-emerald-600 text-3xl mx-auto mb-8 group-hover:rotate-12 group-hover:scale-110 transition-transform">
                     <i class="fas fa-plug"></i>
                </div>
                <h4 class="text-sm font-black text-gray-900 tracking-tight uppercase italic mb-2">Gateway: ERP Cluster Model {{ i }}</h4>
                <p class="text-[10px] font-medium text-gray-400 leading-relaxed font-sans mb-10 italic">Secure data port for bi-directional enterprise parity replication.</p>
                <button class="px-6 py-3 bg-gray-900 text-white rounded-2xl text-[9px] font-black uppercase tracking-widest shadow-xl hover:bg-black transition-all">Connect Hub</button>
            </div>
        </div>

    </div>
</template>

<script setup>
defineProps({
    activeTab: String
});

const syncStats = [
    { label: 'Uptime Integrity', value: '99.99%' },
    { label: 'Push Velocity', value: '1.2k/min' },
    { label: 'Diagnostic Load', value: '42%' },
    { label: 'Last Sync', value: '2s Ago' },
];
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

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(16, 185, 129, 0.1);
    border-radius: 20px;
}
</style>
