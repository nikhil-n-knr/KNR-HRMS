<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import MainLayout from '@/Layouts/MainLayout.vue';
import { CalendarIcon, MapPinIcon } from '@heroicons/vue/24/outline';

const holidaysByMonth = ref({});
const year = ref(new Date().getFullYear());
const availableYears = ref([new Date().getFullYear()]);
const loading = ref(false);
const counts = ref({ total: 0, fixed: 0, restricted: 0 });

const fetchData = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/attendance/holidays-list', {
            params: { year: year.value },
            headers: { 'Accept': 'application/json' }
        });
        holidaysByMonth.value = response.data.holidays; 
        if(response.data.years) availableYears.value = response.data.years;
        // Ensure counts exist or default
        counts.value = response.data.counts || { total: 0, fixed: 0, restricted: 0 };
    } catch (e) {
        console.error("Failed to load holidays");
    } finally {
        loading.value = false;
    }
};

const exportUrl = computed(() => `/attendance/holidays-list/export?year=${year.value}`);

const formatDate = (date) => new Date(date).toLocaleDateString(undefined, { weekday: 'short', day: 'numeric' });

const isPast = (date) => new Date(date) < new Date();

onMounted(() => {
    fetchData();
});
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold">Holiday Calendar {{ year }}</h2>
                    <p class="text-indigo-100 mt-1">Plan your vacations and time off.</p>
                </div>
                <div class="flex gap-3">
                     <select v-model="year" @change="fetchData" class="text-sm text-gray-800 border-none rounded-lg shadow-lg py-2 px-4 cursor-pointer focus:ring-2 focus:ring-white/50">
                          <option v-for="y in availableYears" :key="y" :value="y">{{ y }}</option>
                     </select>
                     <a :href="exportUrl" target="_blank" class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg backdrop-blur-sm transition flex items-center gap-2 font-medium border border-white/10">
                        Export CSV
                     </a>
                </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded-xl shadow-sm border border-indigo-100">
                 <p class="text-xs font-bold text-indigo-500 uppercase tracking-wider">Total</p>
                 <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ counts.total }}</h3>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-blue-100">
                 <p class="text-xs font-bold text-blue-500 uppercase tracking-wider">Fixed</p>
                 <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ counts.fixed }}</h3>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-purple-100">
                 <p class="text-xs font-bold text-purple-500 uppercase tracking-wider">Floating</p>
                 <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ counts.restricted }}</h3>
            </div>
        </div>
            </div>
        </div>

        <!-- List of Months -->
        <div v-if="loading" class="text-center py-10 text-gray-500">Loading calendar...</div>
        
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="(holidays, month) in holidaysByMonth" :key="month" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-100">
                    <h3 class="font-bold text-gray-800">{{ month }}</h3>
                </div>
                <div class="divide-y divide-gray-50">
                    <div v-for="holiday in holidays" :key="holiday.id" 
                        class="px-4 py-3 flex items-start justify-between hover:bg-gray-50 transition-colors"
                        :class="{ 'opacity-50 grayscale': isPast(holiday.date) }"
                    >
                        <div class="flex items-start gap-3">
                            <div class="w-12 text-center bg-indigo-50 text-indigo-700 rounded-lg p-1">
                                <div class="text-sm uppercase font-bold tracking-wider">{{ new Date(holiday.date).toLocaleDateString(undefined, { weekday: 'short' }) }}</div>
                                <div class="text-xl font-bold leading-none">{{ new Date(holiday.date).getDate() }}</div>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">{{ holiday.name }}</p>
                                <span v-if="holiday.type === 'Restricted'" class="inline-flex items-center px-1.5 py-0.5 rounded text-sm font-medium bg-purple-100 text-purple-800 mt-1">
                                    Restricted (Optional)
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div v-if="!loading && Object.keys(holidaysByMonth).length === 0" class="text-center py-12 bg-white rounded-lg border border-dashed border-gray-300">
            <p class="text-gray-500">No holidays found for this year.</p>
        </div>
    </div>
</template>

