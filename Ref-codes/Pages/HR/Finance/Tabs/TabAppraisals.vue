<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    employees: Array // Passed from controller
});

// Transform props to editable state
const gridData = ref([]);

// Initialize data
const initData = () => {
    if (props.employees) {
        gridData.value = props.employees.map(emp => ({
            employee_id: emp.id,
            name: emp.name,
            code: emp.code,
            department: emp.department,
            current_ctc: Number(emp.current_ctc),
            new_ctc: Number(emp.current_ctc),
            increment_percentage: 0,
            effective_date: new Date().toISOString().substr(0, 10), // Today
            is_dirty: false
        }));
    }
};

initData();

// Watch logic to auto-calculate percentage or amount
const updateIncrement = (row, type) => {
    row.is_dirty = true;
    if (type === 'amount') {
        // Changed New CTC -> Calc %
        if (row.current_ctc > 0) {
            row.increment_percentage = ((row.new_ctc - row.current_ctc) / row.current_ctc) * 100;
            row.increment_percentage = Math.round(row.increment_percentage * 100) / 100;
        }
    } else {
        // Changed % -> Calc New CTC
        row.new_ctc = row.current_ctc + (row.current_ctc * row.increment_percentage / 100);
        row.new_ctc = Math.round(row.new_ctc); 
    }
};

const hasChanges = computed(() => gridData.value.some(r => r.is_dirty));

const form = useForm({
    revisions: []
});

const submitAppraisals = () => {
    // Filter only changed rows
    const revisions = gridData.value
        .filter(r => r.is_dirty && r.new_ctc !== r.current_ctc)
        .map(r => ({
            employee_id: r.employee_id,
            new_ctc: r.new_ctc,
            effective_date: r.effective_date
        }));
    
    if (revisions.length === 0) {
        alert('No changes to save.');
        return;
    }

    form.revisions = revisions;
    form.post(route('finance.appraisals.store'), {
        onSuccess: () => {
            // Reset dirty flags
            gridData.value.forEach(r => r.is_dirty = false);
            alert('Revisions processed successfully!');
        }
    });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(amount);
};
</script>
<template>
    <div class="space-y-6 h-full flex flex-col">
        <div class="flex justify-between items-center">
            <div>
                 <h2 class="text-lg font-bold text-gray-800">Bulk Appraisals Grid</h2>
                 <p class="text-sm text-gray-500">Edit 'New CTC' or '% Hike' to simulate revisions.</p>
            </div>
            
            <div class="flex items-center space-x-4">
                 <div class="text-right mr-4">
                     <span class="block text-xs text-gray-400 uppercase">Updates</span>
                     <span class="block font-bold text-indigo-600">{{ gridData.filter(r => r.is_dirty).length }} Pending</span>
                 </div>
                 <button @click="submitAppraisals" :disabled="form.processing || !hasChanges" 
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg shadow-md font-bold hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition transform hover:-translate-y-0.5">
                    {{ form.processing ? 'Processing...' : 'Process Revisions' }}
                 </button>
            </div>
        </div>

        <div class="flex-1 bg-white rounded-lg shadow border border-gray-200 overflow-hidden flex flex-col">
            <div class="overflow-auto flex-1">
                <table class="min-w-full divide-y divide-gray-200 relative">
                    <thead class="bg-gray-50 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Department</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Current CTC</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-indigo-600 uppercase tracking-wider w-32">Hike %</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-green-600 uppercase tracking-wider w-40">New CTC</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-40">Effective Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="row in gridData" :key="row.employee_id" class="hover:bg-gray-50 transition-colors" :class="{'bg-indigo-50': row.is_dirty}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ row.name }}</div>
                                <div class="text-xs text-gray-500">{{ row.code }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ row.department }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-600 font-medium">
                                {{ formatCurrency(row.current_ctc) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end">
                                    <input type="number" step="0.5" 
                                        v-model="row.increment_percentage"
                                        @input="updateIncrement(row, 'percent')"
                                        class="w-20 text-right text-sm border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 focus:bg-white transition"
                                    >
                                    <span class="ml-1 text-xs text-gray-400">%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                 <input type="number" step="1000" 
                                        v-model="row.new_ctc"
                                        @input="updateIncrement(row, 'amount')"
                                        class="w-32 text-right text-sm font-bold text-green-700 border-gray-300 rounded shadow-sm focus:ring-green-500 focus:border-green-500 bg-gray-50 focus:bg-white transition"
                                    >
                            </td>
                             <td class="px-6 py-4 whitespace-nowrap text-right">
                                 <input type="date" 
                                        v-model="row.effective_date"
                                        @change="row.is_dirty = true"
                                        class="text-sm border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 focus:bg-white transition"
                                    >
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-200 bg-gray-50 text-xs text-gray-500 flex justify-between">
                <span>Total Employees: {{ gridData.length }}</span>
                <span>Avg Hike: {{ (gridData.reduce((acc, r) => acc + Number(r.increment_percentage), 0) / (gridData.length || 1)).toFixed(1) }}%</span>
            </div>
        </div>
    </div>
</template>
