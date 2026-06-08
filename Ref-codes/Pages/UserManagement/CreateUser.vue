<template>
  <div class="max-w-4xl mx-auto space-y-8">
     <div class="flex items-center gap-4">
        <router-link to="/users" class="p-2 rounded-lg hover:bg-white/50 text-emerald-800">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
        </router-link>
        <h1 class="text-2xl font-bold text-gray-800">Create New User</h1>
     </div>

     <div class="bg-white/60 backdrop-blur-md border border-white/60 rounded-2xl shadow-sm overflow-hidden">
        <!-- Steps -->
        <div class="flex border-b border-gray-200/60">
           <div 
             v-for="(step, index) in ['Basic Information', 'Role Assignment', 'Security']" 
             :key="index"
             class="flex-1 px-6 py-4 text-center text-sm font-medium border-r last:border-r-0 border-gray-200/60 cursor-pointer transition-colors"
             :class="currentStep === index ? 'bg-emerald-50/50 text-emerald-700 border-b-2 border-b-emerald-500' : 'text-gray-500 hover:bg-gray-50/50'"
             @click="currentStep = index"
           >
              {{ index + 1 }}. {{ step }}
           </div>
        </div>

        <div class="p-8">
           <!-- Step 1: Basic Info -->
           <div v-show="currentStep === 0" class="space-y-6">
               <div class="grid grid-cols-2 gap-6">
                   <BaseInput 
                      v-model="form.name"
                      label="Full Name"
                      placeholder="John Doe"
                      minlength="2"
                      maxlength="100"
                      required
                   />
                   <BaseInput 
                      v-model="form.email"
                      label="Email Address"
                      type="email"
                      placeholder="john@example.com"
                      maxlength="100"
                      required
                   />
                   <BaseInput 
                      v-model="form.employee_id"
                      label="Employee ID"
                      placeholder="EMP-001"
                      maxlength="50"
                   />
                   <BaseInput 
                      v-model="form.password"
                      label="Temporary Password"
                      placeholder="secret123"
                      minlength="8"
                   />
                   <BaseSelect
                        v-model="form.department_id"
                        label="Department"
                   >
                        <option value="">No Department</option>
                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                   </BaseSelect>
                   <BaseSelect
                        v-model="form.location_id"
                        label="Location"
                   >
                        <option value="">No Location</option>
                        <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                   </BaseSelect>
               </div>
           </div>

           <!-- Step 2: Roles -->
           <div v-show="currentStep === 1" class="space-y-6">
              <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4 flex items-start gap-3">
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                 </svg>
                 <div>
                    <h4 class="text-sm font-semibold text-blue-800">AI Recommendation</h4>
                    <p class="text-sm text-blue-600">Based on "HR Manager" job title, we recommend the <strong>HR Administrator</strong> role.</p>
                 </div>
              </div>

              <div>
                 <label class="block text-sm font-medium text-gray-700 mb-2">Assign Primary Role</label>
                 <div class="grid grid-cols-2 gap-3">
                    <div 
                        v-for="role in roles" 
                        :key="role.id" 
                        class="p-4 rounded-xl border cursor-pointer transition-all"
                        :class="form.role_ids.includes(role.id) ? 'border-emerald-500 bg-emerald-50 ring-1 ring-emerald-500' : 'border-gray-200 hover:border-emerald-300'"
                        @click="toggleRole(role.id)"
                    >
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-gray-900">{{ role.name }}</span>
                            <div v-if="form.role_ids.includes(role.id)" class="h-5 w-5 rounded-full bg-emerald-500 text-white flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>
                 </div>
              </div>
           </div>

           <!-- Step 3: Security & Review -->
           <div v-show="currentStep === 2" class="space-y-6">
               <div class="space-y-4">
                  <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                     <div>
                        <h4 class="font-medium text-gray-900">Force Two-Factor Authentication</h4>
                        <p class="text-xs text-gray-500">User must set up 2FA on first login.</p>
                     </div>
                     <!-- Toggle Switch Mock -->
                     <button class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none bg-emerald-500" role="switch" aria-checked="true">
                        <span aria-hidden="true" class="translate-x-5 pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                     </button>
                  </div>
               </div>
               
               <div class="mt-8 pt-8 border-t border-gray-100 flex justify-end">
                   <button 
                     @click="saveUser"
                     class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl font-medium shadow-md hover:shadow-lg transition-all"
                   >
                     Create User
                   </button>
               </div>
           </div>
        </div>
        
        <!-- Footer Nav -->
        <div class="px-8 py-4 bg-gray-50/50 border-t border-gray-200/60 flex justify-between">
            <button 
              v-if="currentStep > 0" 
              @click="currentStep--"
              class="px-4 py-2 text-gray-600 hover:text-gray-900 font-medium"
            >
              Back
            </button>
            <div v-else></div> <!-- Spacer -->

            <button 
              v-if="currentStep < 2"
              @click="currentStep++"
              class="px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition"
            >
              Next Step
            </button>
        </div>
     </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
// import { Link } from '@inertiajs/vue3'; 
import BaseInput from '@/Components/BaseInput.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import { useToastStore } from '@/stores/toast';

const toast = useToastStore();
const router = useRouter();
const currentStep = ref(0);
const roles = ref([]);
const departments = ref([]);
const locations = ref([]);
const form = ref({
    name: '',
    email: '',
    employee_id: '',
    password: '',
    department_id: '',
    location_id: '',
    role_ids: []
});

const toggleRole = (id) => {
    if (form.value.role_ids.includes(id)) {
        form.value.role_ids = form.value.role_ids.filter(r => r !== id);
    } else {
        form.value.role_ids.push(id);
    }
};

const saveUser = async () => {
    try {
        await axios.post('/api/admin/users', form.value);
        toast.success('User created successfully');
        router.push('/users'); // Redirect to list using router.push
    } catch (error) {
        console.error("Save Error", error);
        toast.error('Failed to create user. ' + (error.response?.data?.message || ''));
    }
};

onMounted(async () => {
    try {
        const [rRes, dRes, lRes] = await Promise.all([
            axios.get('/api/admin/roles'),
            axios.get('/api/admin/departments'),
            axios.get('/api/admin/locations')
        ]);
        roles.value = rRes.data.data;
        departments.value = dRes.data.data || dRes.data;
        locations.value = lRes.data.data || lRes.data;
    } catch (error) {
        console.error("Failed to load form data", error);
    }
});
</script>
