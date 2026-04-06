<template>
    <Teleport to="body">
        <Transition 
            enter-active-class="ease-out duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-[60] overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
                <!-- Backdrop -->
                <div class="fixed inset-0 transform transition-all" @click="close">
                    <div class="absolute inset-0 bg-gray-900/75 backdrop-blur-md"></div>
                </div>

                <!-- Modal Panel -->
                <Transition 
                    enter-active-class="ease-out duration-300"
                    enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                >
                    <div class="relative bg-white rounded-3xl shadow-2xl transform transition-all w-full max-w-[90rem] h-[90vh] flex flex-col overflow-hidden border border-white/20">
                        <!-- Header -->
                        <div class="px-8 py-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex justify-between items-center">
                            <div>
                                <h2 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-700 to-purple-600">
                                    Bulk Module Import
                                </h2>
                                <p class="text-sm text-gray-500 mt-1">Rapidly build your project hierarchy using a simple indented list.</p>
                            </div>
                            <button @click="close" class="text-gray-400 hover:text-gray-600 transition-colors p-2 hover:bg-gray-100 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="flex flex-1 overflow-hidden">
                            <!-- Left Pane: Input -->
                            <div class="w-1/2 p-8 flex flex-col border-r border-gray-100 bg-white">
                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    Hierarchy Definition
                                </label>
                                <div class="flex-1 relative rounded-2xl border border-gray-200 shadow-inner bg-gray-50 focus-within:ring-4 focus-within:ring-indigo-500/10 focus-within:border-indigo-500 transition-all overflow-hidden group">
                                    <textarea 
                                        v-model="rawText"
                                        @input="parseText"
                                        @keydown.tab.prevent="insertTab"
                                        class="w-full h-full p-6 bg-transparent border-none focus:ring-0 resize-none font-mono text-sm leading-7 text-gray-700 placeholder-gray-400"
                                        placeholder="Module A&#10;  Sub Module 1&#10;    Feature X&#10;  Sub Module 2&#10;Module B"
                                        spellcheck="false"
                                    ></textarea>
                                    
                                    <!-- Keyboard Hint -->
                                    <div class="absolute bottom-4 right-4 text-xs text-gray-400 bg-white/80 px-2 py-1 rounded-md border border-gray-100 shadow-sm pointer-events-none">
                                        Use <strong>Tab</strong> to indent
                                    </div>
                                </div>
                            </div>

                            <!-- Right Pane: Preview -->
                            <div class="w-1/2 bg-slate-50/50 flex flex-col relative">
                                <div class="px-8 py-4 border-b border-gray-200/60 bg-white/40 backdrop-blur-sm sticky top-0 flex justify-between items-center z-10">
                                    <h3 class="font-bold text-gray-700 flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
                                        </svg>
                                        Live Preview
                                    </h3>
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            {{ parsedCount }} Nodes Recognized
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                                    <TransitionGroup 
                                        tag="div" 
                                        class="space-y-4"
                                        enter-active-class="transition-all duration-300 ease-out"
                                        enter-from-class="opacity-0 translate-x-4"
                                        enter-to-class="opacity-100 translate-x-0"
                                    >
                                        <div v-if="parsedTree.length === 0" key="empty" class="h-full flex flex-col items-center justify-center text-gray-400 opacity-60 mt-20">
                                            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                </svg>
                                            </div>
                                            <p class="font-medium text-lg">Start typing to build your tree</p>
                                        </div>

                                        <div v-else class="space-y-1">
                                            <PreviewNode v-for="(node, idx) in parsedTree" :key="idx" :node="node" />
                                        </div>
                                    </TransitionGroup>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-8 py-5 bg-white border-t border-gray-100 flex justify-end gap-4 items-center">
                            <button 
                                @click="close" 
                                class="px-6 py-2.5 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-xl font-bold transition-all"
                            >
                                Cancel
                            </button>
                            <button 
                                @click="submit" 
                                :disabled="parsedTree.length === 0 || processing"
                                class="px-8 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:scale-[1.02] disabled:opacity-50 disabled:shadow-none disabled:scale-100 disabled:cursor-not-allowed transition-all flex items-center gap-2"
                            >
                                <svg v-if="processing" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span v-else>Import {{ parsedCount }} Modules</span>
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

