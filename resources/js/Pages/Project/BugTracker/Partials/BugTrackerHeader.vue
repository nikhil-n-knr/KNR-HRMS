<template>
    <div class="bg-white/90 backdrop-blur-md border-b border-gray-200 px-4 md:px-6 py-3 shadow-sm sticky top-0 z-10">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 font-inter">
            <!-- Segment 1: Quick-Pivot Toggle -->
            <div class="flex items-center gap-1 bg-gray-100/80 p-1 rounded-xl w-auto lg:w-fit border border-gray-200 overflow-x-auto no-scrollbar snap-x">
                <button 
                    v-for="mode in modes" 
                    :key="mode.id"
                    @click="store.viewMode = mode.id"
                    :class="[
                        'px-4 py-2 text-sm font-black uppercase tracking-widest rounded-lg transition-all whitespace-nowrap snap-center',
                        store.viewMode === mode.id 
                            ? 'bg-white text-emerald-700 shadow-sm border border-gray-200' 
                            : 'text-gray-500 hover:text-gray-700 hover:bg-white/50'
                    ]"
                >
                    <component :is="mode.icon" class="w-3.5 h-3.5 inline-block mr-1.5 -mt-0.5" />
                    {{ mode.label }}
                </button>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 w-full lg:w-auto">
                <!-- Segment 2: Context Selector -->
                <div class="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto">
                    <div class="relative w-full sm:min-w-[160px] lg:min-w-[180px]">
                        <select 
                            v-model="store.selectedProject"
                            class="w-full pl-9 pr-4 py-2 text-xs font-bold border-gray-200 rounded-xl focus:ring-4 focus:ring-emerald-500/5 focus:border-emerald-500 bg-gray-50/50 appearance-none transition-all shadow-sm"
                        >
                            <option :value="null">Global Scope</option>
                            <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                        <Squares2X2Icon class="w-4 h-4 text-gray-400 absolute left-3 top-2.5 pointer-events-none" />
                    </div>
                    
                    <div class="relative w-full sm:min-w-[160px] lg:min-w-[180px]">
                        <select 
                            v-model="store.selectedModule"
                            class="w-full pl-9 pr-4 py-2 text-xs font-bold border-gray-200 rounded-xl focus:ring-4 focus:ring-emerald-500/5 focus:border-emerald-500 bg-gray-50/50 appearance-none transition-all shadow-sm"
                        >
                            <option :value="null">All Zones</option>
                            <option v-for="m in activeModules" :key="m.id" :value="m.id">{{ m.name }}</option>
                        </select>
                        <CpuChipIcon class="w-4 h-4 text-gray-400 absolute left-3 top-2.5 pointer-events-none" />
                    </div>
                </div>

                <!-- Segment 3: Time-Machine Toggle -->
                <div class="flex items-center gap-1 bg-emerald-50/50 p-1 rounded-xl border border-emerald-100 overflow-x-auto no-scrollbar snap-x shrink-0">
                    <button 
                        v-for="tf in timeframes" 
                        :key="tf.id"
                        @click="store.timeFrame = tf.id"
                        :class="[
                            'px-3 py-1.5 text-sm font-black uppercase tracking-widest rounded-lg transition-all whitespace-nowrap snap-center',
                            store.timeFrame === tf.id 
                                ? 'bg-emerald-600 text-white shadow-md' 
                                : 'text-emerald-600 hover:bg-white'
                        ]"
                    >
                        {{ tf.label }}
                    </button>
                </div>

                <div class="h-8 w-px bg-gray-200 hidden lg:block"></div>

                <div class="flex items-center gap-2 justify-end">
                    <slot name="actions"></slot>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useBugTrackerStore } from '@/Stores/bugTrackerStore';
import { 
    UserGroupIcon, 
    ShieldCheckIcon, 
    DocumentMagnifyingGlassIcon,
    Squares2X2Icon,
    CpuChipIcon
} from '@heroicons/vue/24/outline';
import { computed } from 'vue';

const props = defineProps({
    projects: { type: Array, default: () => [] },
    modules: { type: Array, default: () => [] }
});
const store = useBugTrackerStore();

const modes = [
    { id: 'internal', label: 'Internal Team', icon: UserGroupIcon },
    { id: 'client', label: 'Client View', icon: ShieldCheckIcon },
    { id: 'audit', label: 'Audit Log', icon: DocumentMagnifyingGlassIcon }
];

const timeframes = [
    { id: 'sprint', label: 'Sprint' },
    { id: '30d', label: 'Last 30D' },
    { id: 'year', label: 'FY25' }
];

const activeModules = computed(() => {
    if (!store.selectedProject || !props.projects?.length) return [];
    const project = props.projects.find(p => p.id == store.selectedProject);
    return project?.modules || [];
});
</script>

<style scoped>
.font-inter { font-family: 'Inter', sans-serif; }
</style>
