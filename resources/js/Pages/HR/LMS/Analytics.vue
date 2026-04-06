<template>
  <MainLayout>
    <Head title="LMS Analytics" />
    
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">LMS Analytics & Compliance</h1>
            <p class="mt-1 text-sm text-gray-600">Track training compliance across departments</p>
          </div>
          <button
            @click="exportReport"
            class="btn-secondary"
          >
            <ArrowDownTrayIcon class="h-5 w-5 mr-2" />
            Export Report
          </button>
        </div>

        <!-- Compliance Matrix -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Compliance Matrix</h2>
            <p class="text-sm text-gray-600">Department-wise course completion rates</p>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50">
                    Department
                  </th>
                  <th
                    v-for="course in compliance_matrix.courses"
                    :key="course.id"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    {{ course.title }}
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr
                  v-for="row in compliance_matrix.matrix"
                  :key="row.department"
                  class="hover:bg-gray-50"
                >
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 sticky left-0 bg-white">
                    {{ row.department }}
                  </td>
                  <td
                    v-for="(stats, courseId) in row.courses"
                    :key="courseId"
                    class="px-6 py-4 whitespace-nowrap text-sm"
                  >
                    <div class="flex items-center">
                      <div
                        :class="[
                          'px-3 py-1 rounded-full text-xs font-semibold',
                          getComplianceClass(stats.status)
                        ]"
                      >
                        {{ stats.completion_rate }}%
                      </div>
                      <div class="ml-2 text-xs text-gray-500">
                        {{ stats.completed }}/{{ stats.total }}
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Defaulters List -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Overdue Assignments</h2>
            <p class="text-sm text-gray-600">Employees who have not completed their training</p>
          </div>
          
          <div v-if="defaulters.length" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    Employee
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    Department
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    Course
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    Due Date
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    Days Overdue
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr
                  v-for="defaulter in defaulters"
                  :key="`${defaulter.emp_code}-${defaulter.course_title}`"
                  class="hover:bg-gray-50"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ defaulter.employee_name }}</div>
                    <div class="text-sm text-gray-500">{{ defaulter.emp_code }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ defaulter.department }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-900">
                    {{ defaulter.course_title }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ defaulter.due_date }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                      {{ defaulter.days_overdue }} days
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <button
                      @click="sendReminder(defaulter)"
                      class="text-emerald-600 hover:text-emerald-900"
                    >
                      Send Reminder
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <div v-else class="px-6 py-12 text-center">
            <CheckCircleIcon class="mx-auto h-12 w-12 text-green-500" />
            <p class="mt-2 text-sm text-gray-600">No overdue assignments! 🎉</p>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import {
  ArrowDownTrayIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  compliance_matrix: Object,
  defaulters: Array
});

const getComplianceClass = (status) => {
  const classes = {
    excellent: 'bg-green-100 text-green-800',
    good: 'bg-blue-100 text-blue-800',
    warning: 'bg-yellow-100 text-yellow-800',
    critical: 'bg-red-100 text-red-800'
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const exportReport = () => {
  window.location.href = route('hr.lms.analytics.export');
};

const sendReminder = (defaulter) => {
  if (confirm(`Send reminder to ${defaulter.employee_name}?`)) {
    // Implementation for sending reminder
    router.post(route('hr.lms.send-reminder'), {
      emp_code: defaulter.emp_code
    }, {
      preserveScroll: true,
      onSuccess: () => {
        alert('Reminder sent successfully');
      }
    });
  }
};
</script>
