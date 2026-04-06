<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Add New Employee</h1>
        <p class="text-sm text-gray-500 mt-1">Create a new core profile for your workforce.</p>
      </div>
      <Link 
        :href="route('admin.employees.index')"
        class="text-gray-500 hover:text-gray-700 font-medium text-sm flex items-center gap-1 transition-colors"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to List
      </Link>
    </div>

    <!-- Form Card -->
    <div class="bg-white/80 backdrop-blur-xl rounded-2xl border border-white/50 shadow-xl p-6 sm:p-8">
      <form @submit.prevent="submit" class="space-y-6">
        
        <!-- Section 1: Identity -->
        <div>
            <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-100 pb-2 mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                </svg>
                Identity Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Employee Code -->
                <BaseInput 
                    v-model="form.employee_code"
                    label="Employee Code"
                    placeholder="e.g. EMP-101"
                    required
                    minlength="2"
                    maxlength="20"
                    :error="form.errors.employee_code"
                />
                
                <!-- Email -->
                <BaseInput 
                    v-model="form.email"
                    type="email"
                    label="Work Email"
                    placeholder="email@company.com"
                    :error="form.errors.email"
                />

                <!-- First Name -->
                <BaseInput 
                    v-model="form.first_name"
                    label="First Name"
                    required
                    minlength="2"
                    :error="form.errors.first_name"
                />

                <!-- Last Name -->
                <BaseInput 
                    v-model="form.last_name"
                    label="Last Name"
                    required
                    minlength="2"
                    :error="form.errors.last_name"
                />
                
                <!-- Phone -->
                 <BaseInput 
                    v-model="form.phone"
                    label="Mobile Number"
                    placeholder="+1 234..."
                />
            </div>
        </div>

        <!-- Section 2: Job Details -->
        <div>
            <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-100 pb-2 mb-4 mt-2 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Job Details
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Designation -->
                 <BaseInput 
                    v-model="form.designation"
                    label="Designation/Job Title"
                    placeholder="Software Engineer"
                    required
                    :error="form.errors.designation"
                />

                <!-- Department -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Department <span class="text-red-500">*</span></label>
                    <select 
                        v-model="form.department_id" 
                        required 
                        class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white/50"
                    >
                        <option value="" disabled>Select Department</option>
                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                    </select>
                    <div v-if="form.errors.department_id" class="text-red-500 text-xs mt-1">{{ form.errors.department_id }}</div>
                </div>

                <!-- Location -->
                 <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location <span class="text-red-500">*</span></label>
                    <select 
                        v-model="form.location_id" 
                        required 
                        class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white/50"
                    >
                        <option value="" disabled>Select Location</option>
                        <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                    </select>
                    <div v-if="form.errors.location_id" class="text-red-500 text-xs mt-1">{{ form.errors.location_id }}</div>
                </div>

                <!-- Joining Date -->
                <div>
                     <label class="block text-sm font-medium text-gray-700 mb-1">Joining Date <span class="text-red-500">*</span></label>
                     <input 
                        v-model="form.joining_date"
                        type="date"
                        required
                        class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white/50"
                     />
                </div>

                 <!-- Status -->
                 <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select 
                        v-model="form.status" 
                        required 
                        class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white/50"
                    >
                        <option value="active">Active</option>
                        <option value="probation">Probation</option>
                        <option value="notice_period">Notice Period</option>
                        <option value="on_leave">On Leave</option>
                        <option value="terminated">Terminated</option>
                        <option value="resigned">Resigned</option>
                    </select>
                     <div v-if="form.errors.status" class="text-red-500 text-xs mt-1">{{ form.errors.status }}</div>
                </div>

                <!-- Employment Type -->
                 <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
                    <select 
                        v-model="form.employment_type" 
                        required 
                        class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white/50"
                    >
                        <option value="full_time">Full Time</option>
                        <option value="part_time">Part Time</option>
                        <option value="contract">Contract</option>
                        <option value="intern">Intern</option>
                    </select>
                    <div v-if="form.errors.employment_type" class="text-red-500 text-xs mt-1">{{ form.errors.employment_type }}</div>
                </div>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="flex justify-end pt-6 border-t border-gray-100">
        <Link 
         :href="route('admin.employees.index')"
         class="px-5 py-2 mr-3 text-gray-600 bg-white border border-gray-300 rounded-xl font-medium hover:bg-gray-50 transition-colors inline-block"
        >
         Cancel
        </Link>
            <button 
                type="submit"
                :disabled="form.processing"
                class="px-6 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl font-medium shadow-lg hover:shadow-xl hover:scale-[1.02] transition-all disabled:opacity-70 disabled:cursor-not-allowed flex items-center gap-2"
            >
                <div v-if="form.processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                <span>Create Profile</span>
            </button>
        </div>

      </form>
    </div>
  </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import BaseInput from '@/Components/BaseInput.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    departments: Array,
    locations: Array
});

const form = useForm({
    employee_code: '',
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    designation: '',
    department_id: '',
    location_id: '',
    joining_date: '',
    status: 'active',
    employment_type: 'full_time'
});

const submit = () => {
    form.post(route('admin.employees.store'), {
        preserveScroll: true,
        onSuccess: () => {
             // Redirect handled by controller
        }
    });
};
</script>
