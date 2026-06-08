<template>
    <div class="relative py-12 px-6 overflow-hidden">
        <!-- Connecting Line -->
        <div class="absolute top-1/2 left-0 w-full h-1 bg-slate-100 -translate-y-1/2 z-0"></div>
        <div class="absolute top-1/2 left-0 h-1 bg-emerald-500 -translate-y-1/2 z-0 transition-all duration-[1.5s] ease-in-out shadow-[0_0_15px_rgba(16,185,129,0.5)]" 
             :style="{ width: progressWidth + '%' }"></div>

        <div class="relative z-10 flex justify-between items-start">
            <div v-for="(stage, index) in timelineStages" :key="stage.id" 
                 class="flex flex-col items-center group"
                 :style="{ width: (100 / timelineStages.length) + '%' }">
                
                <!-- Node -->
                <div :class="[
                    'w-12 h-12 rounded-full flex items-center justify-center border-4 transition-all duration-700 relative',
                    stage.reached ? 'border-emerald-500 bg-emerald-500 text-white shadow-xl shadow-emerald-500/20 scale-110' : 'border-slate-100 bg-white text-slate-300'
                ]">
                    <!-- Pulsing indicator if currently here -->
                    <div v-if="stage.isCurrent" class="absolute -inset-2 rounded-full border-2 border-emerald-500 animate-ping opacity-30"></div>
                    
                    <CheckIcon v-if="stage.reached && !stage.isCurrent" class="w-6 h-6 stroke-[3]" />
                    <component v-else :is="getStageIcon(stage)" :class="['w-6 h-6', stage.reached ? 'animate-pulse' : '']" />
                </div>

                <!-- Label -->
                <div class="mt-6 text-center space-y-1">
                    <h4 :class="['text-base font-black uppercase tracking-[0.2em]', stage.reached ? 'text-slate-900' : 'text-slate-400']">
                        {{ stage.name }}
                    </h4>
                    <p v-if="stage.timestamp" class="text-sm font-bold text-emerald-600 uppercase tracking-widest tabular-nums">
                        {{ formatTime(stage.timestamp) }}
                    </p>
                    <p v-else class="text-sm font-black text-slate-300 uppercase tracking-widest">
                        Pending
                    </p>
                </div>

                <!-- Milestone Detail (Tooltip style) -->
                <div class="mt-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                     <span class="px-3 py-1 bg-slate-900 text-white text-xs font-black uppercase tracking-widest rounded-full shadow-lg">
                        {{ stage.reached ? 'Milestone Cleared' : 'In Queue' }}
                     </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { 
    CheckIcon, 
    PlayIcon, 
    BeakerIcon, 
    ShieldCheckIcon, 
    ArchiveBoxIcon,
    InboxIcon,
    SparklesIcon,
    CommandLineIcon
} from '@heroicons/vue/24/outline';
import dayjs from 'dayjs';

const props = defineProps({
    transitions: { type: Array, default: () => [] },
    allStages: { type: Array, default: () => [] },
    currentStageId: Number
});

const timelineStages = computed(() => {
    return props.allStages.map((stage, index) => {
        // Find if this stage was ever reached
        const transition = props.transitions.find(t => t.to_stage_id === stage.id);
        const reached = !!transition;
        const isCurrent = props.currentStageId === stage.id;
        
        return {
            ...stage,
            reached,
            isCurrent,
            timestamp: transition?.created_at
        };
    });
});

const progressWidth = computed(() => {
    // Find highest reached stage index
    let lastIndex = -1;
    timelineStages.value.forEach((s, i) => {
        if (s.reached) lastIndex = i;
    });
    
    if (lastIndex === -1) return 0;
    if (lastIndex === timelineStages.value.length - 1) return 100;
    
    // Width is percentage of stages reached
    return (lastIndex / (timelineStages.value.length - 1)) * 100;
});

const getStageIcon = (stage) => {
    const name = stage.name.toLowerCase();
    if (name.includes('triage') || name.includes('new')) return InboxIcon;
    if (name.includes('progress') || name.includes('dev')) return CommandLineIcon;
    if (name.includes('qa') || name.includes('test')) return BeakerIcon;
    if (name.includes('verify')) return ShieldCheckIcon;
    if (name.includes('closed') || name.includes('final')) return ArchiveBoxIcon;
    return SparklesIcon;
};

const formatTime = (time) => dayjs(time).format('MMM D, h:mm A');
</script>
