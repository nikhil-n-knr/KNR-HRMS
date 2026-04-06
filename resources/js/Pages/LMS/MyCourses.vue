<template>
  <MainLayout>
    <Head title="Learning Vector | My Training" />
    
    <div class="min-h-screen bg-slate-50/50 pb-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12">
        <!-- Header -->
        <div class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-6">
          <div>
            <h1 class="text-4xl lg:text-5xl font-black text-slate-800 uppercase tracking-tighter italic leading-none mb-4">Training Portfolio</h1>
            <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] italic">Manage your assigned intellectual vectors and accreditation</p>
          </div>
          <div class="flex items-center gap-2 text-[10px] font-black text-emerald-600 uppercase tracking-widest bg-emerald-50 px-4 py-2 rounded-xl border border-emerald-100">
             <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
             Live Synchronization
          </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-16">
          <div v-for="stat in stats" :key="stat.label" class="bg-white p-8 rounded-[40px] border border-slate-100 shadow-xl shadow-slate-200/20 group hover:scale-[1.02] transition-all">
            <div class="flex items-center gap-5">
              <div :class="['w-14 h-14 rounded-3xl flex items-center justify-center transition-all shadow-inner', stat.bg]">
                <component :is="stat.icon" :class="['h-7 w-7', stat.color]" />
              </div>
              <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ stat.label }}</p>
                <p class="text-3xl font-black text-slate-800 italic">{{ stat.value }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Course Cards -->
        <div class="space-y-8">
          <div
            v-for="assignment in assignments"
            :key="assignment.id"
            class="bg-white rounded-[48px] border border-slate-100 shadow-2xl shadow-slate-200/20 overflow-hidden group hover:shadow-emerald-500/5 transition-all duration-500"
          >
            <div class="p-10 lg:p-12">
              <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-10">
                <div class="flex-1">
                  <div class="flex flex-wrap items-center gap-4 mb-4">
                    <h3 class="text-2xl font-black text-slate-800 uppercase tracking-tighter italic">
                      {{ assignment.course.title }}
                    </h3>
                    <span
                      :class="[
                        'px-4 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest border transition-all',
                        getStatusStyle(assignment.status)
                      ]"
                    >
                      {{ getStatusLabel(assignment.status) }}
                    </span>
                  </div>
                  
                  <p class="text-slate-500 text-sm mb-10 max-w-3xl font-medium italic leading-relaxed">
                    {{ assignment.course.description }}
                  </p>

                  <!-- Progress Info Matrix -->
                  <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div>
                      <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Terminal Node</p>
                      <p class="text-sm font-black text-slate-700 italic">
                        {{ formatDate(assignment.due_date) }}
                      </p>
                    </div>
                    <div>
                      <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Sync Attempts</p>
                      <p class="text-sm font-black text-slate-700 italic">
                        {{ assignment.attempts_count }} / {{ assignment.max_attempts }}
                      </p>
                    </div>
                    <div v-if="assignment.latest_score">
                      <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Cognitive Score</p>
                      <p class="text-sm font-black text-emerald-600 italic">
                        {{ assignment.latest_score }}%
                      </p>
                    </div>
                    <div v-if="assignment.is_passed">
                      <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Validation Status</p>
                      <p class="text-sm font-black text-emerald-600 flex items-center gap-2 italic uppercase">
                        <CheckCircleIcon class="h-4 w-4" />
                        Accredited
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Action Hub -->
                <div class="shrink-0 flex items-center">
                  <button
                    v-if="assignment.can_attempt?.can_attempt"
                    @click="startCourse(assignment.course.id)"
                    class="w-full lg:w-auto bg-slate-800 hover:bg-black text-white text-[11px] font-black uppercase tracking-[0.2em] px-12 py-5 rounded-[32px] shadow-2xl shadow-slate-900/10 transition-all flex items-center justify-center gap-3 active:scale-95 group/btn"
                  >
                    <PlayIcon class="h-5 w-5 fill-emerald-500 transition-transform group-hover/btn:translate-x-1" />
                    {{ assignment.status === 'pending' ? 'Initialize Vector' : 'Resume Uplink' }}
                  </button>
                  
                  <div v-else class="bg-slate-50 border border-slate-100 px-8 py-4 rounded-3xl text-[10px] font-black text-slate-400 uppercase tracking-widest italic">
                    {{ assignment.can_attempt?.reason || 'Access Locked' }}
                  </div>
                </div>
              </div>

              <!-- Progress Bar -->
              <div v-if="assignment.status === 'in_progress'" class="mt-12 pt-10 border-t border-slate-50">
                <div class="flex justify-between text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3 italic">
                  <span>Usage Index</span>
                  <span class="text-emerald-600">{{ Math.round((assignment.attempts_count / assignment.max_attempts) * 100) }}% Delta</span>
                </div>
                <div class="w-full bg-slate-50 rounded-full h-3 border border-slate-100 p-0.5">
                  <div
                    class="bg-emerald-600 h-full rounded-full transition-all duration-1000 shadow-[0_0_12px_rgba(16,185,129,0.3)]"
                    :style="{ width: `${(assignment.attempts_count / assignment.max_attempts) * 100}%` }"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="!assignments.length" class="text-center py-32 bg-white rounded-[60px] border border-dashed border-slate-200 mt-12">
          <AcademicCapIcon class="mx-auto h-20 w-20 text-slate-100 mb-6" />
          <h3 class="text-xl font-black text-slate-800 uppercase tracking-tighter italic">No Active Vectors</h3>
          <p class="text-slate-400 text-sm mt-3 font-medium italic">Your training archive is currently synchronization-free.</p>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import MainLayout from '../../Layouts/MainLayout.vue';
