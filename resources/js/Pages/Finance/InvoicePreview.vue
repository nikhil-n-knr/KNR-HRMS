<template>
  <div class="max-w-4xl mx-auto py-8">
     <!-- Header -->
     <div class="mb-6 flex justify-between items-center">
        <div>
           <h1 class="text-2xl font-bold text-gray-800">Invoice Draft</h1>
           <p class="text-sm text-gray-500">Project: {{ project.name }} ({{ project.code }})</p>
        </div>
        <div class="text-right">
           <p class="text-sm font-bold text-gray-500">Client</p>
           <p class="text-lg font-semibold text-indigo-600">{{ client.name }}</p>
        </div>
     </div>

     <!-- Invoice Table -->
     <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
         <table class="w-full text-left">
             <thead class="bg-gray-50 border-b border-gray-100">
                 <tr>
                     <th class="p-4 text-xs font-bold text-gray-500 uppercase">Item Description</th>
                     <th class="p-4 text-xs font-bold text-gray-500 uppercase text-right">Hours</th>
                     <th class="p-4 text-xs font-bold text-gray-500 uppercase text-right">Rate</th>
                     <th class="p-4 text-xs font-bold text-gray-500 uppercase text-right">Amount</th>
                 </tr>
             </thead>
             <tbody class="divide-y divide-gray-100">
                 <tr v-for="(item, index) in items" :key="index" class="hover:bg-gray-50/50">
                     <td class="p-4 text-sm font-medium text-gray-700">
                         {{ item.description }}
                         <span class="block text-xs text-gray-400">Task ID: #{{ item.task_id }}</span>
                     </td>
                     <td class="p-4 text-sm text-gray-600 text-right">{{ item.quantity }}h</td>
                     <td class="p-4 text-sm text-gray-600 text-right">{{ formatCurrency(item.rate) }}</td>
                     <td class="p-4 text-sm font-bold text-gray-800 text-right">{{ formatCurrency(item.amount) }}</td>
                 </tr>
             </tbody>
             <tfoot class="bg-gray-50 border-t border-gray-200">
                 <tr>
                     <td colspan="3" class="p-4 text-right text-sm font-bold text-gray-500">Total Due:</td>
                     <td class="p-4 text-right text-xl font-bold text-indigo-700">{{ formatCurrency(total) }}</td>
                 </tr>
             </tfoot>
         </table>
     </div>

     <!-- Actions -->
     <div class="flex justify-end gap-3">
         <button @click="back" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50">
             Cancel
         </button>
         <button @click="generateInvoice" :disabled="processing" class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all flex items-center gap-2">
             <svg v-if="processing" class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
             {{ processing ? 'Finalizing...' : 'Generate Invoice' }}
         </button>
     </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { useToastStore } from '@/stores/toast';

defineOptions({ layout: MainLayout });

const props = defineProps(['project', 'client', 'items', 'total', 'currency']);
const toast = useToastStore();
const processing = ref(false);

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: props.currency }).format(val);
};

const back = () => window.history.back();

const generateInvoice = () => {
    if (!confirm('This will lock all listed tasks and generate an official invoice. Proceed?')) return;
    
    processing.value = true;
    router.post(route('invoices.store', { project: props.project.id }), {}, {
        onSuccess: () => toast.success('Invoice Generated!'),
        onError: (err) => toast.error('Failed to generate invoice.'),
        onFinish: () => processing.value = false
    });
};
</script>
