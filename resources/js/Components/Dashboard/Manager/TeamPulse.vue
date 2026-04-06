<template>
    <div class="bg-white/40 backdrop-blur-xl border border-white/40 rounded-3xl p-5 shadow-xl h-full flex flex-col group relative overflow-hidden">
        <div class="flex justify-between items-center mb-6 relative z-10">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] flex items-center gap-2">
                <UserGroupIcon class="w-4 h-4 text-emerald-600" />
                Team_Operational_Pulse
            </h3>
            <div class="flex items-center gap-1.5">
                <span class="w-1 h-1 bg-emerald-500 rounded-full"></span>
                <span class="text-[8px] font-black text-emerald-600 uppercase tracking-widest text-right">Velocity: {{ performance.velocity }}%</span>
            </div>
        </div>

        <div class="flex-grow space-y-6 relative z-10">
            <!-- Main Stats -->
            <div class="flex items-end justify-between">
                <div>
                    <h4 class="text-3xl font-black text-slate-800 tracking-tighter">{{ presentCount }} <span class="text-slate-300">/ {{ totalCount }}</span></h4>
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mt-1">Deployment Strength</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-black text-slate-800">{{ performance.blockers }} Active</p>
                    <p class="text-[8px] font-bold text-rose-500 uppercase tracking-widest">Blockers</p>
                </div>
            </div>

            <!-- Heatmap / Weekly Trend -->
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <p class="text-[7px] font-black text-slate-500 uppercase tracking-widest">Engagement_Weekly_Heatmap</p>
                    <span class="text-[7px] font-bold text-slate-400">MTWTFSS</span>
                </div>
                <div class="flex gap-1 h-6 items-end">
                    <div v-for="(val, idx) in performance.weekly_engagement" :key="idx" 
                         class="flex-1 bg-emerald-500/20 rounded-sm hover:bg-emerald-500 transition-colors cursor-help"
                         :style="{ height: (val/100 * 100) + '%' }"
                         v-tooltip="val + '%'">
                    </div>
                </div>
            </div>

            <!-- Top Performers Mini-List -->
            <div class="space-y-2 pt-2">
                <p class="text-[7px] font-black text-slate-500 uppercase tracking-widest border-b border-slate-100 pb-1">Top_Performers_Intelligence</p>
                <div v-for="performer in performance.top_performers" :key="performer.name" class="flex items-center justify-between group/p">
                    <div class="flex items-center gap-2">
                        <div class="w-5 h-5 bg-slate-100 rounded-full flex items-center justify-center text-[8px] font-bold text-slate-500">{{ performer.name.charAt(0) }}</div>
                        <span class="text-[9px] font-bold text-slate-600 truncate max-w-[80px] group-hover/p:text-emerald-600 transition-colors">{{ performer.name }}</span>
                    </div>
                    <span class="text-[9px] font-black text-emerald-600">{{ performer.score }}%</span>
                </div>
            </div>
        </div>

        <button class="mt-6 w-full py-2.5 bg-slate-900 text-white text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-600 transition-all active:scale-95 shadow-lg">
            Assembly Console
        </button>
    </div>
</template>

<script setup>
import { UserGroupIcon } from '@heroicons/vue/24/outline';
import { computed } from 'vue';

const props = defineProps({
    presentCount: { type: Number, default: 0 },
    totalCount: { type: Number, default: 0 },
    performance: { type: Object, default: () => ({ velocity: 0, blockers: 0, weekly_engagement: [], top_performers: [] }) }
});

const attendanceRate = computed(() => {
    if (props.totalCount === 0) return 0;
    return Math.round((props.presentCount / props.totalCount) * 100);
});
</script>
