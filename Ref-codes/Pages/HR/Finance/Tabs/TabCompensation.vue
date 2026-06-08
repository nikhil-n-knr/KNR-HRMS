<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

defineProps({ 
    structures: Array,
    deferredPayments: Array, // Passed from Controller
    employees: Array // For picker
});

const showDeferredModal = ref(false);
const form = useForm({
    employee_id: '',
    type: 'Retention Bonus',
    amount: '',
    due_date: '',
    conditions: ''
});

const submitDeferred = () => {
    form.post(route('hr.finance.deferred.store'), {
        onSuccess: () => {
            showDeferredModal.value = false;
            form.reset();
        }
    });
};
</script>
<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-800">Compensation Structures</h2>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded text-sm font-medium hover:bg-indigo-700">
                + New Structure
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
             <div v-for="struct in structures" :key="struct.id" class="bg-white p-4 rounded-lg shadow border border-gray-100">
                 <h3 class="font-bold text-gray-800">{{ struct.name }}</h3>
                 <p class="text-xs text-gray-500 mt-1">{{ struct.description || 'No description' }}</p>
                 <div class="mt-3 flex gap-2">
                     <button class="text-xs text-indigo-600 font-bold hover:underline">Edit Components</button>
                 </div>
             </div>
        </div>
        
        <!-- Deferred Logic Area -->
        <div class="mt-8">
             <div class="flex justify-between items-center mb-4">
                 <div>
                    <h3 class="font-bold text-gray-900">Deferred Components & Retention</h3>
                    <p class="text-sm text-gray-500">Future payment schedules (Sign-on, Retention).</p>
                 </div>
                 <button @click="showDeferredModal = true" class="text-sm text-indigo-600 font-bold border border-indigo-200 px-3 py-1 rounded hover:bg-indigo-50">
                    + Schedule Payment
                 </button>
            </div>
            
            <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="pay in deferredPayments" :key="pay.id">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ pay.employee?.user?.name || '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ pay.type }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-900">{{ pay.amount }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 font-mono">{{ pay.due_date }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-bold rounded-full"
                                    :class="pay.status === 'Paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                                    {{ pay.status }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="!deferredPayments || !deferredPayments.length">
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400">No scheduled future payments.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Modal -->
        <div v-if="showDeferredModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-bold mb-4">Schedule Future Payment</h3>
                <form @submit.prevent="submitDeferred" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Employee</label>
                        <select v-model="form.employee_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Select Employee</option>
                            <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                {{ emp.first_name }} {{ emp.last_name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Type</label>
                        <select v-model="form.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option>Retention Bonus</option>
                            <option>Sign-on Bonus</option>
                            <option>Performance Incentive</option>
                             <option>Variable Pay</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Amount</label>
                        <input type="number" step="0.01" v-model="form.amount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <p class="text-xs text-gray-500 mt-1">One-time payment amount</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Due Date</label>
                        <input type="date" v-model="form.due_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    
                    <div class="flex justify-end gap-2 mt-6">
                        <button type="button" @click="showDeferredModal = false" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm font-medium">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium" :disabled="form.processing">Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
