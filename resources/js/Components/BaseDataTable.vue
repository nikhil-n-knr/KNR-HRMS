<template>
    <div class="space-y-4">
        <!-- Header Controls -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="relative w-full sm:w-72">
                 <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input 
                    v-model="searchQuery"
                    type="text" 
                    :placeholder="searchPlaceholder" 
                    class="block w-full pl-10 pr-3 py-2.5 border-none rounded-xl bg-white/50 focus:ring-2 focus:ring-emerald-500/50 text-sm placeholder-gray-400 transition-all shadow-sm"
                >
            </div>
            
            <div class="flex gap-3">
                 <slot name="actions"></slot>
            </div>
        </div>

        <!-- Table Container -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl border border-white/50 shadow-xl overflow-hidden relative min-h-[400px]">
             <!-- Loading Overlay -->
            <div v-if="loading" class="absolute inset-0 bg-white/60 backdrop-blur-sm z-10 flex items-center justify-center">
                 <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-600"></div>
            </div>

            <!-- Mobile Card View -->
            <div class="block md:hidden border-t border-gray-100 bg-white/50">
                <div 
                    v-for="item in data" 
                    :key="item.id || Math.random()" 
                    class="p-4 border-b border-gray-100 space-y-3 relative group"
                    :class="rowClickable ? 'cursor-pointer hover:bg-emerald-50/30' : ''"
                    @click="rowClickable ? $emit('row-click', item) : null"
                >
                    <div v-if="selectable" class="absolute top-4 right-4">
                         <input 
                            type="checkbox" 
                            :checked="isSelected(item)"
                            @click.stop="toggleRow(item)"
                            class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-600 cursor-pointer shadow-sm"
                        >
                    </div>
                    
                    <div v-for="(col, key) in columns" :key="key" class="flex justify-between items-start gap-4 text-[13px]" :class="{'pr-7': selectable}">
                        <span class="text-gray-500 font-bold uppercase tracking-widest text-[10px] w-24 shrink-0 pt-1 leading-none">{{ col.label }}</span>
                        <div class="flex-1 min-w-0 flex flex-col items-end text-right">
                            <!-- Custom Cell Slot -->
                            <slot :name="`cell-${col.key || key}`" :item="item" :value="item[col.key || key]">
                                <span class="text-gray-900 font-medium break-words leading-tight">
                                    {{ (item[col.key || key] !== null && item[col.key || key] !== undefined) ? item[col.key || key] : '--' }}
                                </span>
                            </slot>
                        </div>
                    </div>
                    
                    <div v-if="$slots.rowActions" class="mt-4 pt-3 border-t border-gray-50 flex justify-end">
                        <slot name="rowActions" :item="item"></slot>
                    </div>
                </div>
                
                <!-- Empty State Mobile -->
                <div v-if="data.length === 0 && !loading" class="p-8 text-center bg-white/50">
                    <slot name="empty">
                        <div class="mx-auto h-12 w-12 text-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="mt-2 text-[11px] font-black text-slate-800 tracking-widest uppercase">No records found</h3>
                    </slot>
                </div>
            </div>

            <!-- Desktop View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th v-if="selectable" scope="col" class="px-6 py-4 w-4">
                                <input 
                                    type="checkbox" 
                                    :checked="isAllSelected"
                                    :indeterminate="isIndeterminate"
                                    @change="toggleAll"
                                    class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-600 cursor-pointer"
                                >
                            </th>
                            <th 
                                v-for="(col, key) in columns" 
                                :key="key"
                                scope="col" 
                                class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider"
                                :class="[
                                    col.class || '',
                                    col.align === 'right' ? 'text-right' : (col.align === 'center' ? 'text-center' : 'text-left')
                                ]"
                            >
                                {{ col.label }}
                            </th>
                             <th v-if="$slots.rowActions" scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr 
                            v-for="item in data" 
                            :key="item.id" 
                            class="hover:bg-blue-50/50 transition-colors duration-150 group"
                            :class="rowClickable ? 'cursor-pointer' : ''"
                            @click="rowClickable ? $emit('row-click', item) : null"
                        >
                            <td v-if="selectable" class="px-6 py-4 whitespace-nowrap text-left">
                                <input 
                                    type="checkbox" 
                                    :checked="isSelected(item)"
                                    @click.stop="toggleRow(item)"
                                    class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-600 cursor-pointer"
                                >
                            </td>
                            <td 
                                v-for="(col, key) in columns" 
                                :key="key"
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                :class="[
                                    col.align === 'right' ? 'text-right' : (col.align === 'center' ? 'text-center' : 'text-left')
                                ]"
                            >
                                <!-- Custom Cell Slot -->
                                <slot :name="`cell-${col.key || key}`" :item="item" :value="item[col.key || key]">
                                    {{ (item[col.key || key] !== null && item[col.key || key] !== undefined) ? item[col.key || key] : '--' }}
                                </slot>
                            </td>
                            
                             <td v-if="$slots.rowActions" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <slot name="rowActions" :item="item"></slot>
                            </td>
                        </tr>
                        
                        <!-- Empty State -->
                        <tr v-if="data.length === 0 && !loading">
                            <td :colspan="Object.keys(columns).length + ($slots.rowActions ? 1 : 0) + (selectable ? 1 : 0)" class="px-6 py-12 text-center">
                                <slot name="empty">
                                    <div class="mx-auto h-12 w-12 text-gray-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No records found</h3>
                                    <p class="mt-1 text-sm text-gray-500">Try adjusting your search or add new data.</p>
                                </slot>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="meta.total > 0" class="bg-gray-50/50 px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-500">
                    Showing <span class="font-medium text-gray-900">{{ meta.from || 0 }}</span> to <span class="font-medium text-gray-900">{{ meta.to || 0 }}</span> of <span class="font-medium text-gray-900">{{ meta.total }}</span> results
                </div>

                <div class="flex items-center gap-2">
                    <button 
                        @click="$emit('page-change', meta.current_page - 1)" 
                        :disabled="meta.current_page <= 1"
                        class="disabled:opacity-40 disabled:cursor-not-allowed px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition shadow-sm"
                    >
                        Previous
                    </button>
                    <button 
                        @click="$emit('page-change', meta.current_page + 1)" 
                        :disabled="meta.current_page >= meta.last_page"
                         class="disabled:opacity-40 disabled:cursor-not-allowed px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition shadow-sm"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    columns: { type: Object, required: true },
    data: { type: Array, default: () => [] },
    meta: { type: Object, default: () => ({ current_page: 1, last_page: 1, total: 0, per_page: 10 }) },
    loading: Boolean,
    perPage: { type: [Number, String], default: 15 },
    searchPlaceholder: { type: String, default: 'Search...' },
    rowClickable: Boolean,
    selectable: Boolean,
    modelValue: { type: Array, default: () => [] }, // Selected IDs
    itemKey: { type: String, default: 'id' }
});

