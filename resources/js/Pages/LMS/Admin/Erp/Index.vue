<template>
  <MainLayout>
    <Head title="ERP Integration Hub" />

    <div class="min-h-screen bg-slate-50/50 p-4 sm:p-8">
      <!-- Header -->
      <div class="max-w-7xl mx-auto mb-10">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div>
            <div class="flex items-center gap-3 mb-2">
              <span class="bg-emerald-600/10 text-emerald-600 text-[10px] font-black uppercase tracking-[0.2em] px-2 py-0.5 rounded border border-emerald-500/30">LMS Internal Data-Bus</span>
              <span class="text-slate-400 text-[10px] font-bold uppercase tracking-widest">Active Link</span>
            </div>
            <h1 class="text-4xl font-black text-slate-800 tracking-tight uppercase italic">ERP <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-600 to-teal-500 whitespace-nowrap">Integration</span></h1>
            <p class="text-slate-500 text-sm mt-1 font-medium italic">Synchronizing academic clusters with institutional ERP records.</p>
          </div>

          <div class="flex items-center gap-3">
             <div class="bg-white border border-slate-200 rounded-2xl px-6 py-3 shadow-sm">
                <p class="text-[9px] text-slate-400 uppercase font-black tracking-widest mb-1 text-right">Total Synced</p>
                <p class="text-xl font-black text-slate-800 text-right">{{ stats.total_synced.toLocaleString() }}</p>
             </div>
             <button @click="openSyncModal" class="bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-black uppercase tracking-widest px-8 py-4 rounded-2xl transition-all shadow-lg shadow-emerald-500/20 active:scale-95 flex items-center gap-2">
                <ArrowPathIcon class="h-4 w-4" />
                Initiate Handshake
             </button>
          </div>
        </div>
      </div>

      <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main: Sync Activity -->
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white rounded-[32px] border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between">
               <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest flex items-center gap-2">
                 <ListBulletIcon class="h-4 w-4 text-emerald-500" />
                 Transmission Logs
               </h3>
            </div>
            
            <div class="overflow-x-auto">
               <table class="w-full text-left">
                 <thead>
                   <tr class="bg-slate-50 text-[10px] text-slate-400 uppercase font-black border-b border-slate-100">
                     <th class="px-8 py-4">Institution</th>
                     <th class="px-6 py-4">Entity</th>
                     <th class="px-6 py-4 text-center">Status</th>
                     <th class="px-6 py-4 text-right">Payload</th>
                     <th class="px-8 py-4 text-right">Timestamp</th>
                   </tr>
                 </thead>
                 <tbody class="divide-y divide-slate-100 italic font-medium">
                   <tr v-for="log in sync_logs" :key="log.id" class="hover:bg-slate-50/50 transition-colors">
                     <td class="px-8 py-5">
                       <span class="text-xs font-black text-slate-700 uppercase tracking-tighter">{{ log.institution?.name || 'Global' }}</span>
                     </td>
                     <td class="px-6 py-5">
                       <span class="text-[10px] font-black text-slate-400 uppercase bg-slate-100 px-2 py-1 rounded">{{ log.entity_type }}</span>
                     </td>
                     <td class="px-6 py-5 text-center">
                        <span :class="['text-[9px] font-black uppercase tracking-widest px-2.5 py-1 rounded-full', log.status === 'completed' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100']">
                          {{ log.status }}
                        </span>
                     </td>
                     <td class="px-6 py-5 text-right font-black text-slate-800 text-xs">
                       {{ log.records_processed }}
                     </td>
                     <td class="px-8 py-5 text-right text-[10px] text-slate-400">
                       {{ formatDate(log.created_at) }}
                     </td>
                   </tr>
                 </tbody>
               </table>
            </div>
          </div>
        </div>

        <!-- Sidebar: Configurations -->
        <div class="space-y-6">
           <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-[32px] p-8 text-white shadow-xl shadow-slate-900/10">
              <h3 class="text-xs font-black uppercase tracking-[0.2em] text-emerald-400 mb-6 flex items-center gap-2">
                <ShieldCheckIcon class="h-4 w-4" />
                Connectivity
              </h3>
              <div class="space-y-4">
                 <div class="p-4 bg-white/5 rounded-2xl border border-white/5 flex items-center justify-between">
                    <div>
                      <p class="text-[10px] font-black uppercase text-slate-400 mb-1">Last System Sync</p>
                      <p class="text-sm font-black">{{ stats.last_sync ? formatDate(stats.last_sync) : 'Never' }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/20 flex items-center justify-center">
                       <CloudArrowUpIcon class="h-5 w-5 text-emerald-400" />
                    </div>
                 </div>
                 <div class="p-4 bg-white/5 rounded-2xl border border-white/5 flex items-center justify-between">
                    <div>
                      <p class="text-[10px] font-black uppercase text-slate-400 mb-1">Failure Drift</p>
                      <p class="text-sm font-black text-rose-400">{{ stats.fail_count }} Instances</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-rose-500/20 flex items-center justify-center">
                       <ExclamationTriangleIcon class="h-5 w-5 text-rose-400" />
                    </div>
                 </div>
              </div>
           </div>

           <div class="bg-white rounded-[32px] border border-slate-200 p-8 shadow-sm">
             <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-6">Service Health</h3>
             <div class="space-y-4">
                <div v-for="i in 3" :key="i" class="flex items-center justify-between">
                   <div class="flex items-center gap-3">
                      <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                      <span class="text-[10px] font-black text-slate-500 uppercase">Gateway Node {{ i }}</span>
                   </div>
                   <span class="text-[10px] font-black text-emerald-600 uppercase">Online</span>
                </div>
             </div>
           </div>
        </div>
      </div>
    </div>

    <!-- Sync Trigger Modal -->
    <TransitionRoot appear :show="isModalOpen" as="template">
      <Dialog as="div" @close="isModalOpen = false" class="relative z-[100]">
        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-slate-900/20 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4 text-center">
            <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
              <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-[32px] bg-white p-10 text-left align-middle shadow-2xl transition-all border border-slate-100">
                <DialogTitle as="h3" class="text-xl font-black text-slate-800 uppercase tracking-tighter italic mb-2">
                  Initiate <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-600 to-teal-600">Sync Pipeline</span>
                </DialogTitle>
                <p class="text-xs text-slate-500 font-medium mb-8 italic">Triggering a manual handshake with the state-level institution ERP.</p>

                <form @submit.prevent="submitSync" class="space-y-6">
                  <div>
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2 block px-1">Institutional Cluster</label>
                    <select v-model="form.institution_id" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3.5 text-sm font-black text-slate-700 outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all">
                      <option v-for="inst in institutions" :key="inst.id" :value="inst.id">{{ inst.name }}</option>
                    </select>
                  </div>

                  <div>
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2 block px-1">Entity Mapping</label>
                    <div class="grid grid-cols-3 gap-2">
                      <button v-for="ent in ['user', 'enrollment', 'grade']" :key="ent" type="button"
                        @click="form.entity = ent"
                        :class="['py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all border', 
                                form.entity === ent ? 'bg-emerald-600 text-white border-emerald-600 shadow-md shadow-emerald-500/20' : 'bg-slate-50 text-slate-400 border-slate-100 hover:bg-white hover:border-emerald-200']">
                        {{ ent }}
                      </button>
                    </div>
                  </div>

                  <div class="pt-4 flex gap-3">
                    <button type="button" @click="isModalOpen = false" class="flex-1 py-4 text-[10px] font-black uppercase text-slate-400 hover:text-slate-600 transition-colors">Abort</button>
                    <button type="submit" :disabled="form.processing" class="flex-[2] bg-slate-800 text-white text-[10px] font-black uppercase tracking-[0.2em] py-4 rounded-2xl hover:bg-black transition-all active:scale-95 disabled:opacity-50">
                      Execute Transmission
                    </button>
                  </div>
                </form>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </MainLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import MainLayout from '../../../../Layouts/MainLayout.vue';
import { 
  Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot 
} from '@headlessui/vue';
import {
  ArrowPathIcon, ListBulletIcon, CloudArrowUpIcon, 
  ShieldCheckIcon, ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  sync_logs:    Array,
  institutions: Array,
  stats:        Object
});

const isModalOpen = ref(false);

const form = useForm({
  institution_id: '',
  entity: 'user'
});

const openSyncModal = () => {
  if (props.institutions.length > 0) {
    form.institution_id = props.institutions[0].id;
  }
  isModalOpen.value = true;
};

const submitSync = () => {
  form.post(route('lms.admin.erp.sync'), {
    onSuccess: () => {
      isModalOpen.value = false;
    }
  });
};

const formatDate = (date) => {
  return new Date(date).toLocaleString('en-IN', {
    day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit'
  });
};
</script>
