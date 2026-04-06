<template>
  <div class="space-y-6">
      
      <!-- Batch Selector -->
      <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm flex justify-between items-center">
        <div class="w-1/3">
             <label class="block text-sm font-medium text-gray-700 mb-1">Select Payroll Batch</label>
             <select v-model="selectedPayroll" @change="runDiagnostics" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                  <option :value="null">-- Select Month --</option>
                  <option v-for="batch in payrolls" :key="batch.id" :value="batch.id">
                      {{ batch.month_name }} {{ batch.year }}
                  </option>
             </select>
        </div>
        <div v-if="selectedPayroll">
            <span v-if="loading" class="text-sm text-gray-500 animate-pulse">Running Diagnostics...</span>
             <span v-else class="text-sm font-bold px-3 py-1 rounded-full" :class="hasErrors ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'">
                {{ hasErrors ? 'Attention Required' : 'Ready to File' }}
            </span>
        </div>
      </div>

      <div v-if="selectedPayroll" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          
          <!-- Left Panel: Health Check (Validation) -->
          <div class="lg:col-span-1 space-y-4">
              <h3 class="font-bold text-gray-800 border-b pb-2">Health Check</h3>
              
              <!-- Check Items -->
              <ValidationItem 
                title="UAN Mapping" 
                :count="validation.uan_missing?.length" 
                type="critical"
                :items="validation.uan_missing"
              />
               <ValidationItem 
                title="NCP Days Mismatch" 
                :count="validation.ncp_mismatch?.length" 
                type="critical"
                :items="validation.ncp_mismatch"
              />
               <ValidationItem 
                title="Negative Wages" 
                :count="validation.wage_negative?.length" 
                type="critical"
                :items="validation.wage_negative"
              />
               <ValidationItem 
                title="KYC Compliance" 
                :count="validation.kyc_missing?.length" 
                type="warning"
                :items="validation.kyc_missing"
              />
          </div>

          <!-- Right Panel: Preview & Generate -->
          <div class="lg:col-span-2 space-y-6">
               <h3 class="font-bold text-gray-800 border-b pb-2">ECR Data Preview</h3>
               
               <!-- Summary Cards -->
               <div class="grid grid-cols-3 gap-4">
                   <div class="bg-gray-50 p-4 rounded-lg">
                       <div class="text-xs text-gray-500 uppercase">Total Wages</div>
                       <div class="text-xl font-bold text-gray-800">{{ formatCurrency(preview.totals?.gross || 0) }}</div>
                   </div>
                    <div class="bg-indigo-50 p-4 rounded-lg">
                       <div class="text-xs text-indigo-500 uppercase">EPF Liability</div>
                       <div class="text-xl font-bold text-indigo-800">{{ formatCurrency(preview.totals?.epf || 0) }}</div>
                   </div>
                    <div class="bg-pink-50 p-4 rounded-lg">
                       <div class="text-xs text-pink-500 uppercase">EPS (Pension)</div>
                       <div class="text-xl font-bold text-pink-800">{{ formatCurrency(preview.totals?.eps || 0) }}</div>
                   </div>
               </div>

               <!-- Spot Check Table -->
               <div class="bg-white border rounded-lg overflow-hidden">
                   <table class="min-w-full divide-y divide-gray-200 text-xs text-left">
                       <thead class="bg-gray-50 font-medium text-gray-500">
                           <tr>
                               <th class="px-4 py-2">UAN</th>
                               <th class="px-4 py-2">Name</th>
                               <th class="px-4 py-2 text-right">EPF Wage</th>
                               <th class="px-4 py-2 text-right">EPS Wage</th>
                               <th class="px-4 py-2 text-right">NCP</th>
                           </tr>
                       </thead>
                       <tbody class="divide-y divide-gray-200">
                           <tr v-for="row in preview.sample" :key="row.uan">
                               <td class="px-4 py-2 font-mono">{{ row.uan }}</td>
                               <td class="px-4 py-2">{{ row.name }}</td>
                               <td class="px-4 py-2 text-right">{{ row.epf_wages }}</td>
                               <td class="px-4 py-2 text-right">{{ row.eps_wages }}</td>
                               <td class="px-4 py-2 text-right">{{ row.ncp_days }}</td>
                           </tr>
                       </tbody>
                   </table>
                   <div class="bg-gray-50 px-4 py-2 text-xs text-gray-500 text-center">
                       Showing first 5 of {{ preview.totals?.employees }} records
                   </div>
               </div>

               <!-- Action Dock -->
               <div class="flex justify-end gap-3 pt-4 border-t">
                   <button class="text-gray-600 text-sm hover:underline">Download ESI Return</button>
                   <button 
                        @click="generateECR" 
                        :disabled="hasCriticalErrors"
                        class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed shadow transition transform hover:scale-105"
                   >
                        {{ hasCriticalErrors ? 'Fix Errors to Generate' : 'Generate ECR Text File' }}
                   </button>
               </div>
          </div>
      </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import ValidationItem from './Partials/ValidationItem.vue';

const payrolls = ref([]);
const selectedPayroll = ref(null);
const loading = ref(false);
const validation = ref({});
const preview = ref({});

onMounted(async () => {
    try {
        const res = await axios.get(route('hr.compliance.payrolls')); 
        payrolls.value = res.data;
    } catch (e) {}
});

const runDiagnostics = async () => {
    if (!selectedPayroll.value) return;
    loading.value = true;
    try {
        const [vRes, pRes] = await Promise.all([
             axios.post(route('hr.compliance.ecr.validate'), { payroll_id: selectedPayroll.value }),
             axios.post(route('hr.compliance.ecr.preview'), { payroll_id: selectedPayroll.value })
        ]);
        validation.value = vRes.data;
        preview.value = pRes.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

const hasErrors = computed(() => {
    return (validation.value.uan_missing?.length > 0) || 
           (validation.value.ncp_mismatch?.length > 0) || 
           (validation.value.wage_negative?.length > 0);
});

const hasCriticalErrors = computed(() => hasErrors.value); 

const generateECR = () => {
     axios.post(route('hr.compliance.ecr.generate'), {
        payroll_id: selectedPayroll.value,
        type: 'pf'
    }, { responseType: 'blob' })
    .then((response) => {
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `PF_ECR_RETURN.txt`);
        document.body.appendChild(link);
        link.click();
    });
};

const formatCurrency = (val) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(val);

</script>
