<template>
  <div class="space-y-6">

      <!-- Loading -->
      <div v-if="loading" class="text-center py-10 text-gray-400 text-sm">
          Loading mapping data...
      </div>

      <!-- Main Card -->
      <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

          <!-- Header -->
          <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100 bg-gray-50/50">
              <div>
                  <h3 class="text-lg font-semibold text-gray-900">UAN & ESI Mapping</h3>
                  <p class="text-xs text-gray-500 mt-1">
                      Manage missing statutory details
                  </p>
              </div>

              <div class="text-xs font-semibold bg-blue-50 text-blue-600 px-3 py-1 rounded-lg">
                  {{ employees.length }} Pending
              </div>
          </div>

          <!-- Table -->
          <div class="overflow-x-auto">
              <table class="min-w-full text-sm">

                  <!-- Header -->
                  <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                      <tr>
                          <th class="px-4 py-2 text-left">Employee</th>
                          <th class="px-4 py-2 text-left">Department</th>
                          <th class="px-4 py-2 text-left">UAN Number</th>
                          <th class="px-4 py-2 text-left">ESI Number</th>
                          <th class="px-4 py-2 text-right">Action</th>
                      </tr>
                  </thead>

                  <!-- Body -->
                  <tbody class="divide-y divide-gray-100">

                      <tr 
                        v-for="emp in employees" 
                        :key="emp.id"
                        class="hover:bg-gray-50 transition"
                      >

                          <!-- Employee -->
                          <td class="px-4 py-2">
                              <div class="font-semibold text-gray-800">
                                  {{ emp.first_name }} {{ emp.last_name }}
                              </div>
                          </td>

                          <!-- Dept -->
                          <td class="px-4 py-2 text-gray-500">
                              {{ emp.department?.name || '--' }}
                          </td>

                          <!-- UAN -->
                          <td class="px-4 py-2">
                              <input 
                                v-model="emp.uan_form" 
                                type="text"
                                placeholder="Enter UAN"
                                class="w-36 h-9 px-3 text-sm rounded-lg border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              />
                          </td>

                          <!-- ESI -->
                          <td class="px-4 py-2">
                              <input 
                                v-model="emp.esi_form" 
                                type="text"
                                placeholder="Enter IP No"
                                class="w-36 h-9 px-3 text-sm rounded-lg border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              />
                          </td>

                          <!-- Action -->
                          <td class="px-4 py-2 text-right">
                              <button 
                                @click="saveMapping(emp)"
                                class="px-4 h-9 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition"
                              >
                                  Save
                              </button>
                          </td>

                      </tr>

                      <!-- Empty State -->
                      <tr v-if="employees.length === 0">
                          <td colspan="5" class="text-center py-10">
                              <div class="flex flex-col items-center gap-2 text-green-600">
                                  <span class="text-lg">🎉</span>
                                  <p class="text-sm font-semibold">
                                      All employees are mapped!
                                  </p>
                              </div>
                          </td>
                      </tr>

                  </tbody>
              </table>
          </div>

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
        employees.value = employees.value.filter(e => e.id !== emp.id);
    } catch (e) {
        toast.error('Failed to save mapping');
    }
};

onMounted(fetchMissing);
</script>