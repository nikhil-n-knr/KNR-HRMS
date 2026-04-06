<template>
    <TransitionRoot appear :show="isOpen" as="template">
        <Dialog as="div" @close="closeModal" class="relative z-[100]">
            <TransitionChild
                as="template"
                enter="duration-300 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-200 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-start justify-center p-4 text-center mt-24">
                    <TransitionChild
                        as="template"
                        enter="duration-300 ease-out"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="duration-200 ease-in"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel class="w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white text-left align-middle shadow-2xl transition-all">
                            <Combobox v-model="selectedAction" @update:modelValue="onActionSelected">
                                <div class="relative">
                                    <div class="flex items-center px-4 border-b border-slate-100">
                                        <CommandLineIcon class="h-5 w-5 text-slate-400" aria-hidden="true" />
                                        <ComboboxInput 
                                            class="h-14 w-full border-0 bg-transparent pl-4 pr-4 text-slate-900 placeholder:text-slate-400 focus:ring-0 sm:text-sm font-medium" 
                                            placeholder="Search for bugs, type 'assign', or 'close'..." 
                                            @change="query = $event.target.value"
                                        />
                                        <kbd class="hidden sm:inline-block rounded-md bg-slate-50 border border-slate-200 px-2 py-1 text-sm font-black text-slate-400 uppercase tracking-widest">Esc</kbd>
                                    </div>

                                    <ComboboxOptions 
                                        static 
                                        class="max-h-80 scroll-py-2 overflow-y-auto py-2 text-sm text-slate-800"
                                        v-if="filteredItems.length > 0"
                                    >
                                        <ComboboxOption 
                                            v-for="item in filteredItems" 
                                            :key="item.id" 
                                            :value="item" 
                                            as="template" 
                                            v-slot="{ active }"
                                        >
                                            <li :class="['cursor-pointer select-none px-4 py-3', active ? 'bg-emerald-50 text-emerald-900' : '']">
                                                <div class="flex items-center gap-3">
                                                    <div v-if="item.type === 'bug'" class="bg-indigo-50 text-indigo-600 px-2 py-1 rounded text-sm font-black uppercase tracking-widest">
                                                        #{{ item.data.id }}
                                                    </div>
                                                    <div v-else class="bg-amber-50 text-amber-600 px-2 py-1 rounded text-sm font-black uppercase tracking-widest">
                                                        Action
                                                    </div>
                                                    
                                                    <span class="flex-auto truncate font-medium" :class="active ? 'text-emerald-900' : 'text-slate-700'">
                                                        {{ item.label }}
                                                    </span>
                                                    <span v-if="active" class="ml-3 flex-none text-emerald-500">
                                                        <ArrowRightIcon class="h-4 w-4" />
                                                    </span>
                                                </div>
                                            </li>
                                        </ComboboxOption>
                                    </ComboboxOptions>

                                    <div v-if="query !== '' && filteredItems.length === 0" class="py-14 px-6 text-center text-sm sm:px-14">
                                        <FaceFrownIcon class="mx-auto h-6 w-6 text-slate-400" aria-hidden="true" />
                                        <p class="mt-4 font-semibold text-slate-900">No results found</p>
                                        <p class="mt-2 text-slate-500">We couldn't find anything matching that term. Please try again.</p>
                                    </div>
                                    
                                    <div class="bg-slate-50 px-4 py-3 border-t border-slate-100 flex items-center justify-between text-sm font-medium text-slate-500">
                                        <div class="flex gap-4">
                                            <span><kbd class="font-sans border bg-white px-1 py-0.5 rounded shadow-sm mr-1">↑↓</kbd> to navigate</span>
                                            <span><kbd class="font-sans border bg-white px-1 py-0.5 rounded shadow-sm mr-1">Enter</kbd> to select</span>
                                        </div>
                                        <div class="font-black uppercase tracking-widest text-emerald-600">God Mode</div>
                                    </div>
                                </div>
                            </Combobox>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import {
    TransitionRoot,
    TransitionChild,
    Dialog,
    DialogPanel,
    Combobox,
    ComboboxInput,
    ComboboxOptions,
    ComboboxOption,
} from '@headlessui/vue';
import { CommandLineIcon, ArrowRightIcon, FaceFrownIcon } from '@heroicons/vue/24/outline';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    isOpen: Boolean,
    bugs: Array
});

const emit = defineEmits(['close', 'openBug']);

const query = ref('');
const selectedAction = ref(null);

const closeModal = () => {
    emit('close');
    query.value = '';
};

// Generate searchable items list (merge actions and bugs)
const searchItems = computed(() => {
    let items = [];
    
    // Add built-in actions
    items.push({ id: 'action-create', type: 'action', label: 'Create new ticket' });
    items.push({ id: 'action-export', type: 'action', label: 'Export tickets to PDF' });
    
    // Add bugs
    if (props.bugs) {
        props.bugs.forEach(bug => {
            items.push({
                id: `bug-${bug.id}`,
                type: 'bug',
                label: `Open: ${bug.subject} (${bug.severity})`,
                data: bug
            });
        });
    }
    
    return items;
});

const filteredItems = computed(() => {
    if (query.value === '') {
        return searchItems.value.slice(0, 5); // Show top 5 default
    }
    return searchItems.value.filter((item) => {
        return item.label.toLowerCase().includes(query.value.toLowerCase()) || 
               (item.type === 'bug' && item.data.id.toString().includes(query.value));
    });
});

const onActionSelected = (item) => {
    if (!item) return;
    
    if (item.type === 'bug') {
        emit('openBug', item.data);
    } else if (item.id === 'action-create') {
        // Trigger create via Global Event or parent
        document.dispatchEvent(new CustomEvent('open-bug-create'));
    } else if (item.id === 'action-export') {
        router.visit(route('bugs.export.pdf'), { data: { format: 'PDF' } });
    }
    
    closeModal();
};

const handleKeydown = (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        if (props.isOpen) {
            closeModal();
        } else {
            emit('open-palette'); // Emitted to parent so it changes the prop `isOpen` to true
        }
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>
