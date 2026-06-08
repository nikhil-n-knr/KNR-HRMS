<template>
    <AppLayout v-bind="$props">
        <div class="space-y-6 pb-24 pt-2">
            <header class="px-2 flex justify-between items-end">
                <div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight">Temporal Hub</h2>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.3em] mt-1 italic">Log & Verify Temporal Cycles</p>
                </div>
                <div class="text-right nature-card px-4 py-2 border-emerald-100 bg-emerald-50/30">
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Weekly Pulse</p>
                    <p class="text-lg font-black text-emerald-600 tabular-nums leading-none">{{ summary.weekly_hours || 0 }}<span class="text-[10px] text-slate-400">/{{ summary.target_hours }}h</span></p>
                </div>
            </header>

            <!-- Quick Log Section -->
            <div class="nature-card p-6 space-y-5 bg-white shadow-xl shadow-emerald-900/5 border-emerald-50">
                <div class="flex justify-between items-center px-1">
                    <h3 class="text-[10px] font-black text-slate-900 uppercase tracking-widest">Temporal Initialization</h3>
                    <button @click="showForm = !showForm" class="text-[9px] font-black text-emerald-600 uppercase tracking-widest border border-emerald-100 px-3 py-1.5 rounded-lg active:scale-90 transition-transform bg-emerald-50/50">
                        {{ showForm ? 'Terminate' : '+ Log Cycle' }}
                    </button>
                </div>

                <div v-if="showForm" class="space-y-5 animate-in fade-in slide-in-from-top-2">
                    <div class="space-y-2">
                        <label class="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] px-1">Context / Intel</label>
                        <textarea 
                            v-model="form.description"
                            rows="2"
                            placeholder="What task was processed in this cycle?"
                            class="w-full bg-slate-50/80 border-slate-100 rounded-2xl text-[11px] font-bold p-4 focus:ring-2 focus:ring-emerald-500/10 placeholder:text-slate-300 placeholder:italic"
                        ></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] px-1">Quantum Hours</label>
                            <input 
                                v-model="form.hours"
                                type="number" 
                                step="0.5"
                                class="w-full bg-slate-50/80 border-slate-100 rounded-2xl text-xs font-black p-4 focus:ring-2 focus:ring-emerald-500/10 tabular-nums"
                            />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] px-1">Shift Origin</label>
                            <input 
                                v-model="form.date"
                                type="date" 
                                class="w-full bg-slate-50/80 border-slate-100 rounded-2xl text-[10px] font-black p-4 focus:ring-2 focus:ring-emerald-500/10 uppercase"
                            />
                        </div>
                    </div>
                    <button 
                        @click="submitTime"
                        :disabled="submitting || !form.description || !form.hours"
                        class="w-full py-4 bg-slate-900 text-emerald-400 rounded-2xl font-black text-[10px] uppercase tracking-[0.3em] shadow-2xl active:scale-95 transition-all disabled:opacity-50 border-b-4 border-slate-950"
                    >
                        {{ submitting ? 'Syncing...' : 'Confirm Temporal Node' }}
                    </button>
                </div>
            </div>

            <!-- History -->
            <div class="space-y-4 pb-28">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] px-2 italic">Historical Cycles</h3>
                
                <div v-if="loading" class="text-center py-10 opacity-50"><i class="fas fa-satellite-dish animate-bounce text-emerald-500"></i></div>
                
                <div 
                    v-for="entry in items" 
                    :key="entry.id"
                    class="nature-card p-5 flex justify-between items-center group active:scale-[0.98] transition-all bg-white border-white/50"
                >
                    <div class="flex gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 text-xs font-black italic shadow-inner">
                            {{ entry.hours_spent }}h
                        </div>
                        <div>
                            <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight line-clamp-1 italic">{{ entry.task_description }}</h4>
                            <p class="text-[9px] text-slate-400 font-bold tracking-tight mt-1 truncate">
                                {{ formatDate(entry.date) }} • {{ entry.project?.name || 'Assigned Core' }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span :class="entry.status === 'Approved' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-amber-50 text-amber-600 border-amber-100'" 
                              class="text-[7px] font-black uppercase px-2 py-1 rounded-lg border tracking-widest leading-none">
                            {{ entry.status }}
                        </span>
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
const summary = ref({});
const loading = ref(true);
const showForm = ref(false);
const submitting = ref(false);

const form = ref({
    date: new Date().toISOString().split('T')[0],
    hours: 1,
    description: '',
    project_id: 1 // Default or selected project
});

const fetchData = async () => {
    try {
        const response = await axios.get('/api/mobile/v1/timesheets');
        items.value = response.data.timesheets.data;
        summary.value = response.data.summary;
    } catch (err) {
        console.error("Failed to fetch timesheets:", err);
    } finally {
        loading.value = false;
    }
};

const submitTime = async () => {
    submitting.value = true;
    try {
        await axios.post('/api/mobile/v1/timesheets', form.value);
        form.value.description = '';
        showForm.value = false;
        fetchData();
    } catch (err) {
        alert("Log failed: " + (err.response?.data?.message || "Check your hours"));
    } finally {
        submitting.value = false;
    }
};

const formatDate = (date) => new Date(date).toLocaleDateString([], { month: 'short', day: 'numeric' });

onMounted(fetchData);
</script>
