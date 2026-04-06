<script setup>
import { ref, computed } from 'vue';
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';

const props = defineProps(['show']);
const emit = defineEmits(['close', 'success']);
const toast = useToastStore();

const step = ref(1); // 1: Upload, 2: Preview
const file = ref(null);
const loading = ref(false);
const previewData = ref([]);
const errors = ref([]);

const handleFileUpload = (event) => {
    file.value = event.target.files[0];
};

const verifyFile = async () => {
    if (!file.value) return;
    loading.value = true;
    
    const formData = new FormData();
    formData.append('file', file.value);

    try {
        const res = await axios.post(route('admin.attendance.bulk-shifts.verify'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        previewData.value = res.data.preview;
        errors.value = res.data.errors;
        step.value = 2;
    } catch (e) {
        toast.error("Failed to verify file. Ensure it is a valid CSV.");
        console.error(e);
    } finally {
        loading.value = false;
    }
};

const executeImport = async () => {
    loading.value = true;
    try {
        const res = await axios.post(route('admin.attendance.bulk-shifts.execute'), {
            rows: previewData.value
        });
        toast.success(res.data.message);
        emit('success');
        close();
    } catch (e) {
        toast.error("Import failed.");
        console.error(e);
    } finally {
        loading.value = false;
    }
};

const close = () => {
    step.value = 1;
    file.value = null;
    previewData.value = [];
    errors.value = [];
    emit('close');
};

const hasErrors = computed(() => errors.value.length > 0);
</script>

<template>
    <TransitionRoot as="template" :show="show">
    <Dialog as="div" class="relative z-50" @close="close">
      <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            <DialogPanel class="relative transform overflow-hidden rounded-[48px] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-white">
              
              <!-- Premium Header -->
              <div class="p-10 border-b border-slate-50 flex justify-between items-center bg-gradient-to-r from-slate-50 to-transparent">
                  <div class="flex items-center gap-6">
                      <div class="w-16 h-16 rounded-[22px] flex items-center justify-center text-white shadow-xl rotate-3 group-hover:rotate-0 transition-transform" :class="step === 1 ? 'bg-gradient-to-br from-indigo-500 to-violet-600 shadow-indigo-200' : 'bg-gradient-to-br from-emerald-500 to-teal-600 shadow-emerald-200'">
                          <i class="fas" :class="step === 1 ? 'fa-file-import text-2xl' : 'fa-clipboard-check text-2xl'"></i>
                      </div>
                      <div>
                          <h3 class="text-2xl font-black text-slate-800 tracking-tighter uppercase leading-none">
                            {{ step === 1 ? 'Shift_Import_Initiation' : 'Matrix_Verification' }}
                          </h3>
                          <p class="text-sm font-black text-slate-400 uppercase tracking-[0.4em] mt-3 block leading-none ml-1">
                            {{ step === 1 ? 'Standard Operating Protocol 714' : 'Temporal Alignment Check' }}
                          </p>
                      </div>
                  </div>
                  <button @click="close" class="w-12 h-12 rounded-2xl bg-slate-100/50 text-slate-400 flex items-center justify-center hover:bg-rose-50 hover:text-rose-500 transition-all active:scale-90">
                      <i class="fas fa-times text-lg"></i>
                  </button>
              </div>

              <div class="p-10">
                  <!-- Step 1: Upload -->
                  <div v-if="step === 1" class="space-y-8">
                    <div class="bg-indigo-50/50 border border-indigo-100 rounded-[32px] p-8 flex items-start gap-6">
                        <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-indigo-500 shadow-sm shrink-0 mt-1">
                            <i class="fas fa-circle-info"></i>
                        </div>
                        <p class="text-base font-black text-slate-600 uppercase tracking-widest leading-relaxed">
                            Upload a CSV file containing the following data schema: <br/>
                            <span class="text-indigo-600">EMPLOYEE_ID</span>, <span class="text-indigo-600">DATE (YYYY-MM-DD)</span>, <span class="text-indigo-600">SHIFT_NAME</span>.
                        </p>
                    </div>
                    
                    <div class="relative group cursor-pointer">
                        <input id="file-upload" type="file" class="sr-only" accept=".csv" @change="handleFileUpload">
                        <label for="file-upload" class="flex flex-col items-center justify-center rounded-[40px] border-4 border-dashed border-slate-100 bg-slate-50/50 px-10 py-16 hover:bg-white hover:border-indigo-200 transition-all cursor-pointer group-focus-within:ring-4 group-focus-within:ring-indigo-500/10">
                            <div class="w-20 h-20 bg-white rounded-[30px] shadow-xl flex items-center justify-center text-slate-300 group-hover:text-indigo-500 transition-colors mb-6 group-hover:scale-110 transition-transform">
                                <i class="fas fa-cloud-arrow-up text-3xl"></i>
                            </div>
                            <div class="text-center">
                                <span class="text-sm font-black uppercase tracking-[0.3em] text-slate-400 group-hover:text-indigo-600">Select csv protocol file</span>
                                <p class="text-sm font-black text-slate-300 uppercase tracking-widest mt-2">Maximum payload: 10MB</p>
                            </div>
                            <div v-if="file" class="mt-8 px-6 py-2 bg-slate-900 text-white rounded-full text-sm font-black tracking-widest animate-fade-in">
                                {{ file.name.toUpperCase() }}
                            </div>
                        </label>
                    </div>
                  </div>

                  <!-- Step 2: Preview -->
                  <div v-if="step === 2" class="space-y-8 animate-fade-in">
                     <div v-if="hasErrors" class="bg-rose-50 p-8 rounded-[32px] border border-rose-100 shadow-sm">
                        <h4 class="text-sm font-black text-rose-600 uppercase tracking-[.2em] flex items-center gap-3 mb-6">
                             <i class="fas fa-triangle-exclamation text-xs"></i>
                             Schema Conflict: {{ errors.length }} Discrepancies Detected
                        </h4>
                        <div class="bg-white/50 rounded-2xl p-4 max-h-40 overflow-y-auto custom-scrollbar border border-rose-100/50 font-mono text-sm font-black text-rose-500 space-y-2">
                            <div v-for="(err, idx) in errors" :key="idx" class="flex gap-4">
                                <span class="opacity-50 min-w-[60px] uppercase">ROW_{{ err.row }}</span>
                                <span class="uppercase tracking-tighter">{{ err.errors.join(' | ') }}</span>
                            </div>
                        </div>
                     </div>

                     <div class="bg-slate-50/50 rounded-[32px] border border-slate-100 overflow-hidden">
                         <div class="p-6 border-b border-slate-100 bg-white/50 flex items-center justify-between">
                             <span class="text-sm font-black text-slate-400 uppercase tracking-widest">Temporal alignment preview</span>
                             <span class="text-sm font-black text-indigo-500 uppercase tracking-widest px-3 py-1 bg-indigo-50 rounded-full border border-indigo-100">{{ previewData.length }} RECORDS</span>
                         </div>
                         <div class="max-h-72 overflow-y-auto custom-scrollbar">
                             <table class="w-full text-left">
                                 <thead class="sticky top-0 bg-white/90 backdrop-blur-md z-10 border-b border-slate-50">
                                     <tr>
                                         <th class="p-6 text-sm font-black text-slate-400 uppercase tracking-widest">Temporal_Marker</th>
                                         <th class="p-6 text-sm font-black text-slate-400 uppercase tracking-widest">Operative</th>
                                         <th class="p-6 text-sm font-black text-slate-400 uppercase tracking-widest">Target_Shift</th>
                                     </tr>
                                 </thead>
                                 <tbody class="divide-y divide-slate-100">
                                     <tr v-for="(row, idx) in previewData" :key="idx" class="hover:bg-white transition-colors">
                                         <td class="px-6 py-4 text-sm font-black text-slate-600 font-mono tracking-tighter">{{ row.date }}</td>
                                         <td class="px-6 py-4 text-sm font-black text-slate-700 tracking-tight uppercase">{{ row.employee_name }}</td>
                                         <td class="px-6 py-4">
                                             <span class="text-sm font-black text-indigo-500 px-3 py-1 bg-white border border-indigo-50 rounded-lg uppercase tracking-widest">{{ row.shift_name }}</span>
                                         </td>
                                     </tr>
                                     <tr v-if="previewData.length === 0">
                                         <td colspan="3" class="p-20 text-center text-sm font-black text-slate-300 uppercase tracking-widest">Zero valid segments found in buffer</td>
                                     </tr>
                                 </tbody>
                             </table>
                         </div>
                     </div>
                  </div>

                  <!-- Action Controls -->
                  <div class="mt-12 flex flex-col sm:flex-row-reverse gap-4 border-t border-slate-50 pt-10">
                    <button v-if="step === 1" @click="verifyFile" :disabled="!file || loading" type="button" class="flex-1 py-5 bg-slate-900 text-white rounded-[24px] text-sm font-black uppercase tracking-[0.3em] shadow-2xl shadow-slate-900/20 hover:scale-[1.02] active:scale-95 transition-all disabled:opacity-50 disabled:scale-100 flex items-center justify-center gap-4">
                        <i v-if="loading" class="fas fa-sync fa-spin"></i>
                        <span>{{ loading ? 'ENCODING_PROTOCOL...' : 'EXECUTE_VERIFICATION' }}</span>
                    </button>
                    <button v-if="step === 2" @click="executeImport" :disabled="previewData.length === 0 || loading" type="button" class="flex-1 py-5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-[24px] text-sm font-black uppercase tracking-[0.3em] shadow-2xl shadow-emerald-500/30 hover:scale-[1.02] active:scale-95 transition-all disabled:opacity-50 disabled:scale-100 flex items-center justify-center gap-4">
                        <i v-if="loading" class="fas fa-sync fa-spin"></i>
                        <span>{{ loading ? 'SYNCHRONIZING...' : `COMMIT_DEPLOIMENT (${previewData.length})` }}</span>
                    </button>
                    <button @click="close" type="button" class="flex-1 py-5 text-sm font-black text-slate-400 hover:text-slate-600 uppercase tracking-[0.3em] transition-all">ABORT_PROTOCOL</button>
                  </div>
              </div>

            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}

.animate-fade-in {
    animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
