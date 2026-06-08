<template>
    <div class="bg-white rounded-[3rem] p-12 border border-slate-100 shadow-xl shadow-slate-200/30 flex flex-col">
        <div class="flex justify-between items-center mb-10">
            <div>
                <h3 class="text-xl font-black tracking-tight text-slate-900">Module Risk Heatmap</h3>
                <p class="text-sm font-black uppercase tracking-widest text-slate-400 mt-1">Severity Distribution Matrix</p>
            </div>
            <div class="flex gap-2">
                <div class="flex items-center gap-1.5 grayscale opacity-50">
                    <span class="w-2 h-2 rounded-full bg-slate-100"></span>
                    <span class="w-2 h-2 rounded-full bg-rose-200"></span>
                    <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                    <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                </div>
            </div>
        </div>

        <div v-if="loading" class="flex-1 flex items-center justify-center py-20">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-slate-900"></div>
        </div>

        <div v-else-if="!heatmapData || !heatmapData.modules?.length" class="flex-1 flex flex-col items-center justify-center py-20 opacity-30">
            <div class="text-xs font-black uppercase tracking-widest">No heatmap data available</div>
        </div>

        <div v-else class="overflow-x-auto custom-scrollbar">
            <table class="w-full border-separate border-spacing-2">
                <thead>
                    <tr>
                        <th class="p-2"></th>
                        <th v-for="sev in heatmapData.severities" :key="sev" 
                            class="p-2 text-sm font-black uppercase tracking-widest text-slate-400 text-center w-24">
                            {{ sev }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="mod in heatmapData.modules" :key="mod.id">
                        <td class="p-2 text-sm font-black uppercase tracking-widest text-slate-600 truncate max-w-[120px]" :title="mod.name">
                            {{ mod.name }}
                        </td>
                        <td v-for="sev in heatmapData.severities" :key="sev" 
                            :class="[getCellClass(mod.id, sev), 'p-4 rounded-xl text-center text-xs font-black transition-all hover:scale-105 cursor-help']"
                            :title="`${getValue(mod.id, sev)} ${sev} bugs in ${mod.name}`">
                            {{ getValue(mod.id, sev) || '·' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    projectId: [Number, String]
});

const heatmapData = ref(null);
const loading = ref(true);

const fetchHeatmap = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('bugs.analytics.heatmap'), {
            params: { project_id: props.projectId }
        });
        heatmapData.value = response.data;
    } catch (e) {
        console.error("Failed to fetch heatmap", e);
    } finally {
        loading.value = false;
    }
};

const getValue = (moduleId, severity) => {
    const entry = heatmapData.value.matrix.find(m => m.module_id == moduleId && m.severity === severity);
    return entry ? entry.total : 0;
};

const getCellClass = (moduleId, severity) => {
    const val = getValue(moduleId, severity);
    if (val === 0) return 'bg-slate-50 text-slate-200';
    
    if (severity === 'critical') {
        if (val > 5) return 'bg-rose-600 text-white shadow-lg shadow-rose-200';
        if (val > 2) return 'bg-rose-400 text-white';
        return 'bg-rose-100 text-rose-700';
    }
    
    if (severity === 'high') {
        if (val > 5) return 'bg-orange-500 text-white shadow-lg shadow-orange-200';
        return 'bg-orange-100 text-orange-700';
    }

    if (severity === 'medium') {
        return 'bg-amber-100 text-amber-700';
    }

    return 'bg-emerald-100 text-emerald-700';
};

onMounted(fetchHeatmap);
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { height: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>
