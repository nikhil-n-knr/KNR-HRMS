<template>
  <Modal :show="show" @close="close" maxWidth="4xl">
    <div class="relative bg-white rounded-lg overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Header with Glass Effect -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-teal-50 flex justify-between items-center flex-shrink-0">
            <div>
                 <h2 class="text-xl font-bold text-gray-800">Career DNA</h2>
                 <p class="text-xs text-gray-500">Growth Timeline & Strategic Metrics</p>
            </div>
            <button @click="close" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div v-if="loading" class="p-12 flex justify-center">
             <svg class="animate-spin h-8 w-8 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        </div>

        <div v-else class="flex flex-col md:flex-row flex-1 overflow-hidden">
             <!-- Left: Metrics Config -->
             <div class="w-full md:w-1/4 bg-gray-50 p-6 border-r border-gray-200 overflow-y-auto">
                 <div class="mb-6">
                     <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Compound Annual Growth</label>
                     <div class="mt-1 flex items-baseline">
                         <span class="text-3xl font-extrabold text-indigo-600">{{ data.cagr }}%</span>
                         <span class="ml-1 text-xs text-gray-500">p.a.</span>
                     </div>
                 </div>
                 
                 <div class="mb-6">
                     <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Current Status</label>
                     <div class="mt-2 text-sm text-gray-800 font-medium pb-2 border-b border-gray-200">
                         CTC: ₹ {{ Number(data.latest_ctc).toLocaleString() }}
                     </div>
                 </div>

                 <!-- Flight Risk Logic (Frontend Sim) -->
                 <div class="mt-4 p-3 rounded-lg" :class="riskLevel.class">
                     <div class="flex items-center">
                         <span class="text-lg mr-2">{{ riskLevel.icon }}</span>
                         <span class="text-sm font-bold">{{ riskLevel.label }}</span>
                     </div>
                     <p class="text-[10px] mt-1 opacity-80">{{ riskLevel.desc }}</p>
                 </div>
             </div>

             <!-- Right: Charts -->
             <div class="w-full md:w-3/4 p-6 overflow-y-auto custom-scrollbar">
                  <div class="h-64 w-full mb-8">
                      <LineChart 
                         title="Career Growth Trajectory"
                         :labels="data.timeline.map(t => t.date)"
                         :datasets="[
                            {
                                label: 'Employee CTC',
                                data: data.timeline.map(t => t.ctc),
                                borderColor: '#059669', // Emerald 600
                                backgroundColor: 'rgba(5, 150, 105, 0.1)',
                                tension: 0.3,
                                fill: true
                            }
                         ]"
                      />
                  </div>

                  <!-- History Table -->
                  <h3 class="text-sm font-bold text-gray-700 mb-3">Milestone History</h3>
                  <table class="min-w-full text-left text-sm whitespace-nowrap">
                      <thead class="uppercase tracking-wider border-b-2 border-gray-200 bg-gray-50">
                          <tr>
                              <th class="px-3 py-2 text-gray-500 font-semibold text-xs">Date</th>
                              <th class="px-3 py-2 text-gray-500 font-semibold text-xs">Event</th>
                              <th class="px-3 py-2 text-gray-500 font-semibold text-xs text-right">CTC</th>
                              <th class="px-3 py-2 text-gray-500 font-semibold text-xs text-right">Growth</th>
                          </tr>
                      </thead>
                      <tbody class="divide-y divide-gray-100">
                           <tr v-for="(item, i) in data.timeline" :key="i" class="hover:bg-gray-50">
                               <td class="px-3 py-2 text-gray-600">{{ item.date }}</td>
                               <td class="px-3 py-2">
                                  <span v-if="i===0" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">Joined</span>
                                  <span v-else-if="item.hike_percent > 15" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Promotion / Correction</span>
                                  <span v-else class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Annual Hike</span>
                               </td>
                               <td class="px-3 py-2 text-right text-gray-800 font-mono">₹ {{ Number(item.ctc).toLocaleString() }}</td>
                               <td class="px-3 py-2 text-right">
                                   <span v-if="item.hike_percent > 0" class="text-green-600 font-bold">+{{ item.hike_percent }}%</span>
                                   <span v-else class="text-gray-400">-</span>
                               </td>
                           </tr>
                      </tbody>
                  </table>
             </div>
        </div>
    </div>
  </Modal>
</template>

<script setup>
import Modal from '@/Components/Modal.vue';
import LineChart from '@/Components/Charts/LineChart.vue';
import { ref, watch, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    show: Boolean,
    employeeId: Number
});

const emit = defineEmits(['close']);

const loading = ref(true);
const data = ref({ timeline: [], cagr: 0, latest_ctc: 0 });

const close = () => emit('close');

const fetchData = async () => {
    if (!props.employeeId) return;
    loading.value = true;
    try {
        // Use the proper endpoint
        const res = await axios.get(route('analytics.career-dna', props.employeeId));
        data.value = res.data;
    } catch (e) {
        console.error("Failed to fetch DNA", e);
    } finally {
        loading.value = false;
    }
};

watch(() => props.show, (val) => {
    if (val) fetchData();
});

const riskLevel = computed(() => {
    const cagr = Number(data.value.cagr);
    if (cagr < 5 && data.value.timeline.length > 1) {
        return { label: 'High Risk', icon: '🚨', desc: 'Growth significantly below market avg.', class: 'bg-red-50 text-red-700' };
    }
    if (cagr < 10) {
        return { label: 'Moderate Risk', icon: '⚠️', desc: 'Growth is steady but slow.', class: 'bg-orange-50 text-orange-700' };
    }
    return { label: 'Safe / Engaged', icon: '✅', desc: 'Healthy growth trajectory.', class: 'bg-green-50 text-green-700' };
});
</script>
