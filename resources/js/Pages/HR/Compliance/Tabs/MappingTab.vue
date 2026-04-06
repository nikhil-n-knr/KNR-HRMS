<template>
  <div class="space-y-6">
      <div v-if="loading" class="text-center py-10 text-gray-500">Loading mapping data...</div>
      
      <div v-else class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
              <h3 class="font-bold text-gray-800">UAN & ESI Mapping</h3>
              <p class="text-sm text-gray-500">Showing {{ employees.length }} employees with missing details.</p>
          </div>
          
          <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                  <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dept</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">UAN Number</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ESI Number</th>
                      <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                  </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                  <tr v-for="emp in employees" :key="emp.id" class="hover:bg-gray-50">
                      <td class="px-6 py-4 whitespace-nowrap">
                          <div class="text-sm font-bold text-gray-900">{{ emp.first_name }} {{ emp.last_name }}</div>
                      </td>
                       <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                          {{ emp.department?.name || '--' }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                          <input 
                            v-model="emp.uan_form" 
                            type="text" 
                            class="text-xs border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500 w-32"
                            placeholder="Enter UAN"
                          >
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                          <input 
                            v-model="emp.esi_form" 
                            type="text" 
                            class="text-xs border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500 w-32"
                            placeholder="Enter IP No"
                          >
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-right">
                          <button 
                            @click="saveMapping(emp)"
                            class="text-xs bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700 transition"
                          >
                              Save
                          </button>
                      </td>
                  </tr>
                   <tr v-if="employees.length === 0">
                      <td colspan="5" class="px-6 py-10 text-center text-green-600 font-medium bg-green-50">
                          🎉 All active employees have UAN mapped!
                      </td>
                  </tr>
              </tbody>
          </table>
      </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';

const employees = ref([]);
const loading = ref(true);
const toast = useToastStore();

const fetchMissing = async () => {
    loading.value = true;
    try {
        const res = await axios.get(route('hr.compliance.missing-uan'));
        employees.value = res.data.map(e => ({
            ...e,
            uan_form: '',
            esi_form: ''
        }));
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

const saveMapping = async (emp) => {
    if(!emp.uan_form && !emp.esi_form) return;
    
    try {
        await axios.put(route('hr.compliance.mapping.update', emp.id), {
            uan_number: emp.uan_form,
            esi_number: emp.esi_form
        });
        toast.success(`Mapping saved for ${emp.first_name}`);
        // Remove from list or clear
        employees.value = employees.value.filter(e => e.id !== emp.id);
    } catch (e) {
        toast.error('Failed to save mapping');
    }
};

onMounted(fetchMissing);
</script>
