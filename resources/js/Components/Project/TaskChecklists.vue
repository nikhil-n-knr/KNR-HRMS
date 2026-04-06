<template>
    <div class="h-full flex flex-col p-4">
        <!-- Progress Bar -->
        <div class="mb-4 flex items-center justify-between gap-4">
             <div class="flex-1">
                <div class="flex justify-between text-xs font-bold text-gray-500 mb-1">
                    <span>Progress</span>
                    <span>{{ progress }}%</span>
                </div>
                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500 transition-all duration-500" :style="{ width: progress + '%' }"></div>
                </div>
             </div>
             
             <div class="flex items-center gap-1 shrink-0">
                <button 
                    v-if="items.length === 0"
                    @click="showClone = true"
                    class="px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-bold hover:bg-indigo-100 transition-colors flex items-center gap-1"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" /></svg>
                    Import
                </button>
                <button 
                    @click="$refs.fileInput.click()"
                    class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                    title="Import from CSV/Excel"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                </button>
                <input 
                    type="file" 
                    ref="fileInput" 
                    class="hidden" 
                    accept=".csv,.txt"
                    @change="handleFileUpload"
                >
             </div>
        </div>

        <!-- Clone Selection -->
        <div v-if="showClone" class="mb-6 p-4 bg-indigo-50 border border-indigo-100 rounded-xl space-y-3">
             <div class="flex justify-between items-center">
                 <h4 class="text-xs font-bold text-indigo-700 uppercase">Clone Checklist From</h4>
                 <button @click="showClone = false" class="text-xs text-gray-400 hover:text-gray-600">Cancel</button>
             </div>
             <div>
                 <input 
                    v-model="taskSearch"
                    type="text" 
                    placeholder="Search task to clone from..." 
                    class="w-full text-xs border-indigo-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 mb-2"
                 />
                 <div class="max-h-[150px] overflow-y-auto space-y-1 pr-1 custom-scrollbar">
                     <button 
                        v-for="task in filteredTasks" 
                        :key="task.id"
                        @click="doClone(task.id)"
                        class="w-full text-left p-2 hover:bg-white rounded border border-transparent hover:border-indigo-200 transition-all"
                     >
                         <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">#{{ task.id }}</p>
                         <p class="text-xs font-medium text-gray-700 truncate">{{ task.title }}</p>
                     </button>
                     <div v-if="filteredTasks.length === 0" class="text-center py-4 text-xs text-gray-400 italic">No tasks found</div>
                 </div>
             </div>
        </div>

        <!-- List -->
        <div class="flex-1 overflow-y-auto space-y-2">
            <div v-for="item in items" :key="item.id" class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg group transition-colors">
                <input 
                    type="checkbox" 
                    :checked="item.is_completed" 
                    @change="$emit('toggle', item)"
                    class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 w-5 h-5 cursor-pointer"
                >
                <span 
                    class="flex-1 text-sm text-gray-700 transition-all"
                    :class="{ 'line-through text-gray-400': item.is_completed }"
                >
                    {{ item.content }}
                </span>
                
                <button 
                    @click="$emit('delete', item.id)"
                    class="text-gray-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all px-2"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                </button>
            </div>

            <!-- New Item Input -->
            <form @submit.prevent="add" class="mt-2 flex items-center gap-3 p-2 border-t border-gray-50 focus-within:bg-gray-50/50 transition-all rounded-lg">
                <button 
                    type="submit"
                    :disabled="!newItem.trim()"
                    class="w-6 h-6 flex items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-indigo-600 hover:text-white disabled:opacity-50 transition-all shadow-sm"
                    title="Add Item (Enter)"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                </button>
                <textarea 
                    v-model="newItem"
                    placeholder="Add item(s)... (Shift+Enter for multiple)" 
                    class="flex-1 bg-transparent border-none text-sm focus:ring-0 p-0 placeholder-gray-400 resize-none overflow-hidden"
                    rows="1"
                    @input="e => { e.target.style.height = 'auto'; e.target.style.height = e.target.scrollHeight + 'px' }"
                    @keydown.enter.prevent="add"
                ></textarea>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    items: Array,
    otherTasks: Array
});

const emit = defineEmits(['add', 'toggle', 'delete', 'clone', 'import']);

const newItem = ref('');
const showClone = ref(false);
const taskSearch = ref('');
const fileInput = ref(null);

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        emit('import', file);
    }
    // Reset
    event.target.value = '';
};

const filteredTasks = computed(() => {
    if (!taskSearch.value) return props.otherTasks.slice(0, 10);
    return props.otherTasks.filter(t => 
        t.title.toLowerCase().includes(taskSearch.value.toLowerCase()) || 
        t.id.toString().includes(taskSearch.value)
    ).slice(0, 10);
});

const doClone = (id) => {
     emit('clone', id);
     showClone.value = false;
};

const add = () => {
    const lines = newItem.value.split('\n').filter(l => l.trim());
    if (lines.length === 0) return;
    
    lines.forEach(line => {
        emit('add', line.trim());
    });
    newItem.value = '';
};

const progress = computed(() => {
    if (props.items.length === 0) return 0;
    const completed = props.items.filter(i => i.is_completed).length;
    return Math.round((completed / props.items.length) * 100);
});
</script>
