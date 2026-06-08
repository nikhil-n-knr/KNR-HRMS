<template>
  <Head title="Live Attendance Monitor" />

  <div class="p-8 space-y-8 animate-in fade-in duration-700">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
            <RadioIcon class="w-8 h-8 text-rose-500 animate-pulse" />
            Live Monitor
        </h1>
        <p class="text-slate-500 font-medium mt-1">Real-time presence tracking for {{ today }}.</p>
      </div>
      
      <div class="flex items-center gap-3">
        <div class="px-4 py-2 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-100 flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
            System Online
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm relative overflow-hidden group hover:shadow-xl hover:shadow-indigo-500/5 transition-all">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-slate-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
        <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Total Strength</div>
        <div class="text-4xl font-black text-slate-900">{{ stats.total_employees }}</div>
        <div class="mt-4 text-xs font-bold text-slate-400">Registered active users</div>
      </div>

      <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm relative overflow-hidden group hover:shadow-xl hover:shadow-emerald-500/5 transition-all">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
        <div class="text-[10px] font-black text-emerald-600 uppercase tracking-[0.2em] mb-4">Clocked In</div>
        <div class="text-4xl font-black text-slate-900">{{ stats.present }}</div>
        <div class="mt-4 text-xs font-bold text-emerald-500 flex items-center gap-1">
            <TrendingUpIcon class="w-3 h-3" /> {{ Math.round((stats.present / stats.total_employees) * 100) }}% Attendance
        </div>
      </div>

      <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm relative overflow-hidden group hover:shadow-xl hover:shadow-amber-500/5 transition-all">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-amber-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
        <div class="text-[10px] font-black text-amber-600 uppercase tracking-[0.2em] mb-4">Late Entries</div>
        <div class="text-4xl font-black text-slate-900">{{ stats.late }}</div>
        <div class="mt-4 text-xs font-bold text-amber-500">Requires review</div>
      </div>

      <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm relative overflow-hidden group hover:shadow-xl hover:shadow-rose-500/5 transition-all">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-rose-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
        <div class="text-[10px] font-black text-rose-600 uppercase tracking-[0.2em] mb-4">Currently Out</div>
        <div class="text-4xl font-black text-slate-900">{{ stats.absent }}</div>
        <div class="mt-4 text-xs font-bold text-rose-500">Pending check-in</div>
      </div>
    </div>

    <!-- Live Feed -->
    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
      <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
        <h2 class="text-lg font-black text-slate-900 tracking-tight">Today's Presence Stream</h2>
        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
            Auto-refreshing <span class="w-1.5 h-1.5 rounded-full bg-slate-300 animate-pulse"></span>
        </div>
      </div>

      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50/10">
            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Employee</th>
            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">First In</th>
            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Department</th>
            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Activity</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          <tr v-for="log in logs.data" :key="log.id" class="group hover:bg-slate-50/50 transition-colors">
            <td class="px-8 py-5">
              <div class="flex items-center gap-3">
                <div class="relative">
                  <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 font-black text-xs overflow-hidden">
                    <img v-if="log.employee.avatar_url" :src="log.employee.avatar_url" class="w-full h-full object-cover" />
                    <span v-else>{{ log.employee.first_name[0] }}{{ log.employee.last_name[0] }}</span>
                  </div>
                  <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 border-2 border-white rounded-full"></div>
                </div>
                <div>
                  <div class="text-sm font-bold text-slate-900">{{ log.employee.first_name }} {{ log.employee.last_name }}</div>
                  <div class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">{{ log.employee.employee_code }}</div>
                </div>
              </div>
            </td>
            <td class="px-8 py-5">
              <div class="flex items-center gap-2">
                <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                    <LogInIcon class="w-3.5 h-3.5" />
                </div>
                <div class="text-sm font-black text-slate-700">{{ getFirstIn(log) }}</div>
              </div>
            </td>
            <td class="px-8 py-5">
              <span 
                class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border"
                :class="{
                  'bg-emerald-50 text-emerald-600 border-emerald-100': log.status === 'Present',
                  'bg-amber-50 text-amber-600 border-amber-100': log.status === 'Late',
                  'bg-indigo-50 text-indigo-600 border-indigo-100': log.status === 'Work From Home',
                }"
              >
                {{ log.status }}
              </span>
            </td>
            <td class="px-8 py-5">
                <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest">{{ log.employee.department?.name || 'Operations' }}</div>
            </td>
            <td class="px-8 py-5 text-right">
                <div class="flex items-center justify-end gap-1">
                    <div v-for="i in 5" :key="i" class="w-1.5 h-3 rounded-full bg-emerald-100" :class="{'bg-emerald-500': i < 4}"></div>
                </div>
            </td>
          </tr>
          <tr v-if="logs.data.length === 0">
            <td colspan="5" class="px-8 py-20 text-center">
              <div class="w-16 h-16 bg-slate-50 rounded-3xl mx-auto flex items-center justify-center mb-4">
                <ActivityIcon class="w-8 h-8 text-slate-300 animate-pulse" />
              </div>
              <div class="text-slate-400 font-bold tracking-tight">Waiting for first check-ins...</div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="logs.links.length > 3" class="px-8 py-6 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">
            Streamed logs: {{ logs.total }}
        </div>
        <div class="flex items-center gap-1">
            <Link 
                v-for="(link, i) in logs.links" 
                :key="i"
                :href="link.url || '#'"
                v-html="link.label"
                class="px-4 py-2 rounded-xl text-xs font-black transition-all"
                :class="{
                    'bg-slate-900 text-white shadow-lg shadow-slate-200': link.active,
                    'text-slate-400 hover:bg-slate-100': !link.active && link.url,
                    'opacity-30 cursor-not-allowed': !link.url
                }"
            />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
  RadioIcon, LogInIcon, ActivityIcon, TrendingUpIcon,
  SearchIcon, UserIcon, MoreVerticalIcon
} from 'lucide-vue-next';
import dayjs from 'dayjs';

defineOptions({ layout: MainLayout });

const props = defineProps({
  logs: Object,
  stats: Object,
  today: String
});

const getFirstIn = (log) => {
  if (log.sessions && log.sessions.length > 0) {
    const minTime = log.sessions.reduce((min, s) => {
        return (new Date(s.in_time) < new Date(min)) ? s.in_time : min;
    }, log.sessions[0].in_time);
    return dayjs(minTime).format('HH:mm');
  }
  return '--:--';
};
</script>
