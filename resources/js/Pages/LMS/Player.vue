<template>
  <!-- Full Screen Player -->
  <div class="fixed inset-0 bg-gray-900 z-50">
    <Head :title="`Taking: ${course.title}`" />
    
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 px-6 py-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <h1 class="text-xl font-semibold text-gray-900">{{ course.title }}</h1>
          <span class="text-sm text-gray-500">
            Attempt {{ attempts_used + 1 }} of {{ course.max_attempts }}
          </span>
        </div>
        
        <div class="flex items-center space-x-6">
          <!-- Timer -->
          <div v-if="course.timer_minutes && examStarted" class="flex items-center space-x-2">
            <ClockIcon class="h-5 w-5 text-gray-400" />
            <span
              :class="[
                'text-lg font-mono font-semibold',
                timeRemaining < 300 ? 'text-red-600' : 'text-gray-900'
              ]"
            >
              {{ formatTime(timeRemaining) }}
            </span>
          </div>

          <!-- Progress -->
          <div v-if="examStarted" class="text-sm text-gray-600">
            Question {{ currentQuestionIndex + 1 }} of {{ questions.length }}
          </div>

          <!-- Exit Button -->
          <button
            @click="confirmExit"
            class="text-gray-600 hover:text-gray-900"
          >
            <XMarkIcon class="h-6 w-6" />
          </button>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="h-[calc(100vh-73px)] overflow-y-auto">
      <!-- Pre-Exam Instructions -->
      <div v-if="!examStarted" class="max-w-3xl mx-auto px-6 py-12">
        <div class="bg-white rounded-lg shadow-lg p-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-6">Exam Instructions</h2>
          
          <div class="space-y-4 mb-8">
            <div class="flex items-start">
              <CheckCircleIcon class="h-6 w-6 text-green-500 mt-0.5 mr-3" />
              <div>
                <p class="font-medium text-gray-900">Passing Score</p>
                <p class="text-sm text-gray-600">You need {{ course.passing_score }}% to pass</p>
              </div>
            </div>

            <div class="flex items-start">
              <ClockIcon class="h-6 w-6 text-blue-500 mt-0.5 mr-3" />
              <div>
                <p class="font-medium text-gray-900">Time Limit</p>
                <p class="text-sm text-gray-600">
                  {{ course.timer_minutes ? `${course.timer_minutes} minutes` : 'No time limit' }}
                </p>
              </div>
            </div>

            <div class="flex items-start">
              <ExclamationTriangleIcon class="h-6 w-6 text-yellow-500 mt-0.5 mr-3" />
              <div>
                <p class="font-medium text-gray-900">Important Rules</p>
                <ul class="text-sm text-gray-600 list-disc list-inside space-y-1">
                  <li v-if="course.track_tab_switches">
                    Do not switch tabs or leave this window (Max {{ course.max_tab_switches }} warnings)
                  </li>
                  <li v-if="course.prevent_copy_paste">Copy/paste is disabled</li>
                  <li>All questions must be answered</li>
                  <li>You cannot go back once submitted</li>
                </ul>
              </div>
            </div>
          </div>

          <button
            @click="startExam"
            class="w-full btn-primary text-lg py-4"
          >
            Start Exam
          </button>
        </div>
      </div>

      <!-- Exam Interface -->
      <div v-else-if="!showResults" class="max-w-4xl mx-auto px-6 py-8">
        <div class="bg-white rounded-lg shadow-lg p-8">
          <!-- Progress Bar -->
          <div class="mb-8">
            <div class="flex justify-between text-sm text-gray-600 mb-2">
              <span>Progress</span>
              <span>{{ currentQuestionIndex + 1 }} / {{ questions.length }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div
                class="bg-emerald-600 h-2 rounded-full transition-all duration-300"
                :style="{ width: `${((currentQuestionIndex + 1) / questions.length) * 100}%` }"
              />
            </div>
          </div>

          <!-- Question -->
          <div v-if="currentQuestion" class="mb-8">
            <!-- Scenario Context -->
            <div
              v-if="currentQuestion.scenario_context"
              class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6"
            >
              <p class="text-sm text-blue-900 font-medium mb-2">Scenario:</p>
              <p class="text-sm text-blue-800">{{ currentQuestion.scenario_context }}</p>
            </div>

            <!-- Image -->
            <div v-if="currentQuestion.image_url" class="mb-6">
              <img
                :src="currentQuestion.image_url"
                alt="Question image"
                class="max-w-full h-auto rounded-lg shadow-md"
              />
            </div>

            <!-- Question Text -->
            <h3 class="text-xl font-semibold text-gray-900 mb-6">
              {{ currentQuestionIndex + 1 }}. {{ currentQuestion.question_text }}
            </h3>

            <!-- Options -->
            <div class="space-y-3">
              <!-- MCQ -->
              <div
                v-if="currentQuestion.type === 'mcq'"
                v-for="(option, index) in currentQuestion.options"
                :key="option.id"
                @click="selectOption(option.id)"
                :class="[
                  'p-4 border-2 rounded-lg cursor-pointer transition-all',
                  answers[currentQuestion.id]?.selected === option.id
                    ? 'border-emerald-500 bg-emerald-50'
                    : 'border-gray-200 hover:border-gray-300'
                ]"
              >
                <div class="flex items-center">
                  <div
                    :class="[
                      'w-5 h-5 rounded-full border-2 mr-3 flex items-center justify-center',
                      answers[currentQuestion.id]?.selected === option.id
                        ? 'border-emerald-500 bg-emerald-500'
                        : 'border-gray-300'
                    ]"
                  >
                    <div
                      v-if="answers[currentQuestion.id]?.selected === option.id"
                      class="w-2 h-2 bg-white rounded-full"
                    />
                  </div>
                  <span class="text-gray-900">{{ option.text }}</span>
                </div>
              </div>

              <!-- Multi-Select -->
              <div
                v-if="currentQuestion.type === 'multi_select'"
                v-for="option in currentQuestion.options"
                :key="option.id"
                @click="toggleMultiSelect(option.id)"
                :class="[
                  'p-4 border-2 rounded-lg cursor-pointer transition-all',
                  isMultiSelected(option.id)
                    ? 'border-emerald-500 bg-emerald-50'
                    : 'border-gray-200 hover:border-gray-300'
                ]"
              >
                <div class="flex items-center">
                  <div
                    :class="[
                      'w-5 h-5 rounded border-2 mr-3 flex items-center justify-center',
                      isMultiSelected(option.id)
                        ? 'border-emerald-500 bg-emerald-500'
                        : 'border-gray-300'
                    ]"
                  >
                    <CheckIcon
                      v-if="isMultiSelected(option.id)"
                      class="h-4 w-4 text-white"
                    />
                  </div>
                  <span class="text-gray-900">{{ option.text }}</span>
                </div>
              </div>

              <!-- Text Answer -->
              <textarea
                v-if="currentQuestion.type === 'text'"
                v-model="answers[currentQuestion.id].text"
                rows="6"
                class="w-full input-field"
                placeholder="Type your answer here..."
              />
            </div>
          </div>

          <!-- Navigation -->
          <div class="flex justify-between pt-6 border-t">
            <button
              @click="previousQuestion"
              :disabled="currentQuestionIndex === 0"
              class="btn-secondary"
              :class="{ 'opacity-50 cursor-not-allowed': currentQuestionIndex === 0 }"
            >
              Previous
            </button>

            <button
              v-if="currentQuestionIndex < questions.length - 1"
              @click="nextQuestion"
              class="btn-primary"
            >
              Next
            </button>

            <button
              v-else
              @click="confirmSubmit"
              class="btn-primary bg-green-600 hover:bg-green-700"
            >
              Submit Exam
            </button>
          </div>
        </div>
      </div>

      <!-- Results -->
      <div v-else class="max-w-3xl mx-auto px-6 py-12">
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
          <div v-if="result.is_passed" class="mb-6">
            <CheckCircleIcon class="h-20 w-20 text-green-500 mx-auto mb-4" />
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Congratulations!</h2>
            <p class="text-gray-600">You have successfully passed the exam</p>
          </div>

          <div v-else class="mb-6">
            <XCircleIcon class="h-20 w-20 text-red-500 mx-auto mb-4" />
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Not Passed</h2>
            <p class="text-gray-600">You need {{ course.passing_score }}% to pass</p>
          </div>

          <!-- Score -->
          <div class="bg-gray-50 rounded-lg p-6 mb-6">
            <div class="text-5xl font-bold text-gray-900 mb-2">
              {{ result.percentage }}%
            </div>
            <div class="text-sm text-gray-600">
              {{ result.score_obtained }} / {{ result.max_score }} points
            </div>
          </div>

          <!-- Time Spent -->
          <div class="text-sm text-gray-600 mb-6">
            Time spent: {{ formatTime(result.time_spent_seconds) }}
          </div>

          <!-- Actions -->
          <div class="space-y-3">
            <button
              v-if="result.certificate"
              @click="downloadCertificate"
              class="w-full btn-primary"
            >
              <DocumentTextIcon class="h-5 w-5 mr-2" />
              Download Certificate
            </button>

            <button
              v-if="!result.is_passed && result.attempts_remaining > 0"
              @click="retakeExam"
              class="w-full btn-secondary"
            >
              Retake Exam ({{ result.attempts_remaining }} attempts left)
            </button>

            <Link
              :href="route('lms.my-courses')"
              class="block w-full btn-secondary"
            >
              Back to My Courses
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Anti-Cheat Warning Modal -->
    <Modal :show="showWarning" @close="showWarning = false">
      <div class="p-6">
        <div class="flex items-center mb-4">
          <ExclamationTriangleIcon class="h-8 w-8 text-yellow-500 mr-3" />
          <h3 class="text-lg font-semibold text-gray-900">Warning!</h3>
        </div>
        <p class="text-gray-600 mb-4">{{ warningMessage }}</p>
        <button @click="showWarning = false" class="btn-primary w-full">
          I Understand
        </button>
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import {
  ClockIcon,
  XMarkIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  CheckIcon,
  XCircleIcon,
  DocumentTextIcon
} from '@heroicons/vue/24/outline';
import axios from 'axios';

