<template>
  <MainLayout>
    <Head :title="course.title" />
    
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <div>
            <div class="flex items-center gap-3">
              <Link :href="route('hr.lms.index')" class="text-gray-500 hover:text-gray-700">
                <ArrowLeftIcon class="h-5 w-5" />
              </Link>
              <h1 class="text-3xl font-bold text-gray-900">{{ course.title }}</h1>
              <span
                :class="[
                  'px-3 py-1 rounded-full text-xs font-semibold',
                  course.is_active
                    ? 'bg-green-100 text-green-800'
                    : 'bg-gray-100 text-gray-800'
                ]"
              >
                {{ course.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
            <p class="mt-1 text-sm text-gray-600">{{ course.category || 'General' }}</p>
          </div>
          
          <div class="flex gap-2">
            <Link
              :href="route('hr.lms.edit', course.id)"
              class="btn-secondary"
              v-can="'lms.edit'"
            >
              <PencilIcon class="h-5 w-5 mr-2" />
              Edit Course
            </Link>
            <button
              @click="showAssignModal = true"
              class="btn-primary"
              v-can="'lms.edit'"
            >
              <UserPlusIcon class="h-5 w-5 mr-2" />
              Assign Employees
            </button>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                <UsersIcon class="h-6 w-6 text-blue-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Assigned</p>
                <p class="text-2xl font-bold text-gray-900">{{ course.assignments_count }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                <CheckCircleIcon class="h-6 w-6 text-green-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Completed</p>
                <p class="text-2xl font-bold text-gray-900">{{ completedCount }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                <AcademicCapIcon class="h-6 w-6 text-purple-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Certificates</p>
                <p class="text-2xl font-bold text-gray-900">{{ course.certificates_count }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                <DocumentTextIcon class="h-6 w-6 text-yellow-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Questions</p>
                <p class="text-2xl font-bold text-gray-900">{{ course.questions_count }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Course Details & Content Tabs -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
          <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
              <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="activeTab = tab.id"
                :class="[
                  'px-6 py-3 border-b-2 font-medium text-sm',
                  activeTab === tab.id
                    ? 'border-emerald-500 text-emerald-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                ]"
              >
                {{ tab.label }}
              </button>
            </nav>
          </div>

          <div class="p-6">
            <!-- Details Tab -->
            <div v-if="activeTab === 'details'">
              <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Description</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ course.description || 'No description' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Passing Score</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ course.passing_score }}%</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Max Attempts</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ course.max_attempts }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Time Limit</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ course.timer_minutes ? `${course.timer_minutes} minutes` : 'No limit' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Validity Period</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ course.validity_days }} days</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Target Audience</dt>
                  <dd class="mt-1 text-sm text-gray-900 capitalize">{{ course.target_audience_type }}</dd>
                </div>
              </dl>
            </div>

            <!-- Content Tab -->
            <div v-if="activeTab === 'content'">
              <div class="space-y-3">
                <div
                  v-for="(content, index) in course.contents"
                  :key="content.id"
                  class="flex items-center p-4 border border-gray-200 rounded-lg"
                >
                  <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center bg-emerald-100 rounded-full text-emerald-600 font-semibold">
                    {{ index + 1 }}
                  </div>
                  <div class="ml-4 flex-1">
                    <h4 class="text-sm font-medium text-gray-900">{{ content.title }}</h4>
                    <p class="text-xs text-gray-500 capitalize">{{ content.type }}</p>
                  </div>
                  <div class="flex items-center gap-2">
                    <span v-if="content.is_mandatory" class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded">
                      Mandatory
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Questions Tab -->
            <div v-if="activeTab === 'questions'">
              <div class="space-y-4">
                <div
                  v-for="(question, index) in course.questions"
                  :key="question.id"
                  class="p-4 border border-gray-200 rounded-lg"
                >
                  <div class="flex justify-between items-start mb-2">
                    <h4 class="text-sm font-medium text-gray-900">
                      {{ index + 1 }}. {{ question.question_text }}
                    </h4>
                    <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded capitalize">
                      {{ question.type.replace('_', ' ') }}
                    </span>
                  </div>
                  <div class="mt-2 space-y-1">
                    <div
                      v-for="option in question.options"
                      :key="option.id"
                      class="text-sm text-gray-600 flex items-center"
                    >
                      <span
                        :class="[
                          'w-4 h-4 mr-2 rounded-full flex items-center justify-center',
                          option.is_correct ? 'bg-green-500' : 'bg-gray-200'
                        ]"
                      >
                        <CheckIcon v-if="option.is_correct" class="h-3 w-3 text-white" />
                      </span>
                      {{ option.text }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Assignments Tab -->
            <div v-if="activeTab === 'assignments'">
              <BaseDataTable
                :data="course.assignments"
                :columns="assignmentColumns"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Assign Modal -->
    <Modal :show="showAssignModal" @close="showAssignModal = false">
      <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Assign Course to Employees</h3>
        <p class="text-sm text-gray-600 mb-4">You can auto-assign based on target audience or manually select employees.</p>
        
        <div class="space-y-4">
          <button
            @click="autoAssign"
            class="w-full btn-primary"
          >
            Auto-Assign to Target Audience
          </button>
          
          <div class="text-center text-sm text-gray-500">or</div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Employees</label>
            <!-- Add employee selector component here -->
            <p class="text-xs text-gray-500">Employee selector would go here</p>
          </div>
        </div>
      </div>
    </Modal>
  </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import {
  ArrowLeftIcon,
  PencilIcon,
  UserPlusIcon,
  UsersIcon,
  CheckCircleIcon,
  AcademicCapIcon,
  DocumentTextIcon,
  CheckIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  course: Object
});

const activeTab = ref('details');
const showAssignModal = ref(false);

const tabs = [
  { id: 'details', label: 'Details' },
  { id: 'content', label: 'Content' },
  { id: 'questions', label: 'Questions' },
  { id: 'assignments', label: 'Assignments' }
];

const completedCount = computed(() => {
  return props.course.assignments?.filter(a => a.status === 'completed').length || 0;
});

const assignmentColumns = [
  { key: 'employee.full_name', label: 'Employee' },
  { key: 'assigned_on', label: 'Assigned On' },
  { key: 'due_date', label: 'Due Date' },
  { key: 'status', label: 'Status' }
];

const autoAssign = () => {
  router.post(route('hr.lms.assign', props.course.id), {
    auto_assign: true
  }, {
    preserveScroll: true,
    onSuccess: () => {
      showAssignModal.value = false;
    }
  });
};
</script>
