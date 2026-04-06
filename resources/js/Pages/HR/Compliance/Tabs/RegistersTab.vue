<template>
  <div class="space-y-6">
      <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
          <h3 class="font-bold text-gray-800 mb-6">Statutory Registers (CSV Export)</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
              <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Select Payroll Batch</label>
                  <select v-model="selectedPayroll" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                      <option :value="null">-- Select Month --</option>
                      <option v-for="batch in payrolls" :key="batch.id" :value="batch.id">
                          {{ batch.month_name }} {{ batch.year }}
                      </option>
                  </select>
              </div>
          </div>
          
           <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
              <button 
                  @click="download('form5')"
                  :disabled="!selectedPayroll"
                  class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-xl hover:border-indigo-500 hover:bg-indigo-50 transition group disabled:opacity-50"
              >
                  <div class="bg-indigo-100 p-3 rounded-full mb-3 group-hover:bg-indigo-200">
                       <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                  </div>
                  <span class="font-bold text-gray-800">Form 5</span>
                  <span class="text-xs text-gray-500">New Joinees Register</span>
              </button>

               <button 
                  @click="download('form10')"
                  :disabled="!selectedPayroll"
                  class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-xl hover:border-red-500 hover:bg-red-50 transition group disabled:opacity-50"
              >
                   <div class="bg-red-100 p-3 rounded-full mb-3 group-hover:bg-red-200">
                       <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 2.192V17h12v-.808A6 6 0 009 14zm4-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                  </div>
                  <span class="font-bold text-gray-800">Form 10</span>
                  <span class="text-xs text-gray-500">Resigned Employees</span>
              </button>

               <button 
                  @click="download('form12a')"
                  :disabled="!selectedPayroll"
                  class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-xl hover:border-emerald-500 hover:bg-emerald-50 transition group disabled:opacity-50"
              >
                  <div class="bg-emerald-100 p-3 rounded-full mb-3 group-hover:bg-emerald-200">
                       <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                  </div>
                  <span class="font-bold text-gray-800">Form 12A</span>
                  <span class="text-xs text-gray-500">Monthly Wages Abstract</span>
              </button>
           </div>
      </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const payrolls = ref([]);
const selectedPayroll = ref(null);

onMounted(async () => {
     try {
        const res = await axios.get(route('hr.compliance.payrolls')); 
        payrolls.value = res.data;
    } catch (e) {}
});

const download = (type) => {
    axios.post(route('hr.compliance.registers.download'), {
        payroll_id: selectedPayroll.value,
        type: type
    }, { responseType: 'blob' })
    .then((response) => {
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `${type.toUpperCase()}.csv`);
        document.body.appendChild(link);
        link.click();
    });
};
</script>
