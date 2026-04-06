<template>
  <MainLayout>
    <Head title="LMS Faculty Hub" />

    <div class="h-[calc(100vh-80px)] bg-indigo-50/30 flex font-sans text-slate-900 overflow-hidden rounded-[2rem] border border-white/40 shadow-2xl relative">
      <!-- Ambient indigo glow -->
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(79,70,229,0.05),transparent_50%)] pointer-events-none"></div>

      <!-- Left Dock: Instructor Command -->
      <Sidebar 
        :current-section="activeSection" 
        @navigate="activeSection = $event"
      />

      <!-- Main Command Deck -->
      <div class="flex-1 flex flex-col min-w-0 bg-white/40 backdrop-blur-md relative z-10">
        <!-- Faculty Header -->
        <header class="h-24 px-8 flex items-center justify-between border-b border-indigo-100/50">
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">{{ activeModuleLabel }}</h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1 italic">Instructor Command Center // Emerald Light Suite</p>
            </div>

            <!-- Quick Stats in Header -->
            <div class="hidden lg:flex items-center gap-6">
                <div v-for="(val, key) in stats" :key="key" class="text-right">
                    <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest">{{ key.replace('_', ' ') }}</p>
                    <p class="text-xl font-black text-slate-800">{{ val }}</p>
                </div>
            </div>
        </header>

        <!-- Dynamic Content Engine -->
        <main class="flex-1 overflow-y-auto p-8 scroll-smooth custom-scrollbar">
          <transition 
            name="fade-slide" 
            mode="out-in"
          >
            <div v-if="activeSection === 'dashboard'" class="space-y-8">
                <!-- Action Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gradient-to-br from-indigo-600 to-violet-700 p-6 rounded-[2rem] text-white shadow-xl shadow-indigo-100 group hover:scale-[1.02] transition-transform cursor-pointer">
                        <div class="flex justify-between items-start mb-6">
                            <i class="fas fa-marker text-2xl opacity-40"></i>
                            <span class="px-3 py-1 bg-white/20 rounded-full text-[9px] font-black uppercase tracking-widest">Urgent</span>
                        </div>
                        <h3 class="text-lg font-black leading-tight mb-1">Grading Queue</h3>
                        <p class="text-xs opacity-70 mb-6">You have {{ stats.pending_grades }} submissions awaiting review.</p>
                        <button class="w-full py-3 bg-white text-indigo-700 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg">Start Assessment</button>
                    </div>

                    <div class="bg-white p-6 rounded-[2rem] border border-indigo-100 shadow-sm group hover:border-indigo-300 transition-colors">
                        <div class="flex justify-between items-start mb-6">
                            <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600">
                                <i class="fas fa-satellite text-xl"></i>
                            </div>
                        </div>
                        <h3 class="text-lg font-black text-slate-800 leading-tight mb-1">Live Sessions</h3>
                        <p class="text-xs text-slate-400 mb-6">{{ stats.live_sessions_today }} scheduled for today.</p>
                        <button class="w-full py-3 bg-indigo-50 text-indigo-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition-all">Enter Classroom</button>
                    </div>

                    <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm group hover:border-indigo-300 transition-colors">
                        <div class="flex justify-between items-start mb-6">
                            <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-colors">
                                <i class="fas fa-paper-plane text-xl"></i>
                            </div>
                        </div>
                        <h3 class="text-lg font-black text-slate-800 leading-tight mb-1">Batch Comms</h3>
                        <p class="text-xs text-slate-400 mb-6">Update all students in a single click.</p>
                        <button @click="showBroadcastModal = true" class="w-full py-3 bg-slate-50 text-slate-400 rounded-xl text-[10px] font-black uppercase tracking-widest group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-all">Broadcast Info</button>
                    </div>
                </div>

                <!-- Broadcast Modal -->
                <div v-if="showBroadcastModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div @click="showBroadcastModal = false" class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm"></div>
                    <div class="bg-white rounded-[3rem] p-10 shadow-2xl relative z-10 w-full max-w-lg border border-indigo-50">
                        <div class="flex items-center gap-5 mb-8">
                            <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 text-2xl shadow-sm">
                                <i class="fas fa-satellite-dish"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-slate-900 italic uppercase">Global Broadcast</h3>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Transmit priority intel to batch</p>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest pl-2">Target Audience</label>
                                <select v-model="broadcastForm.target" class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-xs font-bold focus:ring-2 focus:ring-indigo-100">
                                    <option value="all">All Active Students</option>
                                    <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.title }}</option>
                                    <option value="at-risk">At-Risk Students Only</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest pl-2">Transmission Content</label>
                                <textarea v-model="broadcastForm.message" rows="4" class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-xs focus:ring-2 focus:ring-indigo-100 resize-none font-medium" placeholder="Draft your transmission..."></textarea>
                            </div>

                            <div class="flex items-center justify-between pt-4">
                                <button @click="showBroadcastModal = false" class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors">Abort</button>
                                <button @click="sendBroadcast" class="px-8 py-4 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl hover:bg-indigo-700 hover:scale-105 transition-all flex items-center gap-3">
                                    <i class="fas fa-paper-plane"></i>
                                    Transmit
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- My Courses List -->
                <div class="bg-white/60 p-8 rounded-[2rem] border border-white/40 shadow-sm backdrop-blur-xl">
                    <h2 class="text-xs font-black text-indigo-900 uppercase tracking-[0.3em] mb-8">My Active Courses</h2>
                    <div class="space-y-4">
                        <div v-for="course in courses" :key="course.id" class="p-5 bg-white border border-indigo-50 rounded-2xl flex items-center justify-between hover:shadow-md hover:border-indigo-100 transition-all group">
                             <div class="flex items-center gap-5">
                                 <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                     <i class="fas fa-book"></i>
                                 </div>
                                 <div class="min-w-0">
                                     <h4 class="font-black text-slate-800 text-sm leading-tight">{{ course.title }}</h4>
                                     <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">{{ course.enrollment }} Students Enrolled</p>
                                 </div>
                             </div>
                             <div class="flex items-center gap-12">
                                 <div class="hidden md:block text-right">
                                     <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest mb-1">Avg Progress</p>
                                     <div class="flex items-center gap-3">
                                         <div class="w-24 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                             <div class="h-full bg-indigo-500" :style="{ width: course.progress + '%' }"></div>
                                         </div>
                                         <span class="text-xs font-black text-slate-600">{{ course.progress }}%</span>
                                     </div>
                                 </div>
                                 <button class="p-3 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all">
                                     <i class="fas fa-chevron-right text-xs"></i>
                                 </button>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="h-full flex flex-col items-center justify-center grayscale opacity-50">
                <i class="fas fa-cubes text-6xl text-slate-300 mb-6"></i>
                <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Section {{ activeSection }} Module is Syncing...</p>
            </div>
          </transition>
        </main>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import MainLayout from '../../../Layouts/MainLayout.vue';
