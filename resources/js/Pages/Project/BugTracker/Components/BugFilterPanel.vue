<template>
    <div v-if="show" class="fixed inset-0 overflow-hidden z-[110]">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>
        <div class="fixed inset-y-0 right-0 max-w-sm w-full bg-white shadow-2xl flex flex-col animate-in slide-in-from-right-full duration-300">
            
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">Deep Filters</h2>
                    <p class="text-xs text-slate-500 font-medium">Refine your ticket matrix</p>
                </div>
                <button @click="$emit('close')" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition-colors">
                    <XMarkIcon class="w-5 h-5" />
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6 space-y-8">
                
                <!-- Stages Filter -->
                <div>
                    <InputLabel value="Stages" class="text-sm font-black uppercase tracking-widest text-slate-500 mb-3" />
                    <div class="space-y-2">
                        <label v-for="(name, id) in lookup.stages" :key="'stage_'+id" class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" :value="String(id)" v-model="localFilters.stages" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 group-hover:border-indigo-400 transition-colors cursor-pointer" />
                            <span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">{{ name }}</span>
                        </label>
                    </div>
                </div>

                <!-- Severities Filter -->
                <div>
                    <InputLabel value="Severities" class="text-sm font-black uppercase tracking-widest text-slate-500 mb-3" />
                    <div class="space-y-2">
                        <label v-for="(name, key) in lookup.severities" :key="'sev_'+key" class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" :value="key" v-model="localFilters.severity" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 group-hover:border-indigo-400 transition-colors cursor-pointer" />
                            <span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">{{ name }}</span>
                        </label>
                    </div>
                </div>
                
                <!-- Priorities Filter -->
                <div>
                    <InputLabel value="Priorities" class="text-sm font-black uppercase tracking-widest text-slate-500 mb-3" />
                    <div class="space-y-2">
                        <label v-for="(name, key) in lookup.priorities" :key="'pri_'+key" class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" :value="key" v-model="localFilters.priorities" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 group-hover:border-indigo-400 transition-colors cursor-pointer" />
                            <span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">{{ name }}</span>
                        </label>
                    </div>
                </div>

                <!-- Assignees Filter -->
                <div>
                    <InputLabel value="Assignees" class="text-sm font-black uppercase tracking-widest text-slate-500 mb-3" />
                    <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/50 hover:bg-white hover:border-indigo-200 transition-all focus-within:bg-white focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500">
                        <select multiple v-model="localFilters.assignee_ids" class="block w-full border-0 bg-transparent text-sm focus:ring-0 p-0 h-40">
                            <option v-for="(name, id) in lookup.users" :key="'usr_'+id" :value="String(id)" class="py-1 px-2 mb-1 rounded-md text-slate-700 font-medium hover:bg-indigo-50 checked:bg-indigo-100">{{ name }}</option>
                        </select>
                        <p class="text-sm text-slate-400 mt-2 font-bold uppercase tracking-widest px-2">Ctrl/Cmd + Click for multiple</p>
                    </div>
                </div>

                <!-- Reporters Filter -->
                <div>
                    <InputLabel value="Reporters" class="text-sm font-black uppercase tracking-widest text-slate-500 mb-3" />
                    <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/50 hover:bg-white hover:border-indigo-200 transition-all focus-within:bg-white focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500">
                        <select multiple v-model="localFilters.reporter_ids" class="block w-full border-0 bg-transparent text-sm focus:ring-0 p-0 h-40">
                             <option v-for="(name, id) in lookup.users" :key="'rep_'+id" :value="String(id)" class="py-1 px-2 mb-1 rounded-md text-slate-700 font-medium hover:bg-indigo-50 checked:bg-indigo-100">{{ name }}</option>
                        </select>
                        <p class="text-sm text-slate-400 mt-2 font-bold uppercase tracking-widest px-2">Ctrl/Cmd + Click for multiple</p>
                    </div>
                </div>

            </div>

            <div class="p-6 border-t border-slate-100 bg-slate-50 flex gap-3">
                <button @click="clearFilters" class="flex-1 px-4 py-3 text-sm font-black uppercase tracking-widest text-slate-500 bg-white border border-slate-200 hover:bg-slate-100 rounded-xl transition-all shadow-sm">
                    Reset
                </button>
                <button @click="applyFilters" class="flex-1 px-4 py-3 text-sm font-black uppercase tracking-widest text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl transition-all shadow-lg shadow-indigo-200">
                    Apply Filters
                </button>
            </div>
            
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import { XMarkIcon } from '@heroicons/vue/24/outline'; // Need to ensure it's imported if not global

const props = defineProps({
    show: Boolean,
    filters: Object,
    lookup: Object
});

const emit = defineEmits(['close', 'apply']);

const localFilters = ref({
    stages: [],
    severity: [],
    priorities: [],
    assignee_ids: [],
    reporter_ids: []
});

const syncLocalFilters = () => {
    // Convert current filters into arrays to bind to checkboxes/multiselect
    localFilters.value = {
        stages: Array.isArray(props.filters.stages) ? [...props.filters.stages] : (props.filters.stages ? [props.filters.stages] : []),
        severity: Array.isArray(props.filters.severity) ? [...props.filters.severity] : (props.filters.severity ? [props.filters.severity] : []),
        priorities: Array.isArray(props.filters.priorities) ? [...props.filters.priorities] : (props.filters.priorities ? [props.filters.priorities] : (props.filters.priority ? [props.filters.priority] : [])),
        assignee_ids: Array.isArray(props.filters.assignee_ids) ? [...props.filters.assignee_ids] : (props.filters.assignee_ids ? [props.filters.assignee_ids] : []),
        reporter_ids: Array.isArray(props.filters.reporter_ids) ? [...props.filters.reporter_ids] : (props.filters.reporter_ids ? [props.filters.reporter_ids] : [])
    };
};

watch(() => props.show, (newVal) => {
    if (newVal) {
        syncLocalFilters();
    }
});

const clearFilters = () => {
    localFilters.value = {
        stages: [],
        severity: [],
        priorities: [],
        assignee_ids: [],
        reporter_ids: []
    };
    applyFilters();
};

const applyFilters = () => {
    emit('apply', { ...localFilters.value });
};
</script>
