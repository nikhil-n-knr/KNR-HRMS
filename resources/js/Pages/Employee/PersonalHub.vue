<template>
  <Head title="Personal Hub" />
  
  <div class="min-h-screen bg-[#f8fafc] py-8 pb-32">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <!-- Header Section -->
      <div class="mb-12">
        <div class="flex items-center gap-4 mb-2">
            <div class="h-1 bg-emerald-500 w-12 rounded-full"></div>
            <span class="text-sm font-black text-emerald-600 uppercase tracking-[0.3em]">Command Center</span>
        </div>
        <h1 class="text-4xl font-black text-slate-900 tracking-tight">
          Personal <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-500">Hub</span>
        </h1>
        <p class="mt-2 text-slate-500 font-medium">Manage your digital identity, resources, and career footprint.</p>
      </div>

      <!-- Quick Actions Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        
        <!-- Profile Card -->
        <Link :href="auth.profileUrl" class="group relative bg-white rounded-3xl p-6 shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
          <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50 rounded-full -mr-16 -mt-16 group-hover:bg-emerald-100 transition-colors"></div>
          <div class="relative">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-6 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm">
                <i class="fas fa-id-badge text-xl"></i>
            </div>
            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight mb-1">Digital Identity</h3>
            <p class="text-xs text-slate-500 font-bold uppercase tracking-widest leading-none mb-4">Profile & Stats</p>
            <p class="text-sm text-slate-400 leading-relaxed tabular-nums">Review your employment details, bank records, and personal dossier.</p>
            
            <div class="mt-8 flex items-center gap-2 text-emerald-600 font-black text-sm uppercase tracking-wider">
                Explore <i class="fas fa-arrow-right transition-transform group-hover:translate-x-1"></i>
            </div>
          </div>
        </Link>

        <!-- Referrals Card -->
        <Link v-if="route().has('employee.referrals.index')" :href="route('employee.referrals.index')" class="group relative bg-white rounded-3xl p-6 shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
          <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 rounded-full -mr-16 -mt-16 group-hover:bg-indigo-100 transition-colors"></div>
          <div class="relative">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-6 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-sm">
                <i class="fas fa-user-plus text-xl"></i>
            </div>
            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight mb-1">Talent Bridge</h3>
            <p class="text-xs text-slate-500 font-bold uppercase tracking-widest leading-none mb-4">My Referrals</p>
            <p class="text-sm text-slate-400 leading-relaxed">Refer top talent to the organization and track their recruitment milestones.</p>
            
            <div class="mt-8 flex items-center gap-2 text-indigo-600 font-black text-sm uppercase tracking-wider">
                Connect <i class="fas fa-arrow-right transition-transform group-hover:translate-x-1"></i>
            </div>
          </div>
        </Link>

        <!-- Assets Card -->
        <Link :href="route('employee.assets.index')" class="group relative bg-white rounded-3xl p-6 shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
          <div class="absolute top-0 right-0 w-32 h-32 bg-amber-50 rounded-full -mr-16 -mt-16 group-hover:bg-amber-100 transition-colors"></div>
          <div class="relative">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mb-6 group-hover:bg-amber-600 group-hover:text-white transition-all shadow-sm">
                <i class="fas fa-boxes-stacked text-xl"></i>
            </div>
            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight mb-1">Resource Vault</h3>
            <p class="text-xs text-slate-500 font-bold uppercase tracking-widest leading-none mb-4">My Assets</p>
            <p class="text-sm text-slate-400 leading-relaxed">Manage your assigned hardware, software licenses, and resource inventory.</p>
            
            <div class="mt-8 flex items-center gap-2 text-amber-600 font-black text-sm uppercase tracking-wider">
                Inspect <i class="fas fa-arrow-right transition-transform group-hover:translate-x-1"></i>
            </div>
          </div>
        </Link>

        <!-- Requests Card -->
        <Link v-if="route().has('employee.requests.index')" :href="route('employee.requests.index')" class="group relative bg-white rounded-3xl p-6 shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
          <div class="absolute top-0 right-0 w-32 h-32 bg-rose-50 rounded-full -mr-16 -mt-16 group-hover:bg-rose-100 transition-colors"></div>
          <div class="relative">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mb-6 group-hover:bg-rose-600 group-hover:text-white transition-all shadow-sm">
                <i class="fas fa-file-contract text-xl"></i>
            </div>
            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight mb-1">Request Desk</h3>
            <p class="text-xs text-slate-500 font-bold uppercase tracking-widest leading-none mb-4">Forms & Status</p>
            <p class="text-sm text-slate-400 leading-relaxed">Create new service requests and monitor the resolution status of pending items.</p>
            
            <div class="mt-8 flex items-center gap-2 text-rose-600 font-black text-sm uppercase tracking-wider">
                Apply <i class="fas fa-arrow-right transition-transform group-hover:translate-x-1"></i>
            </div>
          </div>
        </Link>

      </div>

      <!-- Secondary Links Section -->
      <div class="bg-white rounded-[40px] p-8 border border-slate-100 shadow-sm">
          <h2 class="text-2xl font-black text-slate-900 mb-8 px-4">Internal <span class="text-emerald-600">Resources</span></h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <Link :href="route('employee.payslips.index')" class="flex items-center gap-4 p-4 rounded-3xl hover:bg-slate-50 transition-all group">
                  <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center group-hover:bg-emerald-50 group-hover:text-emerald-600 transition-colors">
                      <i class="fas fa-receipt"></i>
                  </div>
                  <div class="flex flex-col">
                      <span class="text-sm font-black text-slate-800 uppercase tracking-tight">Finance records</span>
                      <span class="text-sm font-bold text-slate-400 uppercase tracking-widest">Payslips & Tax</span>
                  </div>
              </Link>

              <Link :href="route('leave.dashboard')" class="flex items-center gap-4 p-4 rounded-3xl hover:bg-slate-50 transition-all group">
                  <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-colors">
                      <i class="fas fa-calendar-alt"></i>
                  </div>
                  <div class="flex flex-col">
                      <span class="text-sm font-black text-slate-800 uppercase tracking-tight">Time Off Hub</span>
                      <span class="text-sm font-bold text-slate-400 uppercase tracking-widest">Leaves & holidays</span>
                  </div>
              </Link>

              <Link :href="route('attendance.requests.index')" class="flex items-center gap-4 p-4 rounded-3xl hover:bg-slate-50 transition-all group">
                  <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center group-hover:bg-amber-50 group-hover:text-amber-600 transition-colors">
                      <i class="fas fa-clock-rotate-left"></i>
                  </div>
                  <div class="flex flex-col">
                      <span class="text-sm font-black text-slate-800 uppercase tracking-tight">Timeline Log</span>
                      <span class="text-sm font-bold text-slate-400 uppercase tracking-widest">Attendance Corrections</span>
                  </div>
              </Link>
          </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    employee: Object,
    auth: Object
});
</script>

<style scoped>
.min-h-screen {
    background: radial-gradient(circle at top right, #f0fdf4 0%, #f8fafc 40%);
}
</style>
