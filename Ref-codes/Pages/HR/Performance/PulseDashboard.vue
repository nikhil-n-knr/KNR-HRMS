<template>
  <div class="max-w-7xl mx-auto py-8">
    <div class="mb-8 flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Pulse Dashboard</h1>
        <p class="text-gray-500 text-lg">Performance Metrics for {{ currentMonth }}</p>
      </div>
      <button class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold shadow-lg shadow-indigo-200 transition-all flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
        Refresh Data
      </button>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
       <div class="bg-white/70 backdrop-blur-xl p-6 rounded-2xl border border-white/50 shadow-xl">
          <div class="flex items-center gap-4">
             <div class="p-3 bg-emerald-100 text-emerald-600 rounded-xl">
               <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
             </div>
             <div>
                <p class="text-sm font-bold text-gray-500 uppercase">Avg Quality Score</p>
                <p class="text-3xl font-extrabold text-gray-800">92%</p>
             </div>
          </div>
       </div>
       <div class="bg-white/70 backdrop-blur-xl p-6 rounded-2xl border border-white/50 shadow-xl">
          <div class="flex items-center gap-4">
             <div class="p-3 bg-blue-100 text-blue-600 rounded-xl">
               <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
             </div>
             <div>
                <p class="text-sm font-bold text-gray-500 uppercase">Total Velocity</p>
                <p class="text-3xl font-extrabold text-gray-800">142 pts</p>
             </div>
          </div>
       </div>
       <div class="bg-white/70 backdrop-blur-xl p-6 rounded-2xl border border-white/50 shadow-xl">
          <div class="flex items-center gap-4">
             <div class="p-3 bg-rose-100 text-rose-600 rounded-xl">
               <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
             </div>
             <div>
                <p class="text-sm font-bold text-gray-500 uppercase">Active Risks</p>
                <p class="text-3xl font-extrabold text-gray-800">2</p>
             </div>
          </div>
       </div>
    </div>

    <!-- Leaderboard Table -->
    <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
         <h3 class="font-bold text-gray-800 text-lg">Team Performance</h3>
         <div class="flex gap-2">
            <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded">High Performers</span>
            <span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded">On Track</span>
            <span class="px-2 py-1 bg-red-100 text-red-700 text-xs font-bold rounded">Needs Attention</span>
         </div>
      </div>
      
      <table class="w-full text-left">
        <thead>
          <tr class="text-xs font-bold text-gray-500 uppercase bg-gray-50/50 border-b border-gray-200">
            <th class="px-6 py-4">Employee</th>
            <th class="px-6 py-4 text-center">Tasks Done</th>
            <th class="px-6 py-4 text-center">Velocity</th>
            <th class="px-6 py-4 text-center">Quality Score</th>
            <th class="px-6 py-4 text-right">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="metric in metrics" :key="metric.id" class="hover:bg-gray-50/50 transition-colors">
            <td class="px-6 py-4 font-medium text-gray-800">
               <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs">
                    {{ metric.first_name[0] }}{{ metric.last_name[0] }}
                  </div>
                  {{ metric.first_name }} {{ metric.last_name }}
               </div>
            </td>
            <td class="px-6 py-4 text-center text-gray-600 font-medium">{{ metric.tasks_completed }}</td>
             <td class="px-6 py-4 text-center text-gray-600 font-medium">{{ metric.avg_completion_hours }}h/task</td>
            <td class="px-6 py-4 text-center">
               <div class="inline-flex items-center gap-2">
                 <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full rounded-full" 
                        :class="metric.quality_score >= 90 ? 'bg-emerald-500' : (metric.quality_score >= 80 ? 'bg-yellow-500' : 'bg-red-500')"
                        :style="`width: ${metric.quality_score}%`"></div>
                 </div>
                 <span class="font-bold text-sm" :class="metric.quality_score >= 90 ? 'text-emerald-600' : 'text-gray-700'">{{ metric.quality_score }}%</span>
               </div>
            </td>
            <td class="px-6 py-4 text-right">
              <span v-if="metric.quality_score >= 90" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                Excellent
              </span>
              <span v-else-if="metric.quality_score >= 80" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                 Good
               </span>
               <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                 At Risk
               </span>
            </td>
          </tr>
          <tr v-if="metrics.length === 0" class="bg-gray-50/50">
             <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                <p>No performance data generated yet for {{ currentMonth }}.</p>
                <p class="text-sm mt-1">Run "php artisan pulse:aggregate" to calculate metrics.</p>
             </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });
const props = defineProps(['metrics', 'currentMonth']);
</script>
