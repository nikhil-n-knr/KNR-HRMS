<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import debounce from 'lodash/debounce';

const props = defineProps({
    filters: Object,
    departments: Array,
    locations: Array,
    placeholder: {
        type: String,
        default: 'Search Employee...'
    }
});

const form = ref({
    search: props.filters?.search || '',
    department_id: props.filters?.department_id || '',
    location_id: props.filters?.location_id || '',
});

// Deep watch for props changes to sync back if Inertia reloads
watch(() => props.filters, (newFilters) => {
    form.value.search = newFilters?.search || '';
    form.value.department_id = newFilters?.department_id || '';
    form.value.location_id = newFilters?.location_id || '';
}, { deep: true });

const emit = defineEmits(['update']);

const updateRouter = debounce(() => {
    // Clean empty values
    const query = {};
    if (form.value.search) query.search = form.value.search;
    if (form.value.department_id) query.department_id = form.value.department_id;
    if (form.value.location_id) query.location_id = form.value.location_id;

    emit('update', query);
}, 300);

watch(form, () => {
    updateRouter();
}, { deep: true });
</script>

<template>
    <div class="flex flex-col xl:flex-row gap-4 items-stretch xl:items-center bg-white/90 backdrop-blur-md p-4 rounded-2xl border border-slate-200 shadow-sm mb-6 transition-all font-outfit">
        <!-- Search Tactical -->
        <div class="relative w-full xl:w-1/3 group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <MagnifyingGlassIcon class="h-4 w-4 text-slate-400 group-focus-within:text-emerald-500 transition-colors" />
            </div>
            <input 
                v-model="form.search"
                type="text" 
                class="block w-full pl-11 pr-4 h-12 border-slate-200 rounded-xl bg-slate-50/50 text-[11px] font-black uppercase tracking-widest text-slate-700 placeholder-slate-300 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all shadow-inner border-0 ring-1 ring-slate-200"
                :placeholder="placeholder"
            />
        </div>

        <!-- Filters Matrix - Using Grid for better control -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:flex xl:flex-1 gap-3 items-center">
            <div v-if="departments" class="relative group">
                <i class="fas fa-sitemap absolute left-4 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 group-focus-within:text-emerald-500 transition-colors"></i>
                <select v-model="form.department_id" class="block w-full pl-11 pr-10 h-12 border-0 ring-1 ring-slate-200 rounded-xl bg-slate-50/50 text-[10px] font-black uppercase tracking-widest text-slate-600 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all appearance-none cursor-pointer shadow-inner">
                    <option value="">ALL_DEPARTMENTS</option>
                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                </select>
                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-[8px] text-slate-300 pointer-events-none"></i>
            </div>

            <div v-if="locations" class="relative group">
                <i class="fas fa-location-dot absolute left-4 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 group-focus-within:text-emerald-500 transition-colors"></i>
                <select v-model="form.location_id" class="block w-full pl-11 pr-10 h-12 border-0 ring-1 ring-slate-200 rounded-xl bg-slate-50/50 text-[10px] font-black uppercase tracking-widest text-slate-600 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all appearance-none cursor-pointer shadow-inner">
                    <option value="">ALL_LOCATIONS</option>
                    <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                </select>
                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-[8px] text-slate-300 pointer-events-none"></i>
            </div>

            <!-- This will occupy remaining space on XL, or 3rd column on LG, or span full width otherwise -->
            <div class="col-span-1 md:col-span-2 lg:col-span-1 xl:flex-1">
                <slot name="extra"></slot>
            </div>
        </div>
    </div>
</template>
