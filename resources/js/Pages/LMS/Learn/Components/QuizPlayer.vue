<template>
  <div class="h-full flex flex-col bg-[#0f172a]">
    <!-- Quiz Header -->
    <div class="bg-[#1e293b] border-b border-white/5 p-4 flex items-center justify-between">
      <div>
        <h3 class="text-sm font-bold text-white">{{ quiz?.title }}</h3>
        <p class="text-[10px] text-slate-500 uppercase font-bold tracking-widest">{{ questions_count }} Questions · {{ config?.pass_mark_pct }}% Passing Score</p>
      </div>

      <div class="flex items-center gap-6">
        <div v-if="timeRemaining" class="flex items-center gap-3 bg-slate-900/50 px-4 py-2 rounded-xl border border-white/5">
          <ClockIcon :class="['h-5 w-5', isLowTime ? 'text-rose-400 animate-pulse' : 'text-indigo-400']" />
          <span :class="['text-sm font-mono font-bold', isLowTime ? 'text-rose-400' : 'text-white']">{{ formatTime(timeRemaining) }}</span>
        </div>
        <button v-if="status === 'idle'" @click="startQuiz" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-6 py-2 rounded-xl transition-all shadow-lg active:scale-95">
          Start Quiz Now
        </button>
      </div>
    </div>

    <!-- Quiz Loading/Ready State -->
    <div v-if="status === 'idle'" class="flex-1 flex flex-col items-center justify-center p-12 text-center">
      <div class="w-16 h-16 bg-indigo-500/20 rounded-2xl flex items-center justify-center mb-6">
        <PencilSquareIcon class="h-8 w-8 text-indigo-400" />
      </div>
      <h2 class="text-2xl font-bold text-white mb-4">Are you ready to begin?</h2>
      <p class="text-slate-400 text-sm max-w-md mb-8 leading-relaxed">
        This quiz has a limit of <strong>{{ config?.duration_minutes }} minutes</strong> and <strong>{{ config?.max_attempts }} attempts</strong>.
        Avoid switching tabs or closing the window, as it may invalidate your attempt.
      </p>
      
      <div class="grid grid-cols-2 gap-4 w-full max-w-sm mb-10">
        <div class="bg-white/5 p-4 rounded-2xl border border-white/5">
          <div class="text-xl font-bold text-white">{{ questions_count }}</div>
          <p class="text-[10px] text-slate-500 uppercase font-bold mt-1">Questions</p>
        </div>
        <div class="bg-white/5 p-4 rounded-2xl border border-white/5">
          <div class="text-xl font-bold text-emerald-400">{{ config?.pass_mark_pct }}%</div>
          <p class="text-[10px] text-slate-500 uppercase font-bold mt-1">Passing Mark</p>
        </div>
      </div>

      <button @click="startQuiz" class="group bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-12 py-4 rounded-3xl shadow-2xl transition-all transform hover:scale-105 active:scale-95 flex items-center gap-3">
        Start Assessment
        <ArrowRightIcon class="h-5 w-5 group-hover:translate-x-1 transition-transform" />
      </button>
    </div>

    <!-- Quiz Active State -->
    <div v-else-if="status === 'active'" class="flex-1 flex flex-col overflow-hidden">
      <!-- Question Navigator Sidebar -->
      <div class="flex-1 flex overflow-hidden">
        <div class="w-20 sm:w-64 bg-[#1e293b]/50 border-r border-white/5 p-4 flex-col hidden sm:flex">
          <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-4">Question Map</p>
          <div class="grid grid-cols-4 gap-2">
            <button v-for="(q, idx) in questions" :key="q.id"
              @click="currentIdx = idx"
              :class="['w-10 h-10 rounded-xl text-xs font-bold transition-all flex items-center justify-center', 
                currentIdx === idx ? 'bg-indigo-600 text-white ring-2 ring-indigo-500/50' : 
                isAnswered(q.id) ? 'bg-emerald-500/20 text-emerald-400' : 'bg-white/5 text-slate-500 hover:bg-white/10']">
              {{ idx + 1 }}
            </button>
          </div>
          
          <div class="mt-auto space-y-3 pt-6 border-t border-white/5">
            <div class="flex items-center justify-between text-[11px]">
              <span class="text-slate-500">Answered</span>
              <span class="text-emerald-400 font-bold">{{ answered_count }} / {{ questions_count }}</span>
            </div>
            <div class="w-full bg-slate-900 rounded-full h-1">
              <div class="bg-emerald-500 h-1 rounded-full transition-all" :style="`width: ${ (answered_count / questions_count) * 100 }%`"></div>
            </div>
          </div>
        </div>

        <!-- Question View -->
        <main class="flex-1 overflow-y-auto p-6 sm:p-12 custom-scrollbar">
          <div class="max-w-3xl mx-auto">
            <div class="flex items-center gap-3 mb-8">
              <span class="bg-indigo-500/20 text-indigo-400 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-widest">Question {{ currentIdx + 1 }} of {{ questions_count }}</span>
              <span v-if="currentQuestion?.marks" class="text-slate-500 text-[10px] uppercase font-bold tracking-widest">{{ currentQuestion.marks }} Points</span>
            </div>

            <h2 class="text-2xl font-bold text-white mb-10 leading-relaxed">{{ currentQuestion?.question_text }}</h2>
            
            <!-- Type-specific Question Inputs -->
            <div v-if="currentQuestion" class="space-y-4">
              <!-- MCQ -->
              <div v-if="currentQuestion.type === 'mcq'" class="space-y-3">
                <button v-for="opt in currentQuestion.options" :key="opt.id"
                  @click="selectAnswer(currentQuestion.id, opt.id)"
                  :class="['w-full group p-5 rounded-2xl border transition-all text-left flex items-center gap-4', 
                    responses[currentQuestion.id] === opt.id ? 'bg-indigo-600/20 border-indigo-600 ring-1 ring-indigo-600 shadow-lg shadow-indigo-600/10' : 'bg-white/5 border-white/5 hover:bg-white/8 hover:border-white/10']">
                  <div :class="['w-6 h-6 rounded-full border-2 flex items-center justify-center transition-colors', 
                    responses[currentQuestion.id] === opt.id ? 'border-indigo-400 bg-indigo-500 shadow-inner' : 'border-slate-700 group-hover:border-slate-500']">
                    <div v-if="responses[currentQuestion.id] === opt.id" class="w-1.5 h-1.5 rounded-full bg-white"></div>
                  </div>
                  <span :class="['text-sm font-medium', responses[currentQuestion.id] === opt.id ? 'text-white' : 'text-slate-300 group-hover:text-slate-100']">{{ opt.text }}</span>
                </button>
              </div>

              <!-- True/False -->
              <div v-else-if="currentQuestion.type === 'true_false'" class="grid grid-cols-2 gap-4">
                 <button v-for="val in ['true','false']" :key="val"
                   @click="selectAnswer(currentQuestion.id, val)"
                   :class="['p-8 rounded-3xl border transition-all text-center flex flex-col items-center gap-3', 
                     responses[currentQuestion.id] === val ? 'bg-indigo-600/20 border-indigo-600' : 'bg-white/5 border-white/5 hover:bg-white/10']">
                   <div :class="['w-10 h-10 rounded-2xl flex items-center justify-center transition-colors', responses[currentQuestion.id] === val ? 'bg-indigo-500 text-white' : 'bg-white/5 text-slate-500']">
                     <CheckIcon v-if="val === 'true'" class="h-6 w-6" />
                     <XMarkIcon v-else class="h-6 w-6" />
                   </div>
                   <span class="text-sm font-bold uppercase tracking-widest text-white">{{ val.toUpperCase() }}</span>
                 </button>
              </div>

              <!-- Descriptive/Short Answer -->
              <div v-else-if="['short_answer','numerical'].includes(currentQuestion.type)">
                <input type="text" v-model="responses[currentQuestion.id]" placeholder="Type your answer here..."
                  class="w-full bg-white/5 border border-white/10 rounded-2xl p-6 text-xl text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none" />
              </div>
            </div>
          </div>
        </main>
      </div>

      <!-- Navigation Footer -->
      <footer class="h-20 bg-[#1e293b] border-t border-white/5 px-8 flex items-center justify-between z-20">
        <button @click="prev" :disabled="currentIdx === 0" class="flex items-center gap-2 text-slate-500 hover:text-white disabled:opacity-20 transition-colors">
          <ChevronLeftIcon class="h-5 w-5" />
          <span class="text-sm font-bold uppercase tracking-widest">Back</span>
        </button>

        <div class="flex items-center gap-4">
          <span class="hidden md:block text-[10px] text-slate-600 uppercase font-black tracking-widest">Auto-saving progress...</span>
          <button @click="submit" :disabled="submitting" 
            class="bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white font-bold px-10 py-3 rounded-2xl shadow-xl shadow-emerald-500/20 transition-all flex items-center gap-3 active:scale-95 group">
            <span v-if="submitting">Submitting...</span>
            <span v-else>Finish & Submit</span>
            <PaperAirplaneIcon v-if="!submitting" class="h-4 w-4 transform group-hover:-translate-y-1 group-hover:translate-x-1 transition-transform" />
          </button>
        </div>

        <button @click="next" :disabled="currentIdx === questions.length - 1" class="flex items-center justify-end gap-2 text-slate-500 hover:text-white disabled:opacity-20 transition-colors group">
          <span class="text-sm font-bold uppercase tracking-widest">Next</span>
          <ChevronRightIcon class="h-5 w-5 group-hover:translate-x-1 transition-transform" />
        </button>
      </footer>
    </div>

    <!-- Result View -->
    <div v-else-if="status === 'result'" class="flex-1 flex flex-col items-center justify-center p-12 text-center bg-slate-900 overflow-y-auto">
      <div class="max-w-lg w-full">
        <!-- Result Circle -->
        <div :class="['w-40 h-40 mx-auto rounded-full flex flex-col items-center justify-center border-8 mb-8 shadow-2xl transition-all scale-110', 
          result?.is_passed ? 'bg-emerald-500/10 border-emerald-500 text-emerald-500' : 'bg-rose-500/10 border-rose-500 text-rose-500']">
          <div class="text-5xl font-black">{{ Math.round(result?.percentage) }}%</div>
          <div class="text-[10px] uppercase font-bold tracking-widest mt-1">Score</div>
        </div>

        <h2 class="text-3xl font-black text-white mb-2">{{ result?.is_passed ? 'CONGRATULATIONS! 🎉' : 'NICE EFFORT! 💪' }}</h2>
        <p class="text-slate-400 mb-10 leading-relaxed">
          You scored <strong>{{ result?.score_obtained }} / {{ result?.max_score }}</strong> marks.
          <span v-if="result?.is_passed">You've successfully cleared this assessment and earned progress points.</span>
          <span v-else>Unfortunately, you didn't clear the pass mark of <strong>{{ config?.pass_mark_pct }}%</strong>. Don't worry, you can try again!</span>
        </p>

        <div class="grid grid-cols-2 gap-4 mb-12">
          <div class="bg-white/5 p-5 rounded-3xl border border-white/5 flex flex-col items-center">
            <div class="bg-indigo-500/20 p-2 rounded-xl mb-3"><ClockIcon class="h-5 w-5 text-indigo-400" /></div>
            <div class="text-xl font-bold text-white">{{ formatTimeSpent(result?.time_spent_seconds) }}</div>
            <p class="text-[9px] text-slate-500 uppercase font-black tracking-widest mt-2">Time Taken</p>
          </div>
          <div class="bg-white/5 p-5 rounded-3xl border border-white/5 flex flex-col items-center">
            <div class="bg-emerald-500/20 p-2 rounded-xl mb-3"><HandThumbUpIcon class="h-5 w-5 text-emerald-400" /></div>
            <div class="text-xl font-bold text-white">{{ result?.is_passed ? 'PASSED' : 'FAILED' }}</div>
            <p class="text-[9px] text-slate-500 uppercase font-black tracking-widest mt-2">Status</p>
          </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <button @click="$emit('completed')" class="bg-white text-slate-900 font-black px-10 py-4 rounded-3xl shadow-xl hover:bg-slate-200 transition-all flex items-center justify-center gap-2">
            Continue Learning
            <ChevronRightIcon class="h-5 w-5" />
          </button>
          <button v-if="!result?.is_passed" @click="status = 'idle'" class="bg-white/10 hover:bg-white/20 text-white font-black px-10 py-4 rounded-3xl transition-all">
            Retake Quiz
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { 
  ClockIcon, PencilSquareIcon, ArrowRightIcon, ChevronLeftIcon, 
  ChevronRightIcon, CheckIcon, XMarkIcon, PaperAirplaneIcon,
  CheckCircleIcon, HandThumbUpIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  activity: Object,
  config:   Object
});