// Simple recursive component for preview
import { h } from 'vue';
const PreviewNode = (props) => {
    return h('div', { class: 'relative pl-6 ml-2 border-l-2 border-indigo-100' }, [
        // Connector Dot
        h('div', { class: 'absolute -left-[5px] top-3 w-2.5 h-2.5 rounded-full bg-indigo-400 ring-2 ring-white' }),
        
        h('div', { class: 'flex items-center gap-2 py-2 group' }, [
            h('span', { class: 'text-sm font-semibold text-gray-700 group-hover:text-indigo-700 transition-colors truncate' }, props.node.name)
        ]),
        props.node.children && props.node.children.length > 0 
            ? props.node.children.map((child, i) => h(PreviewNode, { key: i, node: child }))
            : null
    ]);
};

const props = defineProps({
    show: Boolean,
    project: Object
});

const emit = defineEmits(['close', 'success']);

const rawText = ref('');
const parsedTree = ref([]);
const processing = ref(false);

const parsedCount = computed(() => {
    let count = 0;
    const traverse = (nodes) => {
        nodes.forEach(n => {
            count++;
            if (n.children) traverse(n.children);
        });
    };
    traverse(parsedTree.value);
    return count;
});

const insertTab = (e) => {
    const start = e.target.selectionStart;
    const end = e.target.selectionEnd;
    rawText.value = rawText.value.substring(0, start) + "\t" + rawText.value.substring(end);
    // Move caret
    setTimeout(() => {
        e.target.selectionStart = e.target.selectionEnd = start + 1;
    }, 0);
    parseText();
};

const parseText = () => {
    const lines = rawText.value.split('\n');
    const root = [];
    const stack = [{ level: -1, children: root }];

    lines.forEach(line => {
        if (!line.trim()) return;

        // Determine indent level
        // Tab = 4 spaces for calculation simplification, usually 1 tab is enough level jump
        // We calculate 'leading whitespace' count
        // Strategy: normalization. 1 Tab = 1 Level. 2 Spaces = 1 Level. 4 Spaces = 1 Level.
        // Let's deduce unit from the first indented line? 
        // Simpler: Count leading spaces/tabs. 
        
        const indentMatch = line.match(/^(\s*)/);
        const indentStr = indentMatch ? indentMatch[0] : '';
        const name = line.trim();
        
        // Calculate Level based on common patterns
        // If tabs used, visual length 1. If spaces, 2 or 4.
        // We strictly use the "stack" logical hierarchy.
        // A deeper indent than previous line = child. Same = Sibling. Less = Go up stack.
        
        // Let's use a flexible logic:
        // Current indent > Previous indent -> Child
        // Current indent == Previous indent -> Sibling
        // Current indent < Previous indent -> Find parent with indent < Current
        
        // Actually, we need to normalize to integers to pop stack accurately
        // Heuristic: tab counts as 1. space counts as 0.25? No that breaks.
        // Let's count characters, but normalize by the first indentation we see?
        
        // Robust way:
        let level = 0;
        // Count tabs
        const tabs = (indentStr.match(/\t/g) || []).length;
        // Count spaces
        const spaces = (indentStr.match(/ /g) || []).length;
        
        // Assuming user is consistent. 1 Tab = 1 Level. 2 Spaces = 1 Level (common).
        level = tabs + Math.floor(spaces / 2); 

        const node = { name, children: [] };

        // pop stack until we find the parent with level < current level
        while (stack.length > 1 && stack[stack.length - 1].level >= level) {
            stack.pop();
        }

        const parent = stack[stack.length - 1];
        parent.children.push(node);
        stack.push({ level, children: node.children });
    });

    parsedTree.value = root;
};

const close = () => {
    emit('close');
    rawText.value = '';
    parsedTree.value = [];
};

const submit = () => {
    if (parsedTree.value.length === 0) return;
    
    processing.value = true;
    const form = useForm({
        modules: parsedTree.value
    });

    form.post(route('projects.modules.bulk', props.project.id), {
        onSuccess: () => {
             processing.value = false;
             emit('success');
             close();
        },
        onError: () => {
            processing.value = false;
             // handle error
        }
    });
};

watch(() => props.show, (newVal) => {
    if (!newVal) {
        // rawText.value = ''; // Optional: Clear on close
    }
});
</script>

<style scoped>
textarea:focus {
    outline: none;
    box-shadow: none;
}
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 4px;
}
</style>
