<template>
    <div class="relative" ref="container">
        <label class="block text-sm font-medium text-gray-700 mb-1" v-if="label">{{ label }}</label>
        
        <!-- Selected Pills -->
        <div class="flex flex-wrap gap-2 mb-2" v-if="modelValue.length > 0">
            <span v-for="item in modelValue" :key="item" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                {{ item }}
                <button type="button" @click="remove(item)" class="flex-shrink-0 ml-1.5 h-4 w-4 rounded-full inline-flex items-center justify-center text-indigo-400 hover:bg-indigo-200 hover:text-indigo-500 focus:outline-none">
                    <span class="sr-only">Remove</span>
                    <svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8"><path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7" /></svg>
                </button>
            </span>
        </div>

        <!-- Input & Dropdown -->
        <div class="relative">
             <input
                type="text"
                v-model="query"
                @focus="isOpen = true"
                @keydown.enter.prevent="tryAddCustom" 
                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                :placeholder="modelValue.length === 0 ? placeholder : 'Add another...'"
            >
            <!-- Dropdown -->
            <div v-if="isOpen && filteredOptions.length > 0" class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                <div
                    v-for="option in filteredOptions"
                    :key="option"
                    @click="add(option)"
                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-50 text-gray-900"
                >
                    <span class="block truncate font-normal">
                        {{ option }}
                    </span>
                </div>
            </div>
             <!-- Add Custom Option -->
            <div v-if="isOpen && query && filteredOptions.length === 0" @click="tryAddCustom" class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md py-2 px-3 text-sm text-gray-500 cursor-pointer hover:bg-gray-50">
                Add "{{ query }}"
            </div>
        </div>
        
        <!-- Outside Click Handler (Simple) -->
        <div v-if="isOpen" class="fixed inset-0 z-0" @click="isOpen = false"></div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    },
    options: {
        type: Array,
        default: () => []
    },
    label: String,
    placeholder: {
        type: String,
        default: 'Search or add...'
    }
});

const emit = defineEmits(['update:modelValue']);

const query = ref('');
const isOpen = ref(false);

const filteredOptions = computed(() => {
    if (!query.value) return props.options.filter(o => !props.modelValue.includes(o));
    return props.options
        .filter(o => o.toLowerCase().includes(query.value.toLowerCase()))
        .filter(o => !props.modelValue.includes(o));
});

const add = (item) => {
    emit('update:modelValue', [...props.modelValue, item]);
    query.value = '';
    // Keep open?
    isOpen.value = false; 
};

const remove = (item) => {
    emit('update:modelValue', props.modelValue.filter(i => i !== item));
};

const tryAddCustom = () => {
    if (query.value.trim()) {
        const val = query.value.trim();
        if (!props.modelValue.includes(val)) {
            emit('update:modelValue', [...props.modelValue, val]);
        }
        query.value = '';
        isOpen.value = false;
    }
};
</script>