const props = defineProps({
  course: Object,
  assignment: Object,
  in_progress_attempt: Object,
  attempts_used: Number,
  can_attempt: Object
});

const examStarted = ref(false);
const questions = ref([]);
const currentQuestionIndex = ref(0);
const answers = ref({});
const attemptId = ref(null);
const timeRemaining = ref(0);
const timerInterval = ref(null);
const showResults = ref(false);
const result = ref(null);
const showWarning = ref(false);
const warningMessage = ref('');

const currentQuestion = computed(() => questions.value[currentQuestionIndex.value]);

const startExam = async () => {
  try {
    const response = await axios.post(route('lms.start', props.course.id));
    
    attemptId.value = response.data.attempt_id;
    questions.value = response.data.questions;
    
    // Initialize answers
    questions.value.forEach(q => {
      answers.value[q.id] = q.type === 'multi_select' 
        ? { selected: [] }
        : q.type === 'text'
        ? { text: '' }
        : { selected: null };
    });
    
    // Start timer
    if (props.course.timer_minutes) {
      timeRemaining.value = props.course.timer_minutes * 60;
      startTimer();
    }
    
    examStarted.value = true;
    
    // Setup anti-cheat monitoring
    setupAntiCheat();
  } catch (error) {
    alert('Failed to start exam: ' + error.response?.data?.error);
  }
};

