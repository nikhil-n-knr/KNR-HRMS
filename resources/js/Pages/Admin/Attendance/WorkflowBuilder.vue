<template>
  <component :is="embedded ? 'div' : AttendanceLayout" title="Workflows" activeTab="workflows" v-bind="$props">

    <Head title="Workflows" />
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between bg-white p-3 rounded-xl border border-slate-200 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-600 rounded-lg flex items-center justify-center text-white shadow-sm">
                <i class="fas fa-project-diagram text-sm"></i>
            </div>
            <div>
                <h1 class="text-sm font-bold text-slate-800 tracking-tight leading-none uppercase">Workflow Architect</h1>
                <p class="text-sm text-slate-400 font-bold uppercase tracking-widest mt-1.5 leading-none">Approval Protocols</p>
            </div>
        </div>
      </div>

      <!-- Empty State / Init -->
      <div v-if="needsInit && workflows.length === 0" class="bg-white p-12 rounded-xl border border-slate-200 shadow-sm text-center max-w-2xl mx-auto">
          <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-magic text-2xl"></i>
          </div>
          <h3 class="text-base font-bold text-slate-800 uppercase tracking-tight">No Workflows Initialized</h3>
          <p class="text-base text-slate-500 max-w-xs mx-auto mt-2 font-medium">Initialize the system with standard approval flows for procedural synchronization.</p>
          <button @click="initDefaults" class="mt-8 px-8 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-bold text-sm uppercase tracking-widest transition-all shadow-lg shadow-emerald-500/10">
              Initialize Protocols
          </button>
      </div>

      <!-- Workflow List -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div v-for="flow in workflows" :key="flow.id" class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex justify-between items-start group hover:shadow-md transition-all">
              <div class="space-y-3">
                  <div>
                      <h3 class="font-bold text-slate-800 text-sm uppercase tracking-tight group-hover:text-emerald-600 transition-colors">{{ flow.name }}</h3>
                      <p class="text-sm text-slate-400 font-bold uppercase tracking-widest mt-1">{{ flow.description }}</p>
                  </div>
                  <div class="flex flex-wrap gap-1.5 pt-2 border-t border-slate-50">
                      <div v-for="(stage, idx) in flow.stages" :key="stage.id" class="text-sm font-bold bg-slate-50 text-slate-500 border border-slate-100 px-2.5 py-1 rounded uppercase tracking-tighter">
                          {{ idx + 1 }}. {{ stage.name }}
                      </div>
                  </div>
              </div>
              <div class="flex flex-col items-end gap-3">
                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 text-xs font-bold uppercase tracking-widest rounded border border-emerald-100 shadow-sm">Active</span>
                <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-300">
                    <i class="fas fa-route text-sm"></i>
                </div>
              </div>
          </div>
      </div>
    </div>
  </component>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';

const props = defineProps({
    embedded: Boolean,
    workflows: Array,
    needsInit: Boolean
});

const initDefaults = () => {
    if(confirm("Create default workflows?")) {
        router.post(route('admin.workflows.init'));
    }
};
</script>
