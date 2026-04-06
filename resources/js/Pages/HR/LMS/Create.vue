<template>
  <MainLayout>
    <Head title="Create Course" />
    
    <div class="py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <div class="flex items-center gap-3">
            <Link :href="route('hr.lms.index')" class="text-gray-500 hover:text-gray-700">
              <ArrowLeftIcon class="h-5 w-5" />
            </Link>
            <h1 class="text-3xl font-bold text-gray-900">Create Training Course</h1>
          </div>
          <p class="mt-1 text-sm text-gray-600">Build a new course with content and assessments</p>
        </div>

        <!-- Progress Steps -->
        <div class="mb-8">
          <div class="flex items-center justify-between">
            <div
              v-for="(step, index) in steps"
              :key="step.id"
              class="flex items-center"
              :class="{ 'flex-1': index < steps.length - 1 }"
            >
              <div class="flex items-center">
                <div
                  :class="[
                    'w-10 h-10 rounded-full flex items-center justify-center font-semibold',
                    currentStep >= step.id
                      ? 'bg-emerald-600 text-white'
                      : 'bg-gray-200 text-gray-600'
                  ]"
                >
                  {{ step.id }}
                </div>
                <span class="ml-2 text-sm font-medium text-gray-700">{{ step.label }}</span>
              </div>
              <div
                v-if="index < steps.length - 1"
                :class="[
                  'flex-1 h-0.5 mx-4',
                  currentStep > step.id ? 'bg-emerald-600' : 'bg-gray-200'
                ]"
              />
            </div>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitForm">
          <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <!-- Step 1: Basic Info -->
            <div v-show="currentStep === 1" class="space-y-6">
              <h2 class="text-xl font-semibold text-gray-900 mb-4">Basic Information</h2>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                  <InputLabel for="title" value="Course Title *" />
                  <TextInput
                    id="title"
                    v-model="form.title"
                    type="text"
                    class="mt-1 block w-full"
                    required
                  />
                  <InputError :message="form.errors.title" class="mt-2" />
                </div>

                <div class="md:col-span-2">
                  <InputLabel for="description" value="Description" />
                  <textarea
                    id="description"
                    v-model="form.description"
                    rows="4"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                  />
                </div>

                <div>
                  <InputLabel for="category" value="Category" />
                  <select
                    id="category"
                    v-model="form.category"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                  >
                    <option value="">Select category</option>
                    <option value="compliance">Compliance</option>
                    <option value="safety">Safety</option>
                    <option value="technical">Technical</option>
                    <option value="soft-skills">Soft Skills</option>
                  </select>
                </div>

                <div>
                  <InputLabel for="thumbnail" value="Thumbnail Image" />
                  <input
                    id="thumbnail"
                    type="file"
                    accept="image/*"
                    @change="handleThumbnailChange"
                    class="mt-1 block w-full"
                  />
                </div>
              </div>
            </div>

            <!-- Step 2: Configuration -->
            <div v-show="currentStep === 2" class="space-y-6">
              <h2 class="text-xl font-semibold text-gray-900 mb-4">Course Configuration</h2>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Assessment Settings -->
                <div class="md:col-span-2">
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Assessment</h3>
                </div>
                
                <div>
                  <InputLabel for="passing_score" value="Passing Score (%) *" />
                  <TextInput
                    id="passing_score"
                    v-model.number="form.passing_score"
                    type="number"
                    min="0"
                    max="100"
                    class="mt-1 block w-full"
                    required
                  />
                </div>

                <div>
                  <InputLabel for="max_attempts" value="Max Attempts *" />
                  <TextInput
                    id="max_attempts"
                    v-model.number="form.max_attempts"
                    type="number"
                    min="1"
                    max="10"
                    class="mt-1 block w-full"
                    required
                  />
                </div>

                <div>
                  <InputLabel for="timer_minutes" value="Time Limit (minutes)" />
                  <TextInput
                    id="timer_minutes"
                    v-model.number="form.timer_minutes"
                    type="number"
                    min="0"
                    class="mt-1 block w-full"
                  />
                </div>

                <div>
                  <InputLabel for="validity_days" value="Validity Period (days) *" />
                  <TextInput
                    id="validity_days"
                    v-model.number="form.validity_days"
                    type="number"
                    min="1"
                    class="mt-1 block w-full"
                    required
                  />
                </div>

                <!-- Compliance Settings -->
                <div class="md:col-span-2 mt-6">
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Compliance & Target Audience</h3>
                </div>

                <div>
                  <InputLabel for="target_audience_type" value="Target Audience *" />
                  <select
                    id="target_audience_type"
                    v-model="form.target_audience_type"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                    required
                  >
                    <option value="all">All Employees</option>
                    <option value="department">By Department</option>
                    <option value="role">By Role</option>
                    <option value="location">By Location</option>
                    <option value="gender">By Gender</option>
                  </select>
                </div>

                <div>
                  <InputLabel for="deadline_days_from_joining" value="Deadline (days from joining)" />
                  <TextInput
                    id="deadline_days_from_joining"
                    v-model.number="form.deadline_days_from_joining"
                    type="number"
                    min="0"
                    class="mt-1 block w-full"
                  />
                </div>

                <!-- Anti-Cheat Settings -->
                <div class="md:col-span-2 mt-6">
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Anti-Cheat Settings</h3>
                </div>

                <div class="flex items-center">
                  <input
                    id="prevent_copy_paste"
                    v-model="form.prevent_copy_paste"
                    type="checkbox"
                    class="rounded border-gray-300"
                  />
                  <label for="prevent_copy_paste" class="ml-2 text-sm text-gray-700">
                    Prevent Copy/Paste
                  </label>
                </div>

                <div class="flex items-center">
                  <input
                    id="track_tab_switches"
                    v-model="form.track_tab_switches"
                    type="checkbox"
                    class="rounded border-gray-300"
                  />
                  <label for="track_tab_switches" class="ml-2 text-sm text-gray-700">
                    Track Tab Switches
                  </label>
                </div>

                <div v-if="form.track_tab_switches">
                  <InputLabel for="max_tab_switches" value="Max Tab Switches" />
                  <TextInput
                    id="max_tab_switches"
                    v-model.number="form.max_tab_switches"
                    type="number"
                    min="0"
                    class="mt-1 block w-full"
                  />
                </div>

                <div class="flex items-center">
                  <input
                    id="auto_generate_certificate"
                    v-model="form.auto_generate_certificate"
                    type="checkbox"
                    class="rounded border-gray-300"
                  />
                  <label for="auto_generate_certificate" class="ml-2 text-sm text-gray-700">
                    Auto-Generate Certificate
                  </label>
                </div>
              </div>
            </div>

            <!-- Step 3: Content (Simplified) -->
            <div v-show="currentStep === 3" class="space-y-6">
              <h2 class="text-xl font-semibold text-gray-900 mb-4">Course Content</h2>
              <p class="text-sm text-gray-600 mb-4">
                Add learning materials and questions. You can add more details after creating the course.
              </p>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Number of Questions
                </label>
                <input
                  v-model.number="questionCount"
                  type="number"
                  min="1"
                  max="50"
                  class="mt-1 block w-32 rounded-md border-gray-300 shadow-sm"
                />
                <p class="mt-1 text-xs text-gray-500">
                  You can add detailed questions after creating the course
                </p>
              </div>
            </div>
          </div>

          <!-- Navigation -->
          <div class="flex justify-between">
            <button
              v-if="currentStep > 1"
              type="button"
              @click="currentStep--"
              class="btn-secondary"
            >
              Previous
            </button>
            <div v-else></div>

            <button
              v-if="currentStep < 3"
              type="button"
              @click="currentStep++"
              class="btn-primary"
            >
              Next
            </button>
            <button
              v-else
              type="submit"
              class="btn-primary"
              :disabled="form.processing"
            >
              {{ form.processing ? 'Creating...' : 'Create Course' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

const currentStep = ref(1);
const questionCount = ref(10);

const steps = [
  { id: 1, label: 'Basic Info' },
  { id: 2, label: 'Configuration' },
  { id: 3, label: 'Content' }
];

const form = useForm({
  title: '',
  description: '',
  category: '',
  thumbnail: null,
  passing_score: 70,
  max_attempts: 3,
  timer_minutes: null,
  validity_days: 365,
  target_audience_type: 'all',
  target_audience_config: null,
  deadline_days_from_joining: null,
  prevent_copy_paste: true,
  track_tab_switches: true,
  max_tab_switches: 3,
  auto_generate_certificate: true,
  action_on_fail: 'cooloff',
  cooloff_hours: 24,
  shuffle_questions: true,
  shuffle_options: true,
  contents: [],
  questions: []
});

const handleThumbnailChange = (event) => {
  form.thumbnail = event.target.files[0];
};

const submitForm = () => {
  // Create placeholder content and questions
  form.contents = [
    {
      type: 'text',
      title: 'Course Introduction',
      content: 'Welcome to this training course.',
      is_mandatory: true
    }
  ];
  
  form.questions = Array.from({ length: questionCount.value }, (_, i) => ({
    type: 'mcq',
    question_text: `Question ${i + 1}`,
    options: [
      { text: 'Option A', is_correct: true, score: 1 },
      { text: 'Option B', is_correct: false, score: 0 },
      { text: 'Option C', is_correct: false, score: 0 },
      { text: 'Option D', is_correct: false, score: 0 }
    ],
    max_score: 1,
    score_weight: 1
  }));
  
  form.post(route('hr.lms.store'));
};
</script>
