<template>
    <div class="space-y-6">
        <!-- 3D Funnel Graph Section -->
        <div class="bg-gray-900 p-10 rounded-[40px] shadow-2xl shadow-indigo-500/10 min-h-[600px] relative overflow-hidden group">
            <div class="flex justify-between items-start mb-16 relative z-10">
                <div>
                     <p class="text-sm font-black uppercase tracking-widest text-indigo-400 mb-2">Revenue Lifecycle Engine</p>
                    <h2 class="text-3xl font-black text-white tracking-tight flex items-center gap-4">
                        Interactive 3D Revenue Funnel
                        <span class="px-3 py-1 rounded bg-white/10 text-emerald-400 border border-white/10 text-sm font-black uppercase tracking-widest animate-pulse">
                            Live Revenue: {{ funnel_data.Revenue }}
                        </span>
                    </h2>
                </div>
                <div class="flex gap-2">
                    <button class="px-6 py-3 bg-white/10 text-white rounded-2xl text-sm font-black border border-white/10 hover:bg-white/20 transition-all uppercase tracking-widest shadow-lg backdrop-blur-md">🔍 DRILL DOWN</button>
                    <button class="px-6 py-3 bg-indigo-600 text-white rounded-2xl text-sm font-black shadow-lg shadow-indigo-500/30 hover:scale-105 transition-all uppercase tracking-widest border border-indigo-500 overflow-hidden group/btn">
                         <span class="relative z-10">PREDICT NEXT MONTH</span>
                         <div class="absolute inset-0 bg-white/20 translate-x-[-100%] group-hover/btn:translate-x-[100%] transition-transform duration-700"></div>
                    </button>
                </div>
            </div>

            <!-- 3D Funnel Mock -->
            <div class="relative py-20 flex justify-center items-center scale-125 perspective-1000 group-hover:scale-[1.3] transition-transform duration-1000">
                <div class="flex flex-col items-center">
                    <div v-for="(val, label) in filteredFunnel" :key="label" 
                        class="funnel-layer relative cursor-pointer group/layer transition-all duration-500 hover:z-50"
                        :style="{ width: getLayerWidth(label) + 'px', height: '60px', marginBottom: '-5px' }"
                    >
                        <div class="absolute inset-x-0 inset-y-0 rounded-xl bg-gradient-to-r from-indigo-500/20 via-indigo-600/40 to-indigo-500/20 border-x-2 border-indigo-400/30 shadow-[0_20px_50px_rgba(79,_70,_229,_0.3)] group-hover/layer:shadow-indigo-500/50 group-hover/layer:scale-x-110 group-hover/layer:rotate-x-12 transition-all duration-700"></div>
                        <div class="relative z-10 flex items-center justify-between px-8 h-full">
                            <span class="text-sm font-black text-indigo-100 uppercase tracking-widest opacity-80">{{ label }}</span>
                            <span class="text-lg font-black text-white drop-shadow-lg">{{ val.toLocaleString() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="absolute bottom-10 left-10 p-8 rounded-3xl bg-white/5 backdrop-blur-xl border border-white/10 shadow-2xl max-w-sm group-hover:scale-105 transition-all duration-700">
                <h4 class="text-xs font-black text-white mb-6 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/20 flex items-center justify-center text-indigo-400 border border-indigo-500/30 shadow-inner">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    DROPOFF ANALYSIS (AI DETECTED)
                </h4>
                <div class="space-y-6">
                    <div v-for="(risk, reason) in dropoff_reasons" :key="reason">
                        <div class="flex justify-between items-center mb-2">
                             <span class="text-sm font-bold text-gray-400 tracking-tight">{{ reason }}</span>
                             <span class="text-rose-400 font-black text-sm">{{ risk }}</span>
                        </div>
                        <div class="h-1.5 w-full bg-white/5 rounded-full overflow-hidden">
                             <div class="h-full bg-rose-500" :style="{ width: risk }"></div>
                        </div>
                    </div>
                </div>
                <button class="mt-8 w-full py-3 bg-white text-gray-900 rounded-2xl text-xs font-black shadow-lg hover:bg-gray-100 transition-all uppercase tracking-widest">FIX DROPOFFS</button>
            </div>
            
            <!-- Animated Background Grid -->
            <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.05)_1px,transparent_1px)] bg-[size:40px_40px] opacity-10 pointer-events-none group-hover:bg-[size:60px_60px] transition-all duration-1000"></div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    funnel_data: Object,
    dropoff_reasons: Object
});

const filteredFunnel = computed(() => {
    const data = { ...props.funnel_data };
    delete data.Revenue;
    return data;
});

const getLayerWidth = (label) => {
    const widths = {
        'Impression': 500,
        'Click': 420,
        'Lead': 340,
        'Deal': 260
    };
    return widths[label] || 200;
};
</script>

<style scoped>
.perspective-1000 {
    perspective: 1000px;
}
.rotate-x-12 {
    transform: rotateX(12deg);
}
</style>
