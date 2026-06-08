<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>

        <div v-if="employee" class="relative w-full max-w-6xl bg-white rounded-[32px] overflow-hidden shadow-2xl flex flex-col h-[90vh] transform transition-all border border-slate-200">
          
          <!-- Header Area -->
          <div class="relative px-8 py-8 shrink-0 bg-slate-900 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 to-purple-900 opacity-90 z-0"></div>
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-fuchsia-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob z-0"></div>
            
            <div class="relative z-10 flex items-start gap-6">
               <img :src="employee.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(employee.name || 'U')}&background=random`" @error="$event.target.src=`https://ui-avatars.com/api/?name=${encodeURIComponent(employee.name || 'U')}&background=random`" class="w-24 h-24 rounded-2xl object-cover ring-4 ring-white/20 shadow-xl" />
               
               <div class="text-white flex-1">
                 <div class="flex items-center justify-between">
                   <div>
                     <h2 class="text-3xl font-black tracking-tight">{{ employee.name }}</h2>
                     <p class="text-indigo-200 font-bold tracking-widest uppercase text-xs mt-1">{{ employee.designation }} • {{ employee.department || 'N/A' }}</p>
                   </div>
                   <button @click="$emit('close')" class="p-2 hover:bg-white/10 rounded-xl transition-all">
                     <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                   </button>
                 </div>
                 
                 <div class="flex gap-4 mt-6">
                    <div class="bg-white/10 backdrop-blur-md px-4 py-2 rounded-xl border border-white/10">
                      <span class="text-[10px] text-indigo-300 uppercase font-black tracking-wider block">Check-in Status</span>
                      <span class="text-lg font-black text-white">{{ employee.has_checked_in ? 'In Office' : 'Pending' }}</span>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md px-4 py-2 rounded-xl border border-white/10">
                      <span class="text-[10px] text-indigo-300 uppercase font-black tracking-wider block">Today's Load</span>
                      <span class="text-lg font-black text-white">{{ employee.today_hours }}h</span>
                    </div>
                 </div>
               </div>
            </div>
          </div>

          <!-- Content Area -->
          <div class="flex-1 overflow-y-auto p-8 space-y-8 bg-slate-50/50">
            
            <div v-if="loading" class="h-64 flex flex-col items-center justify-center space-y-4">
               <div class="w-10 h-10 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin"></div>
               <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Aggregating Weekly Analytics...</span>
            </div>

            <template v-else-if="reportData">
               <!-- Performance Stats Grid -->
               <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                  <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                     <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Weekly Effort (Actual)</p>
                     <div class="flex items-end gap-2 mt-2">
                        <span class="text-3xl font-black text-slate-800">{{ reportData.metrics.total_actual_hours }}h</span>
                        <span class="text-xs font-bold text-slate-400 mb-1">/ {{ reportData.metrics.total_allocated_hours }}h</span>
                     </div>
                  </div>
                  <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                     <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Efficiency Gap</p>
                     <div class="flex items-center gap-2 mt-2">
                        <span :class="reportData.metrics.productivity_variance > 0 ? 'text-rose-600' : 'text-emerald-600'" class="text-3xl font-black">
                           {{ reportData.metrics.productivity_variance > 0 ? '+' : '' }}{{ reportData.metrics.productivity_variance }}h
                        </span>
                        <span class="text-[10px] font-black uppercase text-slate-400">{{ reportData.metrics.productivity_variance > 0 ? 'Drift' : 'Lead' }}</span>
                     </div>
                  </div>
                  <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                     <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Attendance & Leaves</p>
                     <div class="flex items-center gap-4 mt-2">
                        <div>
                           <span class="text-xl font-black text-slate-800">{{ reportData.leaveStats.taken_this_month }}</span>
                           <span class="text-[10px] font-bold text-slate-400 ml-1">Leaves</span>
                        </div>
                        <div class="w-px h-8 bg-slate-100"></div>
                        <div>
                           <span class="text-xl font-black text-slate-800">{{ reportData.metrics.tasks_completed }}</span>
                           <span class="text-[10px] font-bold text-slate-400 ml-1">Dones</span>
                        </div>
                     </div>
                  </div>
                  <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                     <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Action Items</p>
                     <div class="flex items-center gap-2 mt-2">
                        <span class="text-3xl font-black text-rose-500">{{ reportData.metrics.delayed_tasks }}</span>
                        <span class="text-xs font-bold text-rose-400">Delayed Tasks</span>
                     </div>
                  </div>
               </div>

               <!-- Detailed Section -->
               <div class="grid grid-cols-12 gap-8">
                  <!-- Task & Timesheet Table -->
                  <div class="col-span-12 lg:col-span-8 bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden h-fit">
                     <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                        <h3 class="font-black text-slate-800 text-sm uppercase tracking-wider">Timesheet Analysis & Assignments</h3>
                     </div>
                     <div class="overflow-x-auto">
                        <table class="w-full text-left">
                           <thead>
                              <tr class="bg-slate-50/30">
                                 <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest pl-8">Task Detail</th>
                                 <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Plan</th>
                                 <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-indigo-600">Actual</th>
                                 <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Variance</th>
                                 <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right pr-8">Status</th>
                              </tr>
                           </thead>
                           <tbody class="divide-y divide-slate-100">
                              <tr v-for="task in reportData.tasks" :key="task.id" class="hover:bg-slate-50 transition-all group">
                                 <td class="px-6 py-4 pl-8">
                                    <p class="text-sm font-bold text-slate-700 group-hover:text-indigo-600">{{ task.title }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">{{ task.project }}</p>
                                 </td>
                                 <td class="px-6 py-4 text-sm font-bold text-slate-600">{{ task.allocated_hours }}h</td>
                                 <td class="px-6 py-4 text-sm font-black text-indigo-600">{{ task.actual_hours }}h</td>
                                 <td class="px-6 py-4">
                                    <span :class="task.variance > 0 ? 'text-rose-600' : 'text-emerald-600'" class="text-xs font-black">
                                       {{ task.variance > 0 ? '+' : '' }}{{ task.variance }}h
                                    </span>
                                 </td>
                                 <td class="px-6 py-4 text-right pr-8">
                                    <span :class="{
                                       'bg-emerald-100 text-emerald-700': task.status === 'Done' || task.status === 'Completed',
                                       'bg-indigo-100 text-indigo-700': task.status === 'In Progress',
                                       'bg-rose-100 text-rose-700': task.is_delayed
                                    }" class="px-2 py-0.5 rounded text-[10px] font-black uppercase">
                                       {{ task.is_delayed ? 'Delayed' : task.status }}
                                    </span>
                                 </td>
                              </tr>
                              <tr v-if="!reportData.tasks.length">
                                 <td colspan="5" class="py-12 text-center text-slate-400 text-sm italic font-bold">No tasks recorded for this period.</td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>

                  <!-- Right Side: Attendance & Bugs -->
                  <div class="col-span-12 lg:col-span-4 space-y-8">
                     <!-- Attendance History -->
                     <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 bg-emerald-50/50">
                           <h3 class="font-black text-emerald-900 text-sm uppercase tracking-wider flex items-center gap-2">
                              <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span> Weekly Presence
                           </h3>
                        </div>
                        <div class="p-0">
                           <div v-for="log in reportData.attendance" :key="log.date" class="p-4 border-b border-slate-50 flex items-center justify-between hover:bg-slate-50 transition-colors">
                              <div>
                                 <p class="text-xs font-bold text-slate-800">{{ log.date }}</p>
                                 <p class="text-[10px] text-slate-400 font-bold">{{ log.status }}</p>
                              </div>
                              <div class="text-right">
                                 <p class="text-xs font-black text-slate-700">{{ log.check_in }} - {{ log.check_out }}</p>
                                 <p class="text-[10px] font-black text-emerald-600 uppercase">{{ log.total_hours }}h Logged</p>
                              </div>
                           </div>
                        </div>
                     </div>

                     <!-- Bugs -->
                     <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 bg-rose-50/50">
                           <h3 class="font-black text-rose-900 text-sm uppercase tracking-wider">Bug Resolution</h3>
                        </div>
                        <div class="p-6 space-y-4">
                           <div v-for="bug in reportData.bugs" :key="bug.id" class="p-3 bg-slate-50 border border-slate-100 rounded-2xl">
                              <div class="flex justify-between items-start mb-1">
                                 <span :class="bug.status === 'Closed' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'" class="px-2 py-0.5 rounded text-[9px] font-black uppercase">{{ bug.status }}</span>
                                 <span class="text-[9px] font-bold text-slate-400">{{ bug.severity }}</span>
                              </div>
                              <p class="text-xs font-bold text-slate-700 line-clamp-2">{{ bug.subject }}</p>
                           </div>
                           <div v-if="!reportData.bugs.length" class="text-center py-4 text-slate-400 text-sm font-bold italic">Clean slate. No bugs!</div>
                        </div>
                     </div>
                  </div>
               </div>
            </template>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  show: Boolean,
  employee: Object
});

const emit = defineEmits(['close']);

const loading = ref(false);
const reportData = ref(null);

watch(() => props.show, async (newVal) => {
  if (newVal && props.employee) {
    loading.value = true;
    reportData.value = null;
    try {
      const response = await axios.get(`/projects/management-dashboard/employee/${props.employee.id}/360`);
      reportData.value = response.data;
    } catch (error) {
      console.error('Failed to load employee 360 data', error);
    } finally {
      loading.value = false;
    }
  }
});
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .transform,
.modal-leave-active .transform {
   transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-enter-from .transform,
.modal-leave-to .transform {
   opacity: 0;
   transform: scale(0.95) translateY(20px);
}

.animate-blob {
  animation: blob 7s infinite;
}

@keyframes blob {
  0% { transform: translate(0px, 0px) scale(1); }
  33% { transform: translate(30px, -50px) scale(1.1); }
  66% { transform: translate(-20px, 20px) scale(0.9); }
  100% { transform: translate(0px, 0px) scale(1); }
}
</style>
