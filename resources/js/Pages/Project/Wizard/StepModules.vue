<template>
    <div class="space-y-6 h-full flex flex-col">
        <div class="flex justify-between items-end mb-4 border-b border-gray-100 pb-4">
             <div>
                <h2 class="text-xl font-bold text-gray-800">Module Architecture</h2>
                <p class="text-gray-500 text-sm">Define the recursive structure of your project.</p>
             </div>

             <!-- Clone Option -->
             <div class="w-64">
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Clone from Existing</label>
                <select 
                    @change="cloneStructure($event.target.value)" 
                    class="block w-full pl-3 pr-10 py-1.5 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                >
                    <option value="">-- Start Fresh --</option>
                    <option v-for="p in existingProjects" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
             </div>
        </div>

        <div class="flex-1 overflow-y-auto pr-2 space-y-4">
            <!-- Root Add Button -->
            <div v-if="form.modules.length === 0" class="text-center py-12 border-2 border-dashed border-gray-300 rounded-2xl bg-gray-50/50">
                <p class="text-gray-500 mb-4 font-medium">No modules defined yet.</p>
                <button 
                    @click="addModule(form.modules)"
                    class="px-4 py-2 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition-colors font-semibold shadow-sm"
                >
                    + Add Root Module
                </button>
            </div>

            <!-- Recursive Tree -->
            <ul class="space-y-3">
                <li v-for="(module, index) in form.modules" :key="index">
                    <ModuleNode :module="module" :index="index" :parentList="form.modules" />
                </li>
            </ul>
             
             <div v-if="form.modules.length > 0" class="flex justify-center mt-6 pt-4 border-t border-gray-100">
                 <button 
                    @click="addModule(form.modules)"
                    class="text-sm text-indigo-600 hover:text-indigo-800 font-bold flex items-center gap-1 bg-indigo-50 px-3 py-1.5 rounded-lg transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                    </svg>
                    Add Root Module
                </button>
             </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import ModuleNode from './ModuleNode.vue';

const props = defineProps({
    form: Object,
    existingProjects: { type: Array, default: () => [] }
});

const addModule = (list) => {
    list.push({
        name: '',
        description: '',
        children: []
    });
};

const cloneStructure = async (projectId) => {
    if (!projectId) {
        if (confirm('Clear all modules?')) {
            props.form.modules = [];
        }
        return;
    }

    if (props.form.modules.length > 0) {
        if (!confirm('This will replace your current structure. Continue?')) return;
    }

    try {
        const response = await axios.get(route('projects.modules.index', { project: projectId }));
        props.form.modules = response.data; // recursive structure
    } catch (error) {
        console.error("Failed to clone", error);
        alert('Failed to load project structure.');
    }
};
</script>
