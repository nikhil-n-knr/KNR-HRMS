<template>
    <div class="bg-white rounded-[3rem] p-12 border border-slate-100 shadow-xl shadow-slate-200/30">
        <div class="flex justify-between items-center mb-10">
            <div>
                <h3 class="text-2xl font-black tracking-tight text-slate-900">Custom Report Builder</h3>
                <p class="text-sm font-black uppercase tracking-widest text-slate-400 mt-1">Operational Audit Engine</p>
            </div>
            <div class="flex gap-2">
                <button @click="generate('pdf')" :disabled="processing" class="flex items-center gap-2 px-6 py-3 bg-slate-900 text-white rounded-xl text-sm font-black uppercase tracking-widest hover:bg-slate-800 transition-all disabled:opacity-50">
                    <DocumentArrowDownIcon class="w-4 h-4" />
                    Export PDF
                </button>
                <button @click="generate('csv')" :disabled="processing" class="flex items-center gap-2 px-6 py-3 bg-slate-100 text-slate-900 rounded-xl text-sm font-black uppercase tracking-widest hover:bg-slate-200 transition-all disabled:opacity-50">
                    <TableCellsIcon class="w-4 h-4" />
                    Export CSV
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <!-- Configuration -->
            <div class="space-y-8">
                <div>
                    <label class="text-base font-black text-slate-400 uppercase tracking-widest mb-4 block">Select Dimensions</label>
                    <div class="flex flex-wrap gap-2">
                        <button 
                            v-for="col in availableColumns" 
                            :key="col"
                            @click="toggleColumn(col)"
                            :class="[selectedColumns.includes(col) ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'bg-slate-50 text-slate-500 hover:bg-slate-100', 'px-4 py-2 rounded-xl text-sm font-black uppercase tracking-widest transition-all']"
                        >
                            {{ col }}
                        </button>
                    </div>
                </div>

                <div class="h-px bg-slate-50"></div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-base font-black text-slate-400 uppercase tracking-widest mb-4 block">Severity Filter</label>
                        <select v-model="filters.severity" multiple class="w-full bg-slate-50 border-none rounded-2xl py-3 px-4 text-xs font-bold focus:ring-2 focus:ring-indigo-500/20">
                            <option value="critical">Critical</option>
                            <option value="high">High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-base font-black text-slate-400 uppercase tracking-widest mb-4 block">Operational Result</label>
                        <p class="text-sm text-slate-400 font-medium italic">Generating an ultra-high fidelity audit of engineering velocity and risk hotspots.</p>
                    </div>
                </div>
            </div>

            <!-- Preview / Summary -->
            <div class="bg-slate-50 rounded-[2.5rem] p-8 flex flex-col items-center justify-center text-center space-y-6">
                <div class="h-20 w-20 bg-white rounded-3xl flex items-center justify-center shadow-2xl shadow-slate-200">
                    <ChartBarIcon class="h-10 w-10 text-indigo-600" />
                </div>
                <div>
                    <h4 class="text-xl font-black tracking-tight text-slate-900">Audit Pulse Summary</h4>
                    <p class="text-xs text-slate-400 font-bold mt-1">Included Columns: {{ selectedColumns.length }}</p>
                </div>
                <div class="w-full max-w-[200px] space-y-2">
                    <div v-for="col in selectedColumns.slice(0, 4)" :key="col" class="flex justify-between items-center bg-white px-4 py-2 rounded-xl border border-slate-100">
                        <span class="text-sm font-black text-slate-400 uppercase tracking-widest">{{ col }}</span>
                        <CheckIcon class="w-3 h-3 text-emerald-500" />
                    </div>
                    <div v-if="selectedColumns.length > 4" class="text-xs font-black text-slate-300 uppercase italic">+ {{ selectedColumns.length - 4 }} more fields</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { DocumentArrowDownIcon, TableCellsIcon, ChartBarIcon, CheckIcon } from '@heroicons/vue/24/solid';
import axios from 'axios';

const props = defineProps({
    projectId: [Number, String]
});

const availableColumns = ['ID', 'Subject', 'Module', 'Stage', 'Severity', 'Priority', 'Reporter', 'Assignee', 'Created'];
const selectedColumns = ref(['ID', 'Subject', 'Severity', 'Priority', 'Stage']);
const processing = ref(false);

const filters = ref({
    severity: [],
    priority: [],
    stage_ids: []
});

const toggleColumn = (col) => {
    if (selectedColumns.value.includes(col)) {
        selectedColumns.value = selectedColumns.value.filter(c => c !== col);
    } else {
        selectedColumns.value.push(col);
    }
};

const generate = async (format) => {
    processing.value = true;
    try {
        // We'll use a direct window.location for file downloads in Laravel
        const queryParams = new URLSearchParams({
            project_id: props.projectId,
            format: format,
            ...filters.value
        });
        
        selectedColumns.value.forEach(col => queryParams.append('columns[]', col));
        
        window.location.href = route('bugs.reports.generate') + '?' + queryParams.toString();
        
        // Minor delay for the loading state to feel real
        setTimeout(() => processing.value = false, 2000);
    } catch (e) {
        console.error("Audit generation failed", e);
        processing.value = false;
    }
};
</script>