const emit = defineEmits(['search', 'page-change', 'row-click', 'limit-change', 'update:modelValue']);

const searchQuery = ref('');

const debouncedSearch = debounce((val) => {
    emit('search', val);
}, 300);

watch(searchQuery, (newVal) => {
    debouncedSearch(newVal);
});

// Selection Logic
const isAllSelected = computed(() => {
    return props.data.length > 0 && props.data.every(item => props.modelValue.includes(item[props.itemKey]));
});

const isIndeterminate = computed(() => {
    const selectedCount = props.data.filter(item => props.modelValue.includes(item[props.itemKey])).length;
    return selectedCount > 0 && selectedCount < props.data.length;
});

const isSelected = (item) => {
    return props.modelValue.includes(item[props.itemKey]);
};

const toggleAll = (e) => {
    if (e.target.checked) {
        // Add all distinct IDs from current page data to selection
        const newIds = props.data.map(i => i[props.itemKey]);
        const unique = [...new Set([...props.modelValue, ...newIds])];
        emit('update:modelValue', unique);
    } else {
        // Unselect items present on current page
        const currentPageIds = props.data.map(i => i[props.itemKey]);
        const newSelection = props.modelValue.filter(id => !currentPageIds.includes(id));
        emit('update:modelValue', newSelection);
    }
};

const toggleRow = (item) => {
    const id = item[props.itemKey];
    const newSelection = [...props.modelValue];
    if (newSelection.includes(id)) {
        const index = newSelection.indexOf(id);
        if (index > -1) newSelection.splice(index, 1);
    } else {
        newSelection.push(id);
    }
    emit('update:modelValue', newSelection);
};
</script>
