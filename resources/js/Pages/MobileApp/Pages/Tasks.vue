<template>
    <AppLayout>
        <div class="space-y-6">
            <header class="flex justify-between items-end px-1">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">My Tasks</h2>
                    <p class="text-sm text-slate-500 font-medium">Tracking your assignments</p>
                </div>
            </header>

            <!-- Search & Filters -->
            <div class="glass-card p-2 flex gap-2">
                <div class="relative flex-1">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Search tasks..." 
                        class="w-full pl-10 pr-4 py-3 bg-transparent border-none focus:ring-0 text-sm"
                    >
                </div>
            </div>

            <!-- Task List -->
            <div class="space-y-4">
                <div v-if="loading" class="text-center py-10 opacity-50">
                    <i class="fas fa-spinner animate-spin text-emerald-500 text-2xl"></i>
                </div>
                
                <div v-else-if="tasks.length === 0" class="text-center py-20 px-8">
                    <div class="w-20 h-20 bg-emerald-50 rounded-[2rem] flex items-center justify-center mx-auto mb-6 text-emerald-200 shadow-inner">
                        <i class="fas fa-clipboard-check text-3xl"></i>
                    </div>
                    <p class="text-slate-400 font-black uppercase text-[10px] tracking-[0.2em]">No Active Directives Found</p>
                </div>

                <div 
                    v-for="task in filteredTasks" 
                    :key="task.id" 
                    class="nature-card p-5 active:scale-[0.98] transition-all cursor-pointer"
                    @click="selectedTask = task"
                >
                    <div class="flex justify-between items-start mb-4">
                        <span :class="priorityClass(task.priority?.name)" class="text-[8px] font-black px-2 py-1 rounded-lg uppercase tracking-widest border border-white">
                            {{ task.priority?.name || 'Medium' }}
                        </span>
                        <div class="w-8 h-8 rounded-xl bg-slate-50 border border-white flex items-center justify-center text-[10px] font-black text-slate-400 shadow-sm">
                            {{ task.project?.name.charAt(0) }}
                        </div>
                    </div>
                    
                    <h4 class="text-sm font-black text-slate-900 mb-1 uppercase tracking-tight">{{ task.name }}</h4>
                    <p class="text-[10px] text-slate-400 font-bold italic line-clamp-1 mb-5">{{ task.project?.name }}</p>

                    <div class="flex justify-between items-center bg-slate-50/50 p-2 rounded-xl border border-white">
                        <div class="flex-1 mr-4">
                            <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden shadow-inner">
                                <div 
                                    class="h-full bg-emerald-500 transition-all duration-700 shadow-[0_0_10px_#10b98144]" 
                                    :style="{ width: task.progress + '%' }"
                                ></div>
                            </div>
                        </div>
                        <span class="text-[10px] font-black text-slate-900 tabular-nums italic">{{ task.progress }}%</span>
                    </div>
                </div>
            </div>

            <!-- Task Detail Modal (Drawer style) -->
            <transition name="slide-up">
                <div v-if="selectedTask" class="fixed inset-0 z-[2000] flex flex-col justify-end">
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-[2px]" @click="selectedTask = null"></div>
                    <div class="nature-card !rounded-t-[3rem] !rounded-b-none p-8 max-h-[90vh] overflow-y-auto relative z-[2001] pb-12 border-t border-white shadow-[0_-20px_50px_rgba(0,0,0,0.1)]">
                        <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-8"></div>
                        
                        <div class="flex justify-between items-start mb-8">
                            <div class="flex-1">
                                <p class="text-[9px] font-black text-emerald-600 uppercase tracking-[0.3em] mb-1 italic">{{ selectedTask.project?.name }}</p>
                                <h3 class="text-xl font-black text-slate-900 tracking-tight leading-tight">{{ selectedTask.name }}</h3>
                            </div>
                            <button @click="selectedTask = null" class="w-10 h-10 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 border border-white shadow-sm active:scale-90 transition-transform">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>

                        <div class="space-y-8">
                            <!-- Checklist -->
                            <div>
                                <div class="flex items-center justify-between mb-5 px-1">
                                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Deployment Checklist</h4>
                                    <span class="text-[9px] font-bold text-emerald-600 italic">{{ selectedTask.checklists?.filter(c => c.is_completed).length || 0 }}/{{ selectedTask.checklists?.length || 0 }} Sync</span>
                                </div>
                                <div class="space-y-3">
                                    <div 
                                        v-for="item in selectedTask.checklists" 
                                        :key="item.id"
                                        class="flex items-center gap-4 p-4 rounded-2xl border border-white shadow-sm transition-all active:scale-[0.98]"
                                        :class="item.is_completed ? 'bg-emerald-50/30' : 'bg-slate-50/50'"
                                        @click="toggleChecklist(item)"
                                    >
                                        <div :class="item.is_completed ? 'bg-emerald-500 border-emerald-500' : 'bg-white border-slate-200'" class="w-6 h-6 rounded-lg border-2 flex items-center justify-center transition-all shadow-sm">
                                            <i v-if="item.is_completed" class="fas fa-check text-white text-[10px]"></i>
                                        </div>
                                        <span :class="{ 'text-slate-400 italic line-through': item.is_completed }" class="text-xs font-black text-slate-700 tracking-tight">
                                            {{ item.item_name }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Progress Slider -->
                            <div class="p-6 bg-slate-900 rounded-[2rem] shadow-xl">
                                <div class="flex justify-between items-center mb-6">
                                    <h4 class="text-[10px] font-black text-emerald-400 uppercase tracking-widest">Progress Metrics</h4>
                                    <span class="text-lg font-black text-white tabular-nums">{{ selectedTask.progress }}%</span>
                                </div>
                                <input 
                                    type="range" v-model="selectedTask.progress" min="0" max="100" step="5"
                                    class="w-full h-1.5 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-emerald-500"
                                    @change="updateProgress"
                                >
                            </div>

                            <!-- Log Time Quick Action -->
                            <div class="pt-2">
                                <button 
                                    @click="showLogTime = !showLogTime"
                                    class="w-full flex items-center justify-between p-5 nature-card !bg-emerald-50/50 border-emerald-100 text-emerald-700 active:scale-[0.98] transition-all"
                                >
                                    <div class="flex items-center gap-3">
                                        <i class="fas fa-stopwatch text-xs"></i>
                                        <span class="text-[10px] font-black uppercase tracking-[0.2em]">Temporal Logging Hub</span>
                                    </div>
                                    <i :class="showLogTime ? 'fa-chevron-down' : 'fa-plus'" class="fas text-[10px]"></i>
                                </button>
                                
                                <div v-if="showLogTime" class="mt-4 p-6 nature-card !bg-slate-50/50 space-y-5 animate-in fade-in slide-in-from-top-2">
                                    <div class="grid grid-cols-3 gap-4">
                                        <div class="col-span-1">
                                            <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest block mb-2 px-1">Quantum Hours</label>
                                            <input v-model="logForm.hours" type="number" step="0.5" class="w-full bg-white border border-slate-100 rounded-xl p-3 text-xs font-black shadow-inner focus:ring-1 focus:ring-emerald-500/20" />
                                        </div>
                                        <div class="col-span-2">
                                            <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest block mb-2 px-1">Activity Context</label>
                                            <input v-model="logForm.description" type="text" placeholder="e.g. Logic Sync" class="w-full bg-white border border-slate-100 rounded-xl p-3 text-xs font-black shadow-inner focus:ring-1 focus:ring-emerald-500/20" />
                                        </div>
                                    </div>
                                    <button 
                                        @click="quickLogTime"
                                        :disabled="logging"
                                        class="w-full py-4 bg-slate-900 text-emerald-400 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] shadow-xl disabled:opacity-50 active:scale-95 transition-all"
                                    >
                                        {{ logging ? 'Synthesizing...' : 'Log Temporal Cycle' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import AppLayout from '../App.vue';
import axios from 'axios';

const tasks = ref([]);
const loading = ref(true);
const search = ref('');
const selectedTask = ref(null);
const showLogTime = ref(false);
const logging = ref(false);
const logForm = ref({
    hours: 1,
    description: '',
    date: new Date().toISOString().split('T')[0]
});

const fetchTasks = async () => {
    try {
        const response = await axios.get('/api/mobile/v1/tasks');
        tasks.value = response.data.data;
    } catch (err) {
        console.error("Failed to fetch tasks:", err);
    } finally {
        loading.value = false;
    }
};

const filteredTasks = computed(() => {
    if (!search.value) return tasks.value;
    return tasks.value.filter(t => 
        t.name?.toLowerCase().includes(search.value.toLowerCase()) || 
        t.project?.name?.toLowerCase().includes(search.value.toLowerCase())
    );
});

const priorityClass = (p) => {
    const map = {
        'High': 'bg-rose-50 text-rose-600 border-rose-100',
        'Medium': 'bg-amber-50 text-amber-600 border-amber-100',
        'Low': 'bg-emerald-50 text-emerald-600 border-emerald-100'
    };
    return map[p] || map['Medium'];
};

const toggleChecklist = async (item) => {
    try {
        const response = await axios.post(`/api/mobile/v1/checklist/${item.id}/toggle`);
        item.is_completed = response.data.checklist.is_completed;
        if (selectedTask.value) {
            selectedTask.value.progress = response.data.new_progress;
        }
    } catch (err) {
        console.error("Toggle failed:", err);
    }
};

const updateProgress = async () => {
    try {
        await axios.put(`/api/mobile/v1/tasks/${selectedTask.value.id}/progress`, {
            progress: selectedTask.value.progress
        });
    } catch (err) {
        console.error("Progress update failed:", err);
    }
};

const quickLogTime = async () => {
    logging.value = true;
    try {
        await axios.post('/api/mobile/v1/timesheets', {
            ...logForm.value,
            project_id: selectedTask.value.project_id,
            task_id: selectedTask.value.id
        });
        showLogTime.value = false;
        logForm.value.description = '';
        alert("Temporal Log Synchronized.");
    } catch (err) {
        alert("Log failed: Check constraints.");
    } finally {
        logging.value = false;
    }
};

onMounted(fetchTasks);
</script>

<style scoped>
input[type=range]::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: #10b981;
  cursor: pointer;
  border: 5px solid white;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

.slide-up-enter-active, .slide-up-leave-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-up-enter-from, .slide-up-leave-to {
  transform: translateY(100%);
}
</style>