const emit = defineEmits(['completed']);

// Quiz State
const status = ref('idle'); // idle, active, result
const questions = ref([]);
const attemptId = ref(null);
const responses = ref({});
const currentIdx = ref(0);
const timeRemaining = ref(null);
const timerInterval = ref(null);
const submitting = ref(false);
const result = ref(null);
const tabSwitchCount = ref(0);

// Multi-Question mapping
const currentQuestion = computed(() => questions.value[currentIdx.value]);
const questions_count = computed(() => props.activity.config?.question_ids?.length || 0);
const answered_count  = computed(() => Object.keys(responses.value).length);
const isLowTime       = computed(() => timeRemaining.value < 120); // less than 2 mins

// Methods
const startQuiz = async () => {
  try {
    const res = await axios.post(`/learn/activity/${props.activity.id}/quiz/start`);
    questions.value = res.data.questions;
    attemptId.value = res.data.attempt_id;
    status.value = 'active';
    
    if (props.config.duration_minutes) {
      timeRemaining.value = props.config.duration_minutes * 60;
      startTimer();
    }

    // Initialize responses if user is resuming
    if (res.data.existing_responses) {
      responses.value = res.data.existing_responses;
    }

    // Attach visibility listener for proctoring
    document.addEventListener('visibilitychange', handleVisibilityChange);
  } catch (err) {
    console.error(err);
    alert('Failed to start quiz. Already reached max attempts?');
  }
};

