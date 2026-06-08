<template>
  <div class="space-y-6">

      <!-- MAIN CARD -->
      <div class="bg-white border border-gray-300 p-8 shadow-xl rounded-2xl">

          <!-- TITLE -->
          <div class="mb-6">
              <h3 class="text-xl font-bold text-gray-900">
                  Statutory Registers
              </h3>
              <p class="text-sm text-gray-500 mt-1">
                  Download statutory CSV reports for compliance
              </p>
          </div>

          <!-- SELECT -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
              <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">
                      Select Payroll Batch
                  </label>

                  <select 
                      v-model="selectedPayroll" 
                      class="w-full h-12 px-4 rounded-xl border-2 border-gray-300 bg-white text-gray-800 shadow-sm focus:border-indigo-600 focus:ring-2 focus:ring-indigo-200 transition"
                  >
                      <option :value="null">-- Select Month --</option>
                      <option 
                          v-for="batch in payrolls" 
                          :key="batch.id" 
                          :value="batch.id"
                      >
                          {{ batch.month_name }} {{ batch.year }}
                      </option>
                  </select>
              </div>
          </div>
          
          <!-- CARDS -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-10">

              <!-- FORM 5 -->
              <button 
                  @click="download('form5')"
                  :disabled="!selectedPayroll"
                  class="group flex flex-col items-center justify-center p-8 rounded-2xl 
                         border-2 border-gray-300 bg-white 
                         shadow-md hover:shadow-xl 
                         hover:border-indigo-500 hover:-translate-y-1 
                         transition-all duration-300 disabled:opacity-50"
              >
                  <div class="bg-indigo-200 p-5 rounded-2xl mb-4 shadow-inner group-hover:bg-indigo-300 transition">
                       <svg class="w-8 h-8 text-indigo-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                       </svg>
                  </div>

                  <span class="font-bold text-gray-900 text-lg">Form 5</span>
                  <span class="text-sm text-gray-500 mt-1">
                      New Joinees Register
                  </span>
              </button>

              <!-- FORM 10 -->
              <button 
                  @click="download('form10')"
                  :disabled="!selectedPayroll"
                  class="group flex flex-col items-center justify-center p-8 rounded-2xl 
                         border-2 border-gray-300 bg-white 
                         shadow-md hover:shadow-xl 
                         hover:border-red-500 hover:-translate-y-1 
                         transition-all duration-300 disabled:opacity-50"
              >
                  <div class="bg-red-200 p-5 rounded-2xl mb-4 shadow-inner group-hover:bg-red-300 transition">
                       <svg class="w-8 h-8 text-red-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 2.192V17h12v-.808A6 6 0 009 14zm4-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                       </svg>
                  </div>

                  <span class="font-bold text-gray-900 text-lg">Form 10</span>
                  <span class="text-sm text-gray-500 mt-1">
                      Resigned Employees
                  </span>
              </button>

              <!-- FORM 12A -->
              <button 
                  @click="download('form12a')"
                  :disabled="!selectedPayroll"
                  class="group flex flex-col items-center justify-center p-8 rounded-2xl 
                         border-2 border-gray-300 bg-white 
                         shadow-md hover:shadow-xl 
                         hover:border-emerald-500 hover:-translate-y-1 
                         transition-all duration-300 disabled:opacity-50"
              >
                  <div class="bg-emerald-200 p-5 rounded-2xl mb-4 shadow-inner group-hover:bg-emerald-300 transition">
                       <svg class="w-8 h-8 text-emerald-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                       </svg>
                  </div>

                  <span class="font-bold text-gray-900 text-lg">Form 12A</span>
                  <span class="text-sm text-gray-500 mt-1">
                      Monthly Wages Abstract
                  </span>
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