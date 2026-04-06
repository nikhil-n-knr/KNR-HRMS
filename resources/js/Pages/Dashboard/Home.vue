<template>
  <div>
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <StatCard 
        title="Total Employees" 
        value="124" 
        trend="+12%" 
        icon="UserGroupIcon" 
        color="bg-blue-500"
      />
      <StatCard 
        title="On Leave" 
        value="8" 
        trend="-2%" 
        icon="CalendarIcon" 
        color="bg-orange-500"
      />
      <StatCard 
        title="New Recruits" 
        value="12" 
        trend="+5%" 
        icon="UserPlusIcon" 
        color="bg-green-500"
      />
      <StatCard 
        title="Pending Approvals" 
        value="-" 
        icon="DocumentTextIcon" 
        color="bg-purple-500"
      />
    </div>

      
      <!-- Action Center -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8 h-[400px]">
          <!-- Pending Approvals Widget -->
          <MyApprovals />

          <!-- My Requests (Recent) -->
           <div class="bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col h-full">
              <div class="px-6 py-4 border-b border-gray-50">
                  <h3 class="font-bold text-gray-800 flex items-center gap-2">
                       <span class="bg-blue-100 text-blue-600 p-1.5 rounded-lg">
                          <UserPlusIcon class="w-5 h-5" />
                      </span>
                      My Recent Requests
                  </h3>
              </div>
              <div class="p-0 flex-1 overflow-hidden overflow-y-auto">
                  <table v-if="myRequests.length" class="w-full text-sm text-left">
                      <thead class="bg-gray-50/50 text-xs text-gray-500 uppercase sticky top-0">
                          <tr>
                              <th class="px-6 py-3">Type</th>
                              <th class="px-6 py-3">Current Step</th>
                              <th class="px-6 py-3 text-right">Status</th>
                          </tr>
                      </thead>
                      <tbody class="divide-y divide-gray-50">
                          <tr v-for="req in myRequests" :key="req.id" class="hover:bg-gray-50/50 transition">
                              <td class="px-6 py-3 font-medium text-gray-900">
                                  {{ req.workflow?.name || 'Request' }}
                                  <div class="text-xs text-gray-400">{{ req.created_at_human }}</div>
                              </td>
                              <td class="px-6 py-3 text-gray-600">
                                  {{ req.current_step?.stage?.name || 'Processing' }}
                                  <div class="text-xs text-gray-400" v-if="req.current_step?.approver">With: {{ req.current_step.approver.name }}</div>
                              </td>
                              <td class="px-6 py-3 text-right">
                                  <span v-if="req.status === 'approved'" class="text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded text-xs font-bold">Approved</span>
                                  <span v-else-if="req.status === 'rejected'" class="text-red-600 bg-red-50 px-2 py-0.5 rounded text-xs font-bold">Rejected</span>
                                  <span v-else class="text-amber-600 bg-amber-50 px-2 py-0.5 rounded text-xs font-bold">Pending</span>
                              </td>
                          </tr>
                      </tbody>
                  </table>
                   <div v-else class="p-8 text-center text-gray-400 text-sm">
                      No recent requests found.
                  </div>
              </div>
          </div>
      </div>

    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <h2 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h2>
      <div class="flex gap-4">
        <button class="px-4 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 font-medium text-sm transition">
          + Add Employee
        </button>
        <button class="px-4 py-2 bg-green-50 text-green-600 rounded-lg hover:bg-green-100 font-medium text-sm transition">
          Process Payroll
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { 
  UserGroupIcon, 
  CalendarIcon, 
  UserPlusIcon, 
  DocumentTextIcon 
} from '@heroicons/vue/24/outline';
import StatCard from '@/Components/StatCard.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import MyApprovals from '@/Components/Dashboard/MyApprovals.vue';
import { router } from '@inertiajs/vue3';

defineOptions({ layout: MainLayout });

const props = defineProps({
    myRequests: { type: Array, default: () => [] },
    stats: Object
});
</script>
