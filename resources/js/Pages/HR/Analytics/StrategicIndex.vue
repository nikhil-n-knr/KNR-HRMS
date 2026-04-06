<template>
    <Head title="Strategic Intelligence" />
    <MainLayout>
        <PayrollTabs class="-mt-6 -mx-4 sm:-mx-6 lg:-mx-8 mb-6" />
        
        <div class="px-4 sm:px-6 lg:px-8 py-6 space-y-8">
             <!-- Header & Search -->
             <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                 <div>
                    <h1 class="text-2xl font-bold text-gray-900 bg-clip-text text-transparent bg-gradient-to-r from-violet-600 to-indigo-600">
                        Strategic Compensation Intelligence
                    </h1>
                    <p class="text-sm text-gray-500">Business Intelligence for Workforce Planning</p>
                 </div>
                 
                 <!-- Player Search -->
                 <div class="relative w-full md:w-96 group">
                     <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                         <svg class="h-5 w-5 text-gray-400 group-focus-within:text-indigo-500 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                     </div>
                     <input 
                        type="text" 
                        v-model="searchQuery" 
                        @input="handleSearch"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm transition shadow-sm"
                        placeholder="Deep Search Employee (Player Card)..."
                     >
                     <!-- Dropdown -->
                     <div v-if="searchResults.length > 0" class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md py-1 border border-gray-100 max-h-60 overflow-auto">
                         <button 
                            v-for="emp in searchResults" 
                            :key="emp.id"
                            @click="openPlayerCard(emp)"
                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                         >
                            <span class="font-bold block">{{ emp.first_name }} {{ emp.last_name }}</span>
                            <span class="text-xs text-gray-500">{{ emp.designation }} • {{ emp.employee_code }}</span>
                         </button>
                     </div>
                 </div>
             </div>

             <!-- Grid Layout for Dimensions -->
             <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                 
                 <!-- 1. The Matrix (Cost vs Value) -->
                 <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 relative overflow-hidden">
                     <div class="flex justify-between items-start mb-4">
                         <div>
                             <h3 class="text-lg font-bold text-gray-900">The Matrix (Department Efficiency)</h3>
                             <p class="text-xs text-gray-500">Cost (Y) vs Performance (X)</p>
                         </div>
                         <div class="flex gap-2 text-sm font-bold uppercase tracking-wider">
                             <span class="text-green-600 bg-green-50 px-2 py-1 rounded">High Value</span>
                             <span class="text-red-600 bg-red-50 px-2 py-1 rounded">Budget Bleed</span>
                         </div>
                     </div>
                     <div class="h-80 relative">
                          <BubbleChart :data="matrixData" /> 
                     </div>
                 </div>

                 <!-- 2. The Structure (Salary Inversion) -->
                 <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                     <div class="flex justify-between items-start mb-4">
                         <div>
                             <h3 class="text-lg font-bold text-gray-900">The Structure (Range Analysis)</h3>
                             <p class="text-xs text-gray-500">Salary Ranges & Outliers by Role</p>
                         </div>
                     </div>
                     <div class="h-80 relative">
                         <!-- Using a Custom Candle/Bar implementation for Box Plot approximation -->
                         <StructureChart :data="structure" />
                     </div>
                 </div>

                 <!-- 3. The Vintage (Loyalty vs Market) -->
                 <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                     <h3 class="text-lg font-bold text-gray-900 mb-4">The Vintage (Tenure vs Pay)</h3>
                     <div class="h-80">
                         <ScatterChart 
                            :points="vintage" 
                            title=""
                            xLabel="Tenure (Years)"
                         />
                     </div>
                     <p class="text-xs text-center mt-2 text-gray-500">Dots below the trend line indicate "Loyalty Penalty".</p>
                 </div>

                 <!-- 4. Time Machine (Trends) -->
                 <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                     <h3 class="text-lg font-bold text-gray-900 mb-4">Time Machine (Payroll Velocity)</h3>
                     <div class="h-80">
                         <StackedBarChart :data="timeMachine" />
                     </div>
                 </div>

             </div>
        </div>

        <!-- Player Card Modal -->
        <CareerDnaModal :show="!!selectedEmployeeId" :employee-id="selectedEmployeeId" @close="selectedEmployeeId = null" />

    </MainLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import PayrollTabs from '@/Components/PayrollTabs.vue';
import BubbleChart from '@/Components/Charts/BubbleChart.vue'; // Need to create
import ScatterChart from '@/Components/Charts/ScatterChart.vue';
import StructureChart from '@/Components/Charts/StructureChart.vue'; // Custom
import StackedBarChart from '@/Components/Charts/StackedBarChart.vue'; // Custom
import CareerDnaModal from '@/Components/Analytics/CareerDnaModal.vue';
import { ref, computed } from 'vue';
import axios from 'axios';
import debounce from 'lodash/debounce';

const props = defineProps({
    matrix: Array,
    structure: Array,
    vintage: Array,
    timeMachine: Array
});

const searchQuery = ref('');
const searchResults = ref([]);
const selectedEmployeeId = ref(null);

const handleSearch = debounce(async () => {
    if (searchQuery.value.length < 2) {
        searchResults.value = [];
        return;
    }
    const res = await axios.get(route('hr.analytics.player-search') + '?query=' + searchQuery.value);
    searchResults.value = res.data;
}, 300);

const openPlayerCard = (emp) => {
    selectedEmployeeId.value = emp.id;
    searchQuery.value = '';
    searchResults.value = [];
};

// Transform Matrix Data for Bubble Chart
const matrixData = computed(() => {
    return props.matrix.map(m => ({
        x: m.x,
        y: m.y,
        r: Math.sqrt(m.r) * 3, // Scale radius
        label: m.department
    }));
});

</script>