import {
  AcademicCapIcon,
  CheckCircleIcon,
  ClockIcon,
  ExclamationTriangleIcon,
  PlayIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  assignments: Array
});

const stats = computed(() => [
  { 
    label: 'Total Clusters', 
    value: props.assignments.length, 
    icon: AcademicCapIcon, 
    bg: 'bg-slate-50 border border-slate-100', 
    color: 'text-slate-400' 
  },
  { 
    label: 'Accredited', 
    value: props.assignments.filter(a => a.status === 'completed').length, 
    icon: CheckCircleIcon, 
    bg: 'bg-emerald-50 border border-emerald-100', 
    color: 'text-emerald-600' 
  },
  { 
    label: 'Uplink Active', 
    value: props.assignments.filter(a => a.status === 'in_progress').length, 
    icon: ClockIcon, 
    bg: 'bg-blue-50 border border-blue-100', 
    color: 'text-blue-600' 
  },
  { 
    label: 'Overdue Sync', 
    value: props.assignments.filter(a => a.status === 'overdue').length, 
    icon: ExclamationTriangleIcon, 
    bg: 'bg-rose-50 border border-rose-100', 
    color: 'text-rose-600' 
  },
]);

const getStatusStyle = (status) => {
  const styles = {
    pending: 'bg-slate-50 text-slate-400 border-slate-100',
    in_progress: 'bg-blue-50 text-blue-600 border-blue-100',
    completed: 'bg-emerald-50 text-emerald-600 border-emerald-100',
    overdue: 'bg-rose-50 text-rose-600 border-rose-100'
  };
  return styles[status] || styles.pending;
};

const getStatusLabel = (status) => {
  const labels = {
    pending: 'Origin',
    in_progress: 'Active Transmission',
    completed: 'Fully Synchronized',
    overdue: 'Deadline Violation'
  };
  return labels[status] || status;
};

const formatDate = (date) => {
  if (!date) return 'Unconstrained';
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  }).toUpperCase();
};

const startCourse = (courseId) => {
  router.visit(route('lms.learn.course.detail', courseId));
};
</script>
