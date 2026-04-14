<template>
    <div v-if="show" class="fixed inset-0 z-[60] overflow-hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>
        
        <div class="absolute right-0 top-0 bottom-0 w-full md:w-[450px] bg-white shadow-2xl animate-slide-left flex flex-col">
            <!-- Header -->
            <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="text-sm font-black text-slate-800 uppercase tracking-tight">Governance Change History</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Audit Trail & Reliability Log</p>
                </div>
                <button @click="$emit('close')" class="w-10 h-10 rounded-full hover:bg-white text-slate-400 hover:text-slate-800 transition-all flex items-center justify-center border border-transparent hover:border-slate-100">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-6 space-y-8 custom-scrollbar">
                <div v-if="loading" class="flex flex-col items-center justify-center h-full space-y-4">
                    <div class="w-10 h-10 border-4 border-emerald-500/10 border-t-emerald-500 rounded-full animate-spin"></div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Fetching Artifacts...</p>
                </div>

                <div v-else-if="logs.length === 0" class="flex flex-col items-center justify-center h-full text-center space-y-4">
                     <div class="w-16 h-16 bg-slate-50 rounded-3xl flex items-center justify-center text-slate-200 border border-slate-100">
                        <i class="fas fa-history text-3xl"></i>
                     </div>
                     <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">No manual overrides recorded.</p>
                </div>

                <div v-else class="relative space-y-8">
                    <!-- Vertical Line -->
                    <div class="absolute left-4 top-2 bottom-2 w-px bg-slate-100"></div>

                    <div v-for="log in logs" :key="log.id" class="relative pl-10 group">
                        <!-- Connector Dot -->
                        <div class="absolute left-[13px] top-1.5 w-2.5 h-2.5 rounded-full border-2 border-white bg-emerald-500 z-10 group-hover:scale-125 transition-transform shadow-[0_0_8px_rgba(16,185,129,0.4)]"></div>

                        <div class="bg-slate-50 hover:bg-white border border-slate-100 p-4 rounded-2xl transition-all duration-300 group-hover:shadow-md cursor-default">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-[10px] font-black text-slate-600 shadow-sm">
                                        {{ log.user?.name?.[0] }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-black text-slate-800 leading-none">{{ log.user?.name }}</span>
                                        <span class="text-[8px] font-bold text-slate-400 uppercase tracking-tighter mt-1">{{ formatDate(log.created_at) }}</span>
                                    </div>
                                </div>
                                <span class="text-[8px] font-black uppercase px-2 py-1 bg-emerald-100 text-emerald-700 rounded-lg">Override</span>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-center gap-6">
                                    <div class="flex flex-col">
                                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Old State</span>
                                        <span class="text-xs font-black text-slate-400 tracking-tight">{{ log.old_value }}%</span>
                                    </div>
                                    <div class="text-slate-300">
                                        <i class="fas fa-long-arrow-alt-right"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[8px] font-black text-emerald-500 uppercase tracking-widest leading-none mb-1">New State</span>
                                        <span class="text-xs font-black text-emerald-600 tracking-tight">{{ log.new_value }}%</span>
                                    </div>
                                </div>

                                <div class="bg-white/60 p-3 rounded-xl border border-slate-200">
                                     <p class="text-[10px] text-slate-600 font-bold leading-relaxed italic">
                                        "{{ log.reason || 'Manual recalculation for sprint precision.' }}"
                                     </p>
                                </div>
                                
                                <div class="flex items-center justify-between text-[8px] font-black text-slate-400 uppercase tracking-widest">
                                    <span>IP: {{ log.ip_address }}</span>
                                    <span>Verified Audit Log</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-6 bg-slate-900 border-t border-slate-800 text-white rounded-t-[40px] shadow-2xl relative overflow-hidden group">
                 <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-emerald-500/20 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-1000"></div>
                 <div class="relative z-10 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 flex items-center justify-center border border-emerald-500/30 text-emerald-400">
                        <i class="fas fa-user-shield text-xl"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-black uppercase tracking-widest">Compliance Active</h4>
                        <p class="text-[9px] text-white/50 font-bold uppercase tracking-tighter mt-1 leading-tight">
                             Every deviation is logged with cryptographic timestamps for HR & Governance Audits.
                        </p>
                    </div>
                 </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps(['show', 'projectId']);
defineEmits(['close']);

const logs = ref([]);
const loading = ref(false);

const fetchLogs = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('projects.portal.governance.history', { project: props.projectId }));
        logs.value = response.data;
    } catch (e) {
        console.error('Failed to fetch governance logs', e);
    } finally {
        loading.value = false;
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleString('en-GB', { 
        day: '2-digit', 
        month: 'short', 
        hour: '2-digit', 
        minute: '2-digit' 
    });
};

onMounted(() => {
    if (props.projectId) fetchLogs();
});
</script>

<style scoped>
.animate-slide-left {
    animation: slideLeft 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideLeft {
    from { transform: translateX(100%); }
    to { transform: translateX(0); }
}

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
</style>
