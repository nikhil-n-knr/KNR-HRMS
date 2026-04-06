<template>
  <MainLayout>
    <Head title="Question Bank" />
    
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Question Bank</h1>
            <p class="mt-1 text-sm text-gray-600">Manage exam questions across all courses</p>
          </div>
          
          <div class="flex gap-2">
            <button
              @click="showBulkUpload = true"
              class="btn-secondary"
            >
              <ArrowUpTrayIcon class="h-5 w-5 mr-2" />
              Bulk Upload
            </button>
            <button
              @click="showCreateModal = true"
              class="btn-primary"
            >
              <PlusIcon class="h-5 w-5 mr-2" />
              Add Question
            </button>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
              <input
                v-model="filters.search"
                type="text"
                placeholder="Search questions..."
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                @input="debouncedSearch"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Course</label>
              <select
                v-model="filters.course_id"
                @change="applyFilters"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
              >
                <option value="">All Courses</option>
                <option v-for="course in courses" :key="course.id" :value="course.id">
                  {{ course.title }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
              <select
                v-model="filters.type"
                @change="applyFilters"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
              >
                <option value="">All Types</option>
                <option value="mcq">Multiple Choice</option>
                <option value="multi_select">Multi-Select</option>
                <option value="text">Text Answer</option>
                <option value="scenario">Scenario Based</option>
                <option value="image_based">Image Based</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Difficulty</label>
              <select
                v-model="filters.difficulty"
                @change="applyFilters"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
              >
                <option value="">All Levels</option>
                <option value="easy">Easy</option>
                <option value="medium">Medium</option>
                <option value="hard">Hard</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Questions Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Question
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Course
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Type
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Difficulty
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Score
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="question in questions.data" :key="question.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900 line-clamp-2">
                    {{ question.question_text }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-500">{{ question.course?.title }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                    {{ formatType(question.type) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                      getDifficultyClass(question.difficulty)
                    ]"
                  >
                    {{ question.difficulty || 'Medium' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ question.max_score }} pts
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button
                    @click="previewQuestion(question)"
                    class="text-emerald-600 hover:text-emerald-900 mr-3"
                  >
                    View
                  </button>
                  <button
                    @click="editQuestion(question)"
                    class="text-indigo-600 hover:text-indigo-900 mr-3"
                  >
                    Edit
                  </button>
                  <button
                    @click="confirmDelete(question)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <Pagination v-bind="questions" />
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Modal :show="showCreateModal" @close="showCreateModal = false" max-width="4xl">
      <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
          {{ editingQuestion ? 'Edit Question' : 'Add New Question' }}
        </h3>

        <form @submit.prevent="saveQuestion" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <InputLabel for="course_id" value="Course *" />
              <select
                v-model="form.course_id"
                id="course_id"
                class="mt-1 block w-full rounded-md border-gray-300"
                required
              >
                <option value="">Select Course</option>
                <option v-for="course in courses" :key="course.id" :value="course.id">
                  {{ course.title }}
                </option>
              </select>
              <InputError :message="form.errors.course_id" class="mt-2" />
            </div>

            <div>
              <InputLabel for="type" value="Question Type *" />
              <select
                v-model="form.type"
                id="type"
                class="mt-1 block w-full rounded-md border-gray-300"
                required
              >
                <option value="mcq">Multiple Choice</option>
                <option value="multi_select">Multi-Select</option>
                <option value="text">Text Answer</option>
                <option value="scenario">Scenario Based</option>
              </select>
            </div>
          </div>

          <div>
            <InputLabel for="question_text" value="Question Text *" />
            <textarea
              v-model="form.question_text"
              id="question_text"
              rows="3"
              class="mt-1 block w-full rounded-md border-gray-300"
              required
            />
            <InputError :message="form.errors.question_text" class="mt-2" />
          </div>

          <div v-if="form.type === 'mcq' || form.type === 'multi_select'" class="space-y-3">
            <div class="flex justify-between items-center">
              <label class="block text-sm font-medium text-gray-700">Options</label>
              <button
                type="button"
                @click="addOption"
                class="text-sm text-emerald-600 hover:text-emerald-800"
              >
                + Add Option
              </button>
            </div>

            <div v-for="(option, index) in form.options" :key="index" class="flex gap-2">
              <input
                v-model="option.text"
                type="text"
                placeholder="Option text"
                class="flex-1 rounded-md border-gray-300"
                required
              />
              <label class="flex items-center">
                <input
                  v-model="option.is_correct"
                  type="checkbox"
                  class="rounded border-gray-300"
                />
                <span class="ml-2 text-sm text-gray-600">Correct</span>
              </label>
              <button
                type="button"
                @click="removeOption(index)"
                class="text-red-600 hover:text-red-800"
              >
                <XMarkIcon class="h-5 w-5" />
              </button>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <InputLabel for="max_score" value="Max Score *" />
              <TextInput
                v-model.number="form.max_score"
                id="max_score"
                type="number"
                min="1"
                class="mt-1 block w-full"
                required
              />
            </div>

            <div>
              <InputLabel for="difficulty" value="Difficulty" />
              <select
                v-model="form.difficulty"
                id="difficulty"
                class="mt-1 block w-full rounded-md border-gray-300"
              >
                <option value="easy">Easy</option>
                <option value="medium">Medium</option>
                <option value="hard">Hard</option>
              </select>
            </div>
          </div>

          <div class="flex justify-end gap-2">
            <button
              type="button"
              @click="showCreateModal = false"
              class="btn-secondary"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="btn-primary"
            >
              {{ form.processing ? 'Saving...' : 'Save Question' }}
            </button>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Bulk Upload Modal -->
    <Modal :show="showBulkUpload" @close="showBulkUpload = false">
      <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Bulk Upload Questions</h3>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Course</label>
            <select
              v-model="uploadForm.course_id"
              class="w-full rounded-md border-gray-300"
              required
            >
              <option value="">Select Course</option>
              <option v-for="course in courses" :key="course.id" :value="course.id">
                {{ course.title }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Upload CSV File</label>
            <input
              type="file"
              accept=".csv,.txt"
              @change="handleFileUpload"
              class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100"
            />
            <p class="mt-1 text-xs text-gray-500">
              CSV format: type, question_text, option_1, option_2, option_3, option_4, correct_answer, max_score, difficulty
            </p>
          </div>

          <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
            <div class="flex">
              <InformationCircleIcon class="h-5 w-5 text-blue-400 mr-2" />
              <div class="text-sm text-blue-700">
                <p class="font-medium mb-1">Download Template</p>
                <a
                  :href="route('hr.lms.questions.template')"
                  class="text-blue-600 hover:text-blue-800 underline"
                >
                  Click here to download CSV template
                </a>
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-2">
            <button
              type="button"
              @click="showBulkUpload = false"
              class="btn-secondary"
            >
              Cancel
            </button>
            <button
              @click="submitBulkUpload"
              :disabled="uploadForm.processing || !uploadForm.file || !uploadForm.course_id"
              class="btn-primary"
            >
              {{ uploadForm.processing ? 'Uploading...' : 'Upload' }}
            </button>
          </div>
        </div>
      </div>
    </Modal>

    <!-- Preview Modal -->
    <Modal :show="showPreview" @close="showPreview = false">
      <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Question Preview</h3>

        <div v-if="previewingQuestion" class="space-y-4">
          <div>
            <p class="text-sm font-medium text-gray-700 mb-2">Question</p>
            <p class="text-gray-900">{{ previewingQuestion.question_text }}</p>
          </div>

          <div v-if="previewingQuestion.options">
            <p class="text-sm font-medium text-gray-700 mb-2">Options</p>
            <div class="space-y-2">
              <div
                v-for="option in previewingQuestion.options"
                :key="option.id"
                :class="[
                  'p-3 rounded-md border',
                  option.is_correct ? 'border-green-500 bg-green-50' : 'border-gray-200'
                ]"
              >
                <div class="flex items-center justify-between">
                  <span>{{ option.text }}</span>
                  <span v-if="option.is_correct" class="text-xs font-semibold text-green-700">
                    ✓ Correct
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4 pt-4 border-t">
            <div>
              <p class="text-sm text-gray-500">Type</p>
              <p class="font-medium">{{ formatType(previewingQuestion.type) }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Difficulty</p>
              <p class="font-medium capitalize">{{ previewingQuestion.difficulty || 'Medium' }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Max Score</p>
              <p class="font-medium">{{ previewingQuestion.max_score }} points</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Course</p>
              <p class="font-medium">{{ previewingQuestion.course?.title }}</p>
            </div>
          </div>
        </div>
      </div>
    </Modal>
  </MainLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Pagination from '@/Components/Pagination.vue';
import {
  PlusIcon,
  ArrowUpTrayIcon,
  XMarkIcon,
  InformationCircleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  questions: Object,
  courses: Array,
  filters: Object
});

const showCreateModal = ref(false);
const showBulkUpload = ref(false);
const showPreview = ref(false);
const editingQuestion = ref(null);
const previewingQuestion = ref(null);

const filters = reactive({
  search: props.filters?.search || '',
  course_id: props.filters?.course_id || '',
  type: props.filters?.type || '',
  difficulty: props.filters?.difficulty || ''
});

const form = useForm({
  course_id: '',
  type: 'mcq',
  question_text: '',
  options: [
    { text: '', is_correct: false },
    { text: '', is_correct: false }
  ],
  max_score: 1,
  difficulty: 'medium'
});

const uploadForm = useForm({
  course_id: '',
  file: null
});

let searchTimeout;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    applyFilters();
  }, 500);
};