import Sidebar from './Components/Sidebar.vue';

const props = defineProps({
    stats: Object,
    courses: Array,
    pending_submissions: Array
});

const activeSection = ref('dashboard');
const showBroadcastModal = ref(false);

const broadcastForm = ref({
    target: 'all',
    message: ''
});

const sendBroadcast = () => {
    console.log('Transmitting payload:', broadcastForm.value);
    showBroadcastModal.value = false;
    broadcastForm.value.message = '';
    // Typically an inertia post request: form.post(route('lms.faculty.message.send'))
};

const activeModuleLabel = computed(() => {
    const modules = {
        'dashboard': 'Instructor Overview',
        'courses': 'My Managed Tracks',
        'grading': 'Assessment Ledger',
        'messages': 'Communication Hub',
        'sessions': 'Live Class Engine'
    };
    return modules[activeSection.value] || 'Faculty Hub';
});

</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap');

.font-sans {
    font-family: 'Outfit', sans-serif;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(79, 70, 229, 0.1);
    border-radius: 20px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(79, 70, 229, 0.3);
}

.fade-slide-enter-active, .fade-slide-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.fade-slide-enter-from {
    opacity: 0;
    transform: translateY(20px) scale(0.98);
}
.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-20px) scale(1.02);
}
</style>
