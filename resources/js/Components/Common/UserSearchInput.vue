<template>
    <div class="relative w-full">
        <!-- Input Field -->
        <div class="relative">
            <input 
                ref="inputRef"
                :value="displayValue"
                @input="handleInput"
                @focus="showResults = true"
                type="text" 
                class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all pl-10"
                :placeholder="placeholder"
                :disabled="disabled"
            >
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                </svg>
            </div>
            
            <!-- Clear Button -->
            <button 
                v-if="modelValue" 
                @click="clearSelection" 
                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 transition-colors"
            >
               &times;
            </button>
        </div>

        <!-- Dropdown Results -->
        <div v-if="showResults && results.length > 0" class="absolute z-50 w-full mt-1 bg-white rounded-lg shadow-xl border border-gray-100 max-h-60 overflow-y-auto">
            <div 
                v-for="user in results" 
                :key="user.id" 
                @click="selectUser(user)" 
                class="px-4 py-3 flex items-center gap-3 hover:bg-indigo-50 cursor-pointer transition-colors border-b border-gray-50 last:border-0"
            >
                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs shrink-0">
                    {{ getInitials(user.name) }}
                </div>
                <div>
                    <div class="font-bold text-gray-800 text-sm">{{ user.name }}</div>
                    <div class="text-xs text-gray-500">{{ user.email }}</div>
                </div>
            </div>
        </div>

        <!-- No Results / Loading -->
        <div v-if="showResults && isLoading" class="absolute z-50 w-full mt-1 bg-white rounded-lg shadow-lg p-4 text-center text-sm text-gray-500">
            Searching...
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';
import { debounce } from 'lodash'; 

const props = defineProps({
    modelValue: [String, Number], // The ID
    placeholder: { type: String, default: 'Search User...' },
    disabled: Boolean,
    initialLabel: String // Optional label if we have ID but haven't fetched name
});

const emit = defineEmits(['update:modelValue', 'selected']);

const inputRef = ref(null);
const displayValue = ref('');
const results = ref([]);
const showResults = ref(false);
const isLoading = ref(false);

// Initialize with a known label if provided? 
// Or fetching logic logic is complex if we only have ID. 
// For now, assume parent might handle initial fetch or just show ID/placeholder.
// Update: Props don't usually work that way for async. 
// Let's settle for: if modelValue is set but no displayValue, we might need to fetch. 
// Simple version: clear if modelValue changes to null.

onMounted(() => {
    if (props.modelValue && props.initialLabel) {
        displayValue.value = props.initialLabel;
    }
});

watch(() => props.modelValue, (newVal) => {
    if (!newVal) {
        displayValue.value = '';
    } else if (props.initialLabel && displayValue.value === '') {
        displayValue.value = props.initialLabel;
    }
});

const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase();
};

const handleInput = (e) => {
    displayValue.value = e.target.value;
    if (displayValue.value.length < 2) {
        results.value = [];
        return;
    }
    fetchUsers(displayValue.value);
};

const fetchUsers = debounce(async (query) => {
    isLoading.value = true;
    try {
        const response = await axios.get(route('admin.users.search'), { params: { query } });
        results.value = response.data;
        showResults.value = true;
    } catch (error) {
        console.error("User search failed", error);
    } finally {
        isLoading.value = false;
    }
}, 300);

const selectUser = (user) => {
    displayValue.value = user.name;
    emit('update:modelValue', user.id);
    emit('selected', user);
    showResults.value = false;
};

const clearSelection = () => {
    displayValue.value = '';
    emit('update:modelValue', null);
    emit('selected', null);
};

// Close on click outside (simple implementation)
// In a real app, use click-outside directive
</script>