const startTimer = () => {
  timerInterval.value = setInterval(() => {
    timeRemaining.value--;
    
    if (timeRemaining.value <= 0) {
      clearInterval(timerInterval.value);
      submitExam();
    }
  }, 1000);
};

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const selectOption = (optionId) => {
  answers.value[currentQuestion.value.id] = { selected: optionId };
};

const toggleMultiSelect = (optionId) => {
  const current = answers.value[currentQuestion.value.id].selected;
  const index = current.indexOf(optionId);
  
  if (index > -1) {
    current.splice(index, 1);
  } else {
    current.push(optionId);
  }
};

const isMultiSelected = (optionId) => {
  return answers.value[currentQuestion.value.id]?.selected.includes(optionId);
};

const nextQuestion = () => {
  if (currentQuestionIndex.value < questions.value.length - 1) {
    currentQuestionIndex.value++;
  }
};

const previousQuestion = () => {
  if (currentQuestionIndex.value > 0) {
    currentQuestionIndex.value--;
  }
};

const confirmSubmit = () => {
  if (confirm('Are you sure you want to submit? You cannot change your answers after submission.')) {
    submitExam();
  }
};

const submitExam = async () => {
  if (timerInterval.value) {
    clearInterval(timerInterval.value);
  }
  
  try {
    const response = await axios.post(route('lms.submit', attemptId.value), {
      answers: answers.value
    });
    
    result.value = response.data.result;
    showResults.value = true;
  } catch (error) {
    alert('Failed to submit exam: ' + error.response?.data?.error);
  }
};

const setupAntiCheat = () => {
  if (props.course.track_tab_switches) {
    document.addEventListener('visibilitychange', handleTabSwitch);
  }
  
  if (props.course.prevent_copy_paste) {
    document.addEventListener('copy', preventCopy);
    document.addEventListener('paste', preventPaste);
    document.addEventListener('contextmenu', preventRightClick);
  }
};

const handleTabSwitch = async () => {
  if (document.hidden && examStarted.value && !showResults.value) {
    try {
      const response = await axios.post(route('lms.track-violation', attemptId.value), {
        type: 'tab_switch'
      });
      
      warningMessage.value = response.data.message;
      showWarning.value = true;
      
      if (response.data.action === 'force_submit') {
        submitExam();
      }
    } catch (error) {
      console.error('Failed to track violation');
    }
  }
};

const preventCopy = (e) => {
  e.preventDefault();
  trackViolation('copy_attempt');
};

const preventPaste = (e) => {
  e.preventDefault();
  trackViolation('paste_attempt');
};

const preventRightClick = (e) => {
  e.preventDefault();
};

const trackViolation = async (type) => {
  try {
    await axios.post(route('lms.track-violation', attemptId.value), { type });
  } catch (error) {
    console.error('Failed to track violation');
  }
};

const confirmExit = () => {
  if (examStarted.value && !showResults.value) {
    if (confirm('Are you sure you want to exit? Your progress will be lost.')) {
      router.visit(route('lms.my-courses'));
    }
  } else {
    router.visit(route('lms.my-courses'));
  }
};

const downloadCertificate = () => {
  window.location.href = route('lms.certificate.download', result.value.certificate.id);
};

const retakeExam = () => {
  window.location.reload();
};

onBeforeUnmount(() => {
  if (timerInterval.value) {
    clearInterval(timerInterval.value);
  }
  
  document.removeEventListener('visibilitychange', handleTabSwitch);
  document.removeEventListener('copy', preventCopy);
  document.removeEventListener('paste', preventPaste);
  document.removeEventListener('contextmenu', preventRightClick);
});
</script>
