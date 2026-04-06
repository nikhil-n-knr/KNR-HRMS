<template>
    <div class="w-full bg-slate-900 border-y border-white/5 py-3 overflow-hidden group select-none relative">
        <!-- Abstract Background -->
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(16,185,129,0.05),transparent)] pointer-events-none"></div>
        
        <div class="max-w-7xl mx-auto px-10 flex items-center gap-6 relative z-10">
            <div class="flex items-center gap-2 flex-shrink-0">
                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_10px_rgba(16,185,129,1)]"></span>
                <span class="text-sm font-black text-emerald-500 uppercase tracking-[0.3em]">Live Heartbeat</span>
            </div>

            <div class="flex-1 overflow-hidden pointer-events-none">
                <!-- Double the list for seamless looping -->
                <div class="flex items-center gap-12 whitespace-nowrap animate-ticker pointer-events-auto hover:[animation-play-state:paused] cursor-help">
                    <div v-for="n in 2" :key="n" class="flex items-center gap-12">
                        <div v-for="(item, idx) in activity" :key="idx" class="flex items-center gap-4">
                            <span class="text-sm font-black text-slate-500 uppercase tracking-widest tabular-nums font-mono">BT-{{ item.id.toString().padStart(4, '0') }}</span>
                            <span class="text-sm font-bold text-white uppercase tracking-tight">{{ item.label }}</span>
                            <span class="px-2 py-0.5 bg-white/5 rounded-md text-xs font-black text-emerald-400 uppercase tracking-widest border border-white/5">{{ item.status }}</span>
                            <span class="text-sm font-bold text-slate-600 uppercase">{{ item.time }}</span>
                            <!-- Separator -->
                            <div class="h-1 w-1 rounded-full bg-slate-800 mx-4"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="loading" class="flex-shrink-0">
                <ArrowPathIcon class="w-3 h-3 text-slate-500 animate-spin" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { ArrowPathIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

const activity = ref([]);
const loading = ref(true);
let timer = null;

const fetchPulse = async () => {
    try {
        const { data } = await axios.get(route('portal.pulse'));
        activity.value = data;
    } catch (e) {
        console.error("Pulse sync failed", e);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchPulse();
    timer = setInterval(fetchPulse, 30000); // 30s refresh
});

onUnmounted(() => {
    clearInterval(timer);
});
</script>

<style scoped>
@keyframes ticker {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}
.animate-ticker {
    display: flex;
    animation: ticker 60s linear infinite;
    width: max-content;
}
/*.group-hover\:pause-animation:hover { animation-play-state: paused; }*/

/* Double the items for seamless loop if needed, but for now we rely on a long list */
</style>
