<template>
  <MainLayout>
    <Head title="LMS Command Center" />

    <div class="min-h-screen bg-slate-50/50 p-4 sm:p-8">
      <!-- Header -->
      <div class="max-w-7xl mx-auto mb-10">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div>
            <div class="flex items-center gap-3 mb-2">
              <span class="bg-emerald-600/10 text-emerald-600 text-[10px] font-black uppercase tracking-[0.2em] px-2 py-0.5 rounded border border-emerald-500/30">Analytics Engine v2.0</span>
              <span class="text-slate-400 text-[10px] font-bold uppercase tracking-widest">Real-time Telemetry</span>
            </div>
            <h1 class="text-4xl font-black text-slate-800 tracking-tight italic uppercase">Command <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-600 to-teal-500 whitespace-nowrap">Center</span></h1>
            <p class="text-slate-500 text-sm mt-1 font-medium italic">"Deciphering learning patterns across state-level institutions."</p>
          </div>

          <div class="flex items-center gap-4">
             <div class="bg-white border border-slate-200 rounded-3xl px-8 py-4 shadow-sm relative overflow-hidden group">
               <div class="absolute inset-0 bg-emerald-500/5 translate-y-full group-hover:translate-y-0 transition-transform"></div>
               <p class="text-[10px] text-slate-500 uppercase font-black tracking-widest mb-1 relative z-10">Active Nodes</p>
               <div class="flex items-center gap-2 justify-end relative z-10">
                 <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
                 <span class="text-2xl font-black text-emerald-800 tracking-tighter">{{ stats.active_now || 124 }}</span>
               </div>
             </div>
             <button class="bg-slate-800 hover:bg-black text-white text-[10px] font-black uppercase tracking-widest px-8 py-5 rounded-[24px] transition-all shadow-xl shadow-slate-900/10 active:scale-95">
               Export Intelligence
             </button>
          </div>
        </div>
      </div>

      <!-- Core Stats (Level 1) -->
      <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
          <div v-for="stat in mainStats" :key="stat.label" 
            class="bg-white rounded-[40px] p-8 border border-slate-200 relative overflow-hidden group hover:border-emerald-500/30 transition-all shadow-sm hover:shadow-xl">
            <div class="absolute -right-8 -bottom-8 p-4 text-emerald-500/5 group-hover:text-emerald-500/10 group-hover:scale-110 transition-all uppercase">
              <component :is="stat.icon" class="h-40 w-40" />
            </div>
            <div class="relative z-10">
              <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-4">{{ stat.label }}</p>
              <div class="flex items-end gap-3">
                <h2 class="text-4xl font-black text-slate-800 tracking-tighter">{{ stat.value }}</h2>
                <span :class="['text-[10px] font-black mb-1.5 uppercase', stat.trendUp ? 'text-emerald-600' : 'text-rose-500']">{{ stat.trend }}</span>
              </div>
              <div class="mt-4 w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                <div :class="['h-full rounded-full', stat.color]" :style="`width: ${stat.pct}%` "></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Insights Level 2: Funnels and Trends -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
          <!-- Engagement Funnel -->
          <div class="lg:col-span-1 bg-white rounded-[40px] p-8 border border-slate-200 shadow-sm">
             <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-8 flex items-center gap-3">
               <ArrowDownCircleIcon class="h-5 w-5 text-emerald-500" />
               Learning Funnel
             </h3>
             <div class="space-y-6">
                <div v-for="(step, idx) in funnel" :key="step.label" class="relative">
                   <div class="flex justify-between items-end mb-2">
                     <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ step.label }}</span>
                     <span class="text-sm font-black text-slate-700">{{ step.count.toLocaleString() }}</span>
                   </div>
                   <!-- Funnel bar -->
                   <div class="w-full bg-slate-50 h-12 rounded-2xl overflow-hidden flex items-center px-4 relative border border-slate-100">
                      <div :class="['h-full absolute left-0 transition-all duration-1000 shadow-[inset_-20px_0_40px_rgba(0,0,0,0.02)]', step.color]" 
                        :style="`width: ${step.pct}%` ">
                      </div>
                      <span class="relative z-10 text-[10px] font-black text-emerald-800">{{ step.pct }}% Yield</span>
                   </div>
                   <!-- Connector -->
                   <div v-if="idx < funnel.length - 1" class="flex justify-center -my-3 relative z-20">
                     <div class="w-8 h-8 bg-slate-50 rounded-full border border-slate-200 flex items-center justify-center shadow-sm">
                       <ChevronDownIcon class="h-4 w-4 text-emerald-500" />
                     </div>
                   </div>
                </div>
             </div>
          </div>

          <!-- Trend Chart -->
          <div class="lg:col-span-2 bg-white rounded-[40px] p-8 border border-slate-200 shadow-sm flex flex-col">
             <div class="flex items-center justify-between mb-8">
               <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest flex items-center gap-3">
                 <ChartBarIcon class="h-5 w-5 text-teal-500" />
                 Enrollment & Velocity
               </h3>
               <div class="flex items-center gap-4">
                 <select class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-[10px] font-black uppercase text-slate-500 outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all">
                   <option>Platform Window: 30D</option>
                   <option>Platform Window: 6M</option>
                 </select>
               </div>
             </div>

             <div class="flex-1 min-h-[300px] flex items-end justify-between gap-px px-2 pb-6 border-b border-slate-100">
                <!-- Mock Chart Bars -->
                <div v-for="n in 30" :key="n" class="flex flex-col items-center gap-2 group w-full">
                   <div class="flex flex-col gap-0.5 w-full max-w-[12px]">
                     <div class="bg-emerald-500/20 w-full rounded-t-sm group-hover:bg-emerald-500 transition-all opacity-40 group-hover:opacity-100" :style="`height: ${Math.random() * 100 + 40}px` "></div>
                     <div class="bg-teal-500/30 w-full rounded-b-sm group-hover:bg-teal-500 transition-all opacity-40 group-hover:opacity-100" :style="`height: ${Math.random() * 60 + 10}px` "></div>
                   </div>
                </div>
             </div>
             <div class="flex justify-between mt-4 text-[9px] font-black text-slate-400 uppercase tracking-widest px-2">
               <span>Cycle Start</span>
               <span>Midpoint</span>
               <span>Live Telemetry</span>
             </div>

             <div class="mt-8 flex items-center gap-8">
                <div class="flex items-center gap-2">
                  <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                  <span class="text-[10px] font-black text-slate-500 uppercase">Enrollments</span>
                </div>
                <div class="flex items-center gap-2">
                  <div class="w-2.5 h-2.5 rounded-full bg-teal-500"></div>
                  <span class="text-[10px] font-black text-slate-500 uppercase">Velocity</span>
                </div>
             </div>
          </div>
        </div>

        <!-- Level 3: Leaderboard -->
        <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden mb-12">
          <div class="p-8 border-b border-slate-100 flex items-center justify-between bg-emerald-50/30">
             <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest flex items-center gap-3">
               <AcademicCapIcon class="h-5 w-5 text-emerald-600" />
               Institutional Performance Intelligence
             </h3>
             <div class="text-[9px] font-black text-emerald-800/50 uppercase tracking-widest bg-emerald-100/50 px-3 py-1 rounded-full border border-emerald-200/50">
                Top 10 Global Leaders
             </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead>
                <tr class="bg-slate-50 text-[10px] text-slate-400 uppercase tracking-[0.2em] font-black border-b border-slate-100">
                  <th class="px-8 py-5">Intel-Node</th>
                  <th class="px-6 py-5">Active Hubs</th>
                  <th class="px-6 py-5">Avg Velocity</th>
                  <th class="px-6 py-5">Credentials</th>
                  <th class="px-6 py-5">Fatigue Rate</th>
                  <th class="px-6 py-5">Integrity</th>
                  <th class="px-8 py-5 text-right">Report</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="inst in institutions" :key="inst.id" class="hover:bg-slate-50/50 transition-colors group">
                  <td class="px-8 py-6">
                    <div class="flex items-center gap-4">
                      <div class="w-10 h-10 rounded-2xl bg-slate-100 flex items-center justify-center font-black text-[10px] text-slate-400 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm">{{ inst.code }}</div>
                      <div>
                        <p class="text-[11px] font-black text-slate-800 uppercase tracking-tighter">{{ inst.name }}</p>
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ inst.type }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-6 text-[11px] text-slate-600 font-black uppercase tracking-widest">{{ inst.active_learners.toLocaleString() }}</td>
                  <td class="px-6 py-6">
                    <div class="flex items-center gap-3">
                      <div class="flex-1 bg-slate-100 h-1 rounded-full overflow-hidden max-w-[80px] border border-slate-200/50">
                        <div class="bg-emerald-500 h-full transition-all duration-1000" :style="`width: ${inst.avg_progress}%` "></div>
                      </div>
                      <span class="text-[10px] font-black text-slate-400">{{ inst.avg_progress }}%</span>
                    </div>
                  </td>
                  <td class="px-6 py-6">
                    <div class="flex items-center gap-2">
                      <TrophyIcon class="h-4 w-4 text-emerald-500" />
                      <span class="text-[11px] font-black text-slate-700">{{ inst.certificates_count }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-6">
                    <div class="text-[10px] font-black text-rose-600 bg-rose-50 px-2.5 py-1.5 rounded-lg border border-rose-100 uppercase tracking-tighter">{{ inst.dropout_pct }}% Loss</div>
                  </td>
                  <td class="px-6 py-6">
                    <div class="flex items-center gap-1">
                      <div v-for="n in 5" :key="n" :class="['w-1 h-3.5 rounded-full', n <= inst.health ? 'bg-emerald-500' : 'bg-slate-100']"></div>
                    </div>
                  </td>
                  <td class="px-8 py-6 text-right">
                    <button class="text-slate-300 hover:text-emerald-600 transition-all group/btn">
                      <ChevronRightIcon class="h-5 w-5 group-hover/btn:translate-x-1 transition-transform" />
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '../../../Layouts/MainLayout.vue';
import {
  UserGroupIcon, AcademicCapIcon, TrophyIcon, 
  HandThumbUpIcon, ArrowDownCircleIcon, ChartBarIcon,
  ChevronDownIcon, ChevronRightIcon, ClockIcon, LightBulbIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  stats: Object,
  funnel: Array,
  institutions: Array
});

const mainStats = [
  { label: 'Intelligence Pipeline', value: '1.28M', trend: '+12.4% Flux', trendUp: true, pct: 85, color: 'bg-emerald-600/30', icon: UserGroupIcon },
  { label: 'Platform Velocity', value: '78.2%', trend: '+3.1% Velocity', trendUp: true, pct: 78, color: 'bg-teal-600/30', icon: ChartBarIcon },
  { label: 'Credentials Minted', value: '45.2K', trend: '+18.9% Rate', trendUp: true, pct: 45, color: 'bg-emerald-500/30', icon: TrophyIcon },
  { label: 'Knowledge Integrity', value: '92.4%', trend: '-0.2% Drift', trendUp: false, pct: 92, color: 'bg-slate-800/20', icon: LightBulbIcon },
];
</script>

<style scoped>
.shadow-3xl { box-shadow: 0 35px 60px -15px rgba(0, 0, 0, 0.5); }
</style>