const handleVisibilityChange = () => {
  if (document.hidden && status.value === 'active') {
    tabSwitchCount.value++;
    axios.post(`/learn/quiz/${attemptId.value}/violation`, { type: 'tab_switch', count: tabSwitchCount.value });
    if (props.config.max_tab_switches && tabSwitchCount.value >= props.config.max_tab_switches) {
      alert('Too many tab switches detected. Quiz will be submmitted automatically.');
      submit();
    }
  }
};

const startTimer = () => {
  timerInterval.value = setInterval(() => {
    if (timeRemaining.value > 0) {
      timeRemaining.value--;
    } else {
      clearInterval(timerInterval.value);
      submit(); // auto-submit on timeout
    }
  }, 1000);
};

const selectAnswer = (qId, ans) => {
  responses.value[qId] = ans;
  // Optional: Auto-save single response via API here for resume capability
};

const isAnswered = (id) => !!responses.value[id];

const next = () => { if (currentIdx.value < questions.value.length - 1) currentIdx.value++; };
const prev = () => { if (currentIdx.value > 0) currentIdx.value--; };

const submit = async () => {
  if (submitting.value) return;
  submitting.value = true;
  clearInterval(timerInterval.value);
  
  try {
    const res = await axios.post(`/learn/quiz/${attemptId.value}/submit`, { responses: responses.value });
    result.value = res.data.result;
    status.value = 'result';
    document.removeEventListener('visibilitychange', handleVisibilityChange);
  } catch (err) {
    console.error(err);
  } finally {
    submitting.value = false;
  }
};

const formatTime = (seconds) => {
  const m = Math.floor(seconds / 60);
  const s = seconds % 60;
  return `${m}:${s.toString().padStart(2, '0')}`;
};

const formatTimeSpent = (seconds) => {
  if (!seconds) return '0:00';
  const m = Math.floor(seconds / 60);
  const s = seconds % 60;
  return `${m}m ${s}s`;
};

onUnmounted(() => {
  clearInterval(timerInterval.value);
  document.removeEventListener('visibilitychange', handleVisibilityChange);
});
</script>

<style scoped>
.font-mono { font-family: 'JetBrains Mono', 'Fira Code', monospace; }
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }
</style>
