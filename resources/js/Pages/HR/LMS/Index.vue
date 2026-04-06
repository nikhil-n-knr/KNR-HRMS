<template>
  <MainLayout>
    <Head title="LMS - Courses" />
    
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Learning Management System</h1>
            <p class="mt-1 text-sm text-gray-600">Manage training courses and compliance</p>
          </div>
          <Link
            :href="route('hr.lms.create')"
            class="btn-primary"
            v-can="'lms.create'"
          >
            <PlusIcon class="h-5 w-5 mr-2" />
            Create Course
          </Link>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <input
                v-model="filters.search"
                type="text"
                placeholder="Search courses..."
                class="input-field"
                @input="debouncedSearch"
              />
            </div>
            <div>
              <select v-model="filters.category" class="input-field" @change="applyFilters">
                <option value="">All Categories</option>
                <option value="compliance">Compliance</option>
                <option value="safety">Safety</option>
                <option value="technical">Technical</option>
                <option value="soft-skills">Soft Skills</option>
              </select>
            </div>
            <div>
              <select v-model="filters.is_active" class="input-field" @change="applyFilters">
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
              </select>
            </div>
            <div>
              <Link
                :href="route('hr.lms.analytics.index')"
                class="btn-secondary w-full"
                v-can="'lms.analytics'"
              >
                <ChartBarIcon class="h-5 w-5 mr-2" />
                View Analytics
              </Link>
            </div>
          </div>
        </div>

        <!-- Course Grid -->
        <div v-if="courses.data.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="course in courses.data"
            :key="course.id"
            class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-200 overflow-hidden group"
          >
            <!-- Thumbnail -->
            <div class="relative h-48 bg-gradient-to-br from-emerald-500 to-teal-600 overflow-hidden">
              <img
                v-if="course.thumbnail_path"
                :src="`/storage/${course.thumbnail_path}`"
                :alt="course.title"
                class="w-full h-full object-cover"
              />
              <div class="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition-colors" />
              
              <!-- Status Badge -->
              <div class="absolute top-4 right-4">
                <span
                  :class="[
                    'px-3 py-1 rounded-full text-xs font-semibold',
                    course.is_active
                      ? 'bg-green-500 text-white'
                      : 'bg-gray-500 text-white'
                  ]"
                >
                  {{ course.is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>

              <!-- Category -->
              <div class="absolute bottom-4 left-4">
                <span class="px-3 py-1 bg-white/90 backdrop-blur-sm rounded-full text-xs font-medium text-gray-700">
                  {{ course.category || 'General' }}
                </span>
              </div>
            </div>

            <!-- Content -->
            <div class="p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                {{ course.title }}
              </h3>
              <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                {{ course.description || 'No description' }}
              </p>

              <!-- Stats -->
              <div class="grid grid-cols-3 gap-4 mb-4 pb-4 border-b">
                <div class="text-center">
                  <div class="text-2xl font-bold text-emerald-600">
                    {{ course.questions_count }}
                  </div>
                  <div class="text-xs text-gray-500">Questions</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-blue-600">
                    {{ course.assignments_count }}
                  </div>
                  <div class="text-xs text-gray-500">Assigned</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-purple-600">
                    {{ course.certificates_count }}
                  </div>
                  <div class="text-xs text-gray-500">Certified</div>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex gap-2">
                <Link
                  :href="route('hr.lms.show', course.id)"
                  class="flex-1 btn-secondary text-center"
                >
                  View Details
                </Link>
                <Link
                  :href="route('hr.lms.edit', course.id)"
                  class="btn-icon"
                  v-can="'lms.edit'"
                >
                  <PencilIcon class="h-5 w-5" />
                </Link>
                <button
                  @click="deleteCourse(course)"
                  class="btn-icon-danger"
                  v-can="'lms.delete'"
                >
                  <TrashIcon class="h-5 w-5" />
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
          <AcademicCapIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">No courses</h3>
          <p class="mt-1 text-sm text-gray-500">Get started by creating a new course.</p>
          <div class="mt-6">
            <Link
              :href="route('hr.lms.create')"
              class="btn-primary"
              v-can="'lms.create'"
            >
              <PlusIcon class="h-5 w-5 mr-2" />
              Create Course
            </Link>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="courses.data.length" class="mt-6">
          <Pagination :links="courses.links" />
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '../../../Layouts/MainLayout.vue';
import Pagination from '../../../Components/Pagination.vue';
import {
  PlusIcon,
  PencilIcon,
  TrashIcon,
  ChartBarIcon,
  AcademicCapIcon
} from '@heroicons/vue/24/outline';
import { useCan } from '../../../composables/useCan';
import { debounce } from 'lodash';

const props = defineProps({
  courses: Object,
  filters: Object
});

const filters = reactive({
  search: props.filters.search || '',
  category: props.filters.category || '',
  is_active: props.filters.is_active || ''
});

const applyFilters = () => {
  router.get(route('hr.lms.index'), filters, {
    preserveState: true,
    preserveScroll: true
  });
};

const debouncedSearch = debounce(() => {
  applyFilters();
}, 500);

const deleteCourse = (course) => {
  if (confirm(`Are you sure you want to delete "${course.title}"?`)) {
    router.delete(route('hr.lms.destroy', course.id), {
      onSuccess: () => {
        // Success handled by flash message
      }
    });
  }
};
</script>