const applyFilters = () => {
  router.get(route('hr.lms.questions.index'), filters, {
    preserveState: true,
    preserveScroll: true
  });
};

const addOption = () => {
  form.options.push({ text: '', is_correct: false });
};

const removeOption = (index) => {
  if (form.options.length > 2) {
    form.options.splice(index, 1);
  }
};

const saveQuestion = () => {
  if (editingQuestion.value) {
    form.put(route('hr.lms.questions.update', editingQuestion.value.id), {
      onSuccess: () => {
        showCreateModal.value = false;
        form.reset();
      }
    });
  } else {
    form.post(route('hr.lms.questions.store'), {
      onSuccess: () => {
        showCreateModal.value = false;
        form.reset();
      }
    });
  }
};

const editQuestion = (question) => {
  editingQuestion.value = question;
  form.course_id = question.course_id;
  form.type = question.type;
  form.question_text = question.question_text;
  form.options = question.options || [];
  form.max_score = question.max_score;
  form.difficulty = question.difficulty;
  showCreateModal.value = true;
};

const previewQuestion = (question) => {
  previewingQuestion.value = question;
  showPreview.value = true;
};

const confirmDelete = (question) => {
  if (confirm('Are you sure you want to delete this question?')) {
    router.delete(route('hr.lms.questions.destroy', question.id));
  }
};

const handleFileUpload = (event) => {
  uploadForm.file = event.target.files[0];
};

const submitBulkUpload = () => {
  uploadForm.post(route('hr.lms.questions.bulk-import'), {
    onSuccess: () => {
      showBulkUpload.value = false;
      uploadForm.reset();
    }
  });
};

const formatType = (type) => {
  const types = {
    mcq: 'Multiple Choice',
    multi_select: 'Multi-Select',
    text: 'Text Answer',
    scenario: 'Scenario',
    image_based: 'Image Based'
  };
  return types[type] || type;
};

const getDifficultyClass = (difficulty) => {
  const classes = {
    easy: 'bg-green-100 text-green-800',
    medium: 'bg-yellow-100 text-yellow-800',
    hard: 'bg-red-100 text-red-800'
  };
  return classes[difficulty] || classes.medium;
};
</script>
