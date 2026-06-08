<template>
  <div class="space-y-6">

      <!-- Batch Selector -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col md:flex-row md:items-end md:justify-between gap-4">

          <div class="w-full md:w-1/3">
              <label class="text-sm font-semibold text-gray-700">Select Payroll Batch</label>
              <select 
                v-model="selectedPayroll" 
                @change="runDiagnostics"
                class="mt-1 w-full h-11 px-3 rounded-lg border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
              >
                  <option :value="null">-- Select Month --</option>
                  <option v-for="batch in payrolls" :key="batch.id" :value="batch.id">
                      {{ batch.month_name }} {{ batch.year }}
                  </option>
              </select>
          </div>

          <div v-if="selectedPayroll" class="flex items-center">
              <span v-if="loading" class="text-sm text-gray-400 animate-pulse">
                  Running Diagnostics...
              </span>

              <span 
                v-else
                class="text-xs font-semibold px-3 py-1 rounded-lg"
                :class="hasErrors ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600'"
              >
                {{ hasErrors ? 'Attention Required' : 'Ready to File' }}
              </span>
          </div>
      </div>

      <!-- Main Grid -->
      <div v-if="selectedPayroll" class="grid grid-cols-1 lg:grid-cols-3 gap-6">

          <!-- LEFT PANEL -->
          <div class="space-y-4">

              <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                  <h3 class="text-sm font-semibold text-gray-800 mb-4">Health Check</h3>

                  <div class="space-y-3">
                      <ValidationItem title="UAN Mapping" :count="validation.uan_missing?.length" type="critical" :items="validation.uan_missing" />
                      <ValidationItem title="NCP Days Mismatch" :count="validation.ncp_mismatch?.length" type="critical" :items="validation.ncp_mismatch" />
                      <ValidationItem title="Negative Wages" :count="validation.wage_negative?.length" type="critical" :items="validation.wage_negative" />
                      <ValidationItem title="KYC Compliance" :count="validation.kyc_missing?.length" type="warning" :items="validation.kyc_missing" />
                  </div>
              </div>

          </div>

          <!-- RIGHT PANEL -->
          <div class="lg:col-span-2 space-y-6">

              <!-- SUMMARY -->
              <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                  <h3 class="text-sm font-semibold text-gray-800 mb-4">ECR Data Preview</h3>

                  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                      <div class="p-4 rounded-xl bg-gray-50">
                          <p class="text-xs text-gray-500 uppercase">Total Wages</p>
                          <p class="text-lg font-bold text-gray-900 mt-1">
                              {{ formatCurrency(preview.totals?.gross || 0) }}
                          </p>
                      </div>

                      <div class="p-4 rounded-xl bg-blue-50">
                          <p class="text-xs text-blue-500 uppercase">EPF Liability</p>
                          <p class="text-lg font-bold text-blue-700 mt-1">
                              {{ formatCurrency(preview.totals?.epf || 0) }}
                          </p>
                      </div>

                      <div class="p-4 rounded-xl bg-purple-50">
                          <p class="text-xs text-purple-500 uppercase">EPS</p>
                          <p class="text-lg font-bold text-purple-700 mt-1">
                              {{ formatCurrency(preview.totals?.eps || 0) }}
                          </p>
                      </div>
                  </div>
              </div>

              <!-- TABLE -->
              <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

                  <div class="px-5 py-3 border-b border-gray-100 text-xs text-gray-500">
                      Showing first 5 of {{ preview.totals?.employees }} records
                  </div>

                  <div class="overflow-x-auto">
                      <table class="min-w-full text-sm">

                          <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                              <tr>
                                  <th class="px-4 py-2 text-left">UAN</th>
                                  <th class="px-4 py-2 text-left">Name</th>
                                  <th class="px-4 py-2 text-right">EPF</th>
                                  <th class="px-4 py-2 text-right">EPS</th>
                                  <th class="px-4 py-2 text-right">NCP</th>
                              </tr>
                          </thead>

                          <tbody class="divide-y divide-gray-100">

                              <tr 
                                v-for="row in preview.sample" 
                                :key="row.uan"
                                class="hover:bg-gray-50 transition"
                              >
                                  <td class="px-4 py-2 font-mono text-gray-700">{{ row.uan }}</td>
                                  <td class="px-4 py-2 text-gray-800">{{ row.name }}</td>
                                  <td class="px-4 py-2 text-right">{{ row.epf_wages }}</td>
                                  <td class="px-4 py-2 text-right">{{ row.eps_wages }}</td>
                                  <td class="px-4 py-2 text-right">{{ row.ncp_days }}</td>
                              </tr>

                          </tbody>
                      </table>
                  </div>
              </div>

              <!-- ACTIONS -->
              <div class="flex justify-between items-center pt-2">

                  <button class="text-sm text-gray-600 hover:text-gray-800 underline">
                      Download ESI Return
                  </button>

                  <button 
                    @click="generateECR"
                    :disabled="hasCriticalErrors"
                    class="px-6 h-11 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                  >
                      {{ hasCriticalErrors ? 'Fix Errors to Generate' : 'Generate ECR File' }}
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
