<template>
    <AppLayout>
        <div class="px-6 space-y-7 pb-32 pt-4">
            <header>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Decisions</h2>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.3em] mt-1 italic">Pending Authorization Matrix</p>
            </header>

            <div class="space-y-4">
                <div v-if="loading" class="flex justify-center py-10">
                    <div class="w-8 h-8 border-4 border-emerald-500/20 border-t-emerald-500 rounded-full animate-spin"></div>
                </div>
                
                <div v-else-if="items.length === 0" class="nature-card p-10 text-center bg-white/50 border-dashed">
                    <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-emerald-500">
                        <i class="fas fa-check-circle text-2xl"></i>
                    </div>
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Protocol Clear. Zero Pending.</p>
                </div>

                <div 
                    v-for="item in items" 
                    :key="item.id" 
                    class="nature-card p-5 space-y-4 animate-nature-fade"
                >
                    <div class="flex justify-between items-start">
                        <div class="flex items-center gap-3">
                            <!-- Tactical Avatar -->
                            <div class="w-10 h-10 rounded-xl bg-slate-100 p-0.5 border border-slate-200 overflow-hidden group">
                                <img 
                                    v-if="item.employee?.user?.avatar" 
                                    :src="item.employee.user.avatar" 
                                    @error="item.employee.user.avatar = null"
                                    class="w-full h-full rounded-[8px] object-cover"
                                >
                                <div v-else class="w-full h-full rounded-[8px] bg-emerald-500 flex items-center justify-center text-white text-[10px] font-black">
                                    {{ item.employee?.full_name?.charAt(0) || 'E' }}
                                </div>
                            </div>
                            <div>
                                <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight">{{ item.employee?.full_name || 'Operative' }}</h4>
                                <p class="text-[9px] font-black text-emerald-600 uppercase tracking-widest mt-0.5">{{ item.request_type }}</p>
                            </div>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="text-[8px] font-black text-slate-300 uppercase tracking-widest">{{ formatDate(item.created_at) }}</span>
                            <span class="nature-pill mt-1">Pending</span>
                        </div>
                    </div>

                    <div class="p-4 bg-slate-50/50 rounded-2xl border border-white relative">
                        <div class="absolute -left-2 top-4 w-1 h-4 bg-emerald-500 rounded-full"></div>
                        <p class="text-[11px] font-black text-slate-600 leading-relaxed uppercase italic tracking-tight">{{ item.description || item.reason }}</p>
                        
                        <div v-if="item.metadata" class="mt-3 pt-3 border-t border-slate-200/50 grid grid-cols-2 gap-2">
                            <div v-for="(val, key) in item.metadata" :key="key" class="flex flex-col">
                                <span class="text-[7px] font-bold text-slate-400 uppercase tracking-widest">{{ key }}</span>
                                <span class="text-[9px] font-black text-slate-700 uppercase">{{ val }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <button 
                            @click="takeAction(item, 'reject')"
                            class="py-3 rounded-[1.2rem] bg-slate-50 text-slate-400 text-[10px] font-black uppercase tracking-widest border border-slate-100 active:bg-slate-100 transition-all active:scale-95"
                        >
                            Decline
                        </button>
                        <button 
                            @click="takeAction(item, 'approve')"
                            class="py-3 rounded-[1.2rem] bg-emerald-600 text-white text-[10px] font-black uppercase tracking-widest shadow-lg shadow-emerald-500/20 active:bg-emerald-700 transition-all active:scale-95"
                        >
                            Authorize
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AppLayout from '../App.vue';
import axios from 'axios';

const items = ref([]);
const loading = ref(true);

const fetchApprovals = async () => {
    try {
        const response = await axios.get('/api/mobile/v1/approvals');
        items.value = response.data.data || response.data;
    } catch (err) {
        console.error("Failed to fetch approvals:", err);
    } finally {
        loading.value = false;
    }
};

const takeAction = async (item, action) => {
    try {
        const response = await axios.post('/api/mobile/v1/approvals/action', {
            id: item.id,
            action: action,
            message: 'Processed via Mobile App'
        });
        
        if (response.status === 200 || response.data.success) {
            items.value = items.value.filter(i => i.id !== item.id);
        }
    } catch (err) {
        alert("Action failed: " + (err.response?.data?.message || "Unknown error"));
    }
};

const formatDate = (date) => new Date(date).toLocaleDateString([], { month: 'short', day: 'numeric' });

onMounted(fetchApprovals);
</script>
