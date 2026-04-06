<template>
    <TalentLayout>
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="md:grid md:grid-cols-3 md:gap-6">
                <div class="md:col-span-1">
                    <div class="px-4 sm:px-0">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Edit Job Posting</h3>
                        <p class="mt-1 text-sm text-gray-600">
                             Update role details, requirements, and hiring config.
                        </p>
                    </div>
                </div>
                <div class="mt-5 md:mt-0 md:col-span-2">
                    <form @submit.prevent="submit">
                        <div class="shadow sm:rounded-md sm:overflow-hidden backdrop-blur-xl bg-white/80">
                            <div class="px-4 py-5 space-y-6 sm:p-6">
                                
                                <!-- Error Banner -->
                                <div v-if="Object.keys(form.errors).length > 0" class="rounded-md bg-red-50 p-4 mb-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
                                            <div class="mt-2 text-sm text-red-700">
                                                <ul role="list" class="list-disc pl-5 space-y-1">
                                                    <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Job ID (Read Only) -->
                                <div>
                                    <InputLabel value="Job Code" />
                                    <TextInput :model-value="job.job_code" title="Auto-generated" disabled class="mt-1 block w-full bg-gray-100 cursor-not-allowed" />
                                </div>

                                <!-- Title -->
                                <div>
                                    <InputLabel for="title">Job Title <span class="text-red-500">*</span></InputLabel>
                                    <TextInput id="title" v-model="form.title" type="text" class="mt-1 block w-full" required />
                                    <InputError :message="form.errors.title" class="mt-2" />
                                </div>

                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    <!-- Department -->
                                    <div>
                                        <BaseSelect
                                            id="department"
                                            v-model="form.department_id"
                                            label="Department"
                                            :error="form.errors.department_id"
                                            required
                                            color="indigo"
                                        >
                                            <template #label>Department <span class="text-red-500">*</span></template>
                                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                        </BaseSelect>
                                    </div>

                                    <!-- Location -->
                                    <div>
                                        <BaseSelect
                                            id="location"
                                            v-model="form.location_id"
                                            label="Location"
                                            :error="form.errors.location_id"
                                            required
                                            color="indigo"
                                        >
                                             <template #label>Location <span class="text-red-500">*</span></template>
                                            <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }} ({{ loc.city }})</option>
                                        </BaseSelect>
                                    </div>
                                    
                                     <!-- Category -->
                                    <div>
                                        <div class="flex justify-between">
                                            <BaseSelect
                                                id="category"
                                                v-model="form.job_category_id"
                                                label="Job Category"
                                                :error="form.errors.job_category_id"
                                                required
                                                color="indigo"
                                                class="w-full"
                                            >
                                                <template #label>Job Category <span class="text-red-500">*</span></template>
                                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                            </BaseSelect>
                                        </div>
                                        <p class="text-xs text-right mt-1">
                                            <button type="button" @click="showCategoryModal = true" class="text-indigo-600 hover:text-indigo-900 underline">
                                                + Create New Category
                                            </button>
                                        </p>
                                    </div>
                                    
                                    <!-- Type -->
                                    <div>
                                        <BaseSelect
                                            id="type"
                                            v-model="form.type"
                                            label="Employment Type"
                                            :error="form.errors.type"
                                            required
                                            color="indigo"
                                        >
                                            <template #label>Employment Type <span class="text-red-500">*</span></template>
                                            <option>Full-time</option>
                                            <option>Part-time</option>
                                            <option>Contract</option>
                                            <option>Internship</option>
                                            <option>Gig Worker</option>
                                            <option>Platform Worker</option>
                                        </BaseSelect>
                                    </div>
                                </div>
                                
                                <!-- Experience & Salary -->
                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 border-t border-gray-200 pt-4">
                                    <div>
                                        <InputLabel for="min_experience" value="Min Exp (Years)" />
                                        <TextInput id="min_experience" v-model="form.min_experience" type="number" min="0" class="mt-1 block w-full" />
                                        <InputError :message="form.errors.min_experience" class="mt-2" />
                                    </div>
                                    <div>
                                        <InputLabel for="max_experience" value="Max Exp (Years)" />
                                        <TextInput id="max_experience" v-model="form.max_experience" type="number" min="0" class="mt-1 block w-full" />
                                        <InputError :message="form.errors.max_experience" class="mt-2" />
                                    </div>
                                    <div>
                                        <InputLabel for="salary_min" value="Min Salary (Annual)" />
                                        <div class="relative mt-1 rounded-md shadow-sm">
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                <span class="text-gray-500 sm:text-sm">₹</span>
                                            </div>
                                            <input type="number" v-model="form.salary_min" id="salary_min" class="block w-full rounded-md border-gray-300 pl-7 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="0.00" />
                                        </div>
                                    </div>
                                    <div>
                                        <InputLabel for="salary_max" value="Max Salary (Annual)" />
                                        <div class="relative mt-1 rounded-md shadow-sm">
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                <span class="text-gray-500 sm:text-sm">₹</span>
                                            </div>
                                            <input type="number" v-model="form.salary_max" id="salary_max" class="block w-full rounded-md border-gray-300 pl-7 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="0.00" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Skills -->
                                <div class="border-t border-gray-200 pt-4">
                                     <MultiSelect
                                        v-model="form.skills"
                                        :options="skills_list"
                                        label="Required Skills"
                                        placeholder="Search skills (e.g. PHP, Sales)..."
                                     />
                                     <InputError :message="form.errors.skills" class="mt-2" />
                                </div>

                                <!-- Description -->
                                <div>
                                    <InputLabel for="description">Job Description <span class="text-red-500">*</span></InputLabel>
                                    <textarea id="description" v-model="form.description" rows="4" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md" placeholder="Key responsibilities and requirements..." required></textarea>
                                    <InputError :message="form.errors.description" class="mt-2" />
                                </div>
                                
                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                     <!-- Valid Through -->
                                    <div>
                                        <InputLabel for="valid_through" value="Posting Valid Until" />
                                        <TextInput id="valid_through" v-model="form.valid_through" type="date" class="mt-1 block w-full" />
                                    </div>
                                     <!-- Status -->
                                    <div>
                                         <BaseSelect
                                            id="status"
                                            v-model="form.status"
                                            label="Status"
                                            :error="form.errors.status"
                                            required
                                            color="indigo"
                                        >
                                            <template #label>Status <span class="text-red-500">*</span></template>
                                            <option value="Draft">Draft (Hidden)</option>
                                            <option value="Published">Published (Live)</option>
                                        </BaseSelect>
                                    </div>
                                </div>
                                
                                <!-- Screening Config -->
                                <div class="border-t border-gray-200 pt-4">
                                    <h4 class="text-md font-medium text-gray-900 mb-2">Application Screening</h4>
                                    <div class="max-w-xl">
                                         <BaseSelect
                                            id="screening_template"
                                            v-model="form.screening_template_id"
                                            label="Screening Template"
                                            :error="form.errors.screening_template_id"
                                            color="indigo"
                                        >
                                            <template #label>Screening Question Set (Optional)</template>
                                            <option value="">None (Standard Form)</option>
                                            <option v-for="t in screening_templates" :key="t.id" :value="t.id">{{ t.name }}</option>
                                        </BaseSelect>
                                        <div class="mt-1 text-xs text-gray-500">
                                            Select a custom question set. You can manage these in the "Screening Templates" tab.
                                        </div>
                                    </div>
                                </div>

                                <!-- Smart Features: Notifications -->
                                <div class="border-t border-gray-200 pt-4">
                                    <h4 class="text-md font-medium text-gray-900 mb-2">Notification Loop</h4>
                                    <p class="text-xs text-gray-500 mb-2">Who should be notified about new applications?</p>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Users -->
                                        <div>
                                            <div class="flex justify-between items-center mb-1">
                                                <label class="text-xs font-semibold text-gray-600">Users</label>
                                                <input type="text" v-model="searchUsers" placeholder="Search..." class="text-xs border-gray-300 rounded px-1 py-0.5 w-24 focus:ring-indigo-500 focus:border-indigo-500">
                                            </div>
                                            <div class="grid grid-cols-1 gap-1 max-h-40 overflow-y-auto border p-2 rounded bg-gray-50">
                                                <div v-for="user in filteredUsers" :key="user.id" class="flex items-center">
                                                    <input type="checkbox" :value="user.id" v-model="form.notification_config.loops" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                    <label class="ml-2 block text-sm text-gray-900 truncate" :title="user.email">{{ user.name }}</label>
                                                </div>
                                                 <div v-if="filteredUsers.length === 0" class="text-xs text-gray-400 text-center py-2">No users found</div>
                                            </div>
                                        </div>

                                        <!-- Teams -->
                                        <div>
                                            <div class="flex justify-between items-center mb-1">
                                                <label class="text-xs font-semibold text-gray-600">Teams</label>
                                                <input type="text" v-model="searchTeams" placeholder="Search..." class="text-xs border-gray-300 rounded px-1 py-0.5 w-24 focus:ring-indigo-500 focus:border-indigo-500">
                                            </div>
                                            <div class="grid grid-cols-1 gap-1 max-h-40 overflow-y-auto border p-2 rounded bg-gray-50">
                                                <div v-for="team in filteredTeams" :key="team.id" class="flex items-center">
                                                    <input type="checkbox" :value="team.id" v-model="form.notification_config.teams" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                    <label class="ml-2 block text-sm text-gray-900 truncate">{{ team.name }}</label>
                                                </div>
                                                <div v-if="filteredTeams.length === 0" class="text-xs text-gray-400 text-center py-2">No teams found</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Category Modal -->
                                <Modal :show="showCategoryModal" @close="showCategoryModal = false">
                                    <div class="p-6">
                                        <h2 class="text-lg font-medium text-gray-900">Create New Job Category</h2>
                                        <p class="mt-1 text-sm text-gray-600">Add a new category to the list.</p>

                                        <div class="mt-6">
                                            <InputLabel for="cat_name" value="Category Name" />
                                            <TextInput id="cat_name" v-model="categoryForm.name" type="text" class="mt-1 block w-full" placeholder="e.g. Graphic Design" />
                                            <InputError :message="categoryForm.errors.name" class="mt-2" />
                                        </div>
                                        
                                        <div class="mt-4">
                                             <InputLabel for="cat_parent" value="Parent Category (Optional)" />
                                             <select id="cat_parent" v-model="categoryForm.parent_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                <option value="">None (Top Level)</option>
                                                <option v-for="cat in categories" :key="'p-'+cat.id" :value="cat.id">{{ cat.name }}</option>
                                             </select>
                                        </div>

                                        <div class="mt-6 flex justify-end">
                                            <SecondaryButton @click="showCategoryModal = false">Cancel</SecondaryButton>
                                            <PrimaryButton class="ml-3" :class="{ 'opacity-25': categoryForm.processing }" :disabled="categoryForm.processing" @click="submitCategory">
                                                Create Category
                                            </PrimaryButton>
                                        </div>
                                    </div>
                                </Modal>

                            </div>
                            <div class="px-4 py-3 bg-gray-50/50 text-right sm:px-6">
                                <Link :href="route('talent.jobs.index')" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-3">
                                    Cancel
                                </Link>
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Update Job Listing
                                </PrimaryButton>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </TalentLayout>
</template>

<script setup>
import TalentLayout from '@/Layouts/TalentLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import MultiSelect from '@/Components/MultiSelect.vue'; // NEW
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    job: Object,
    categories: Array,
    departments: Array,
    locations: Array,
    users: Array,
    teams: Array,
    screening_templates: Array,
    skills_list: Array // NEW
});

// Search Logic
const searchUsers = ref('');
const searchTeams = ref('');

const filteredUsers = computed(() => {
    if (!searchUsers.value) return props.users;
    return props.users.filter(u => u.name.toLowerCase().includes(searchUsers.value.toLowerCase()));
});

const filteredTeams = computed(() => {
    if (!searchTeams.value) return props.teams;
    return props.teams.filter(t => t.name.toLowerCase().includes(searchTeams.value.toLowerCase()));
});

const form = useForm({
    title: props.job.title,
    department_id: props.job.department_id,
    location_id: props.job.location_id,
    job_category_id: props.job.job_category_id,
    screening_template_id: props.job.screening_template_id,
    type: props.job.type,
    description: props.job.description,
    min_experience: props.job.min_experience,
    max_experience: props.job.max_experience,
    salary_min: props.job.salary_min,
    salary_max: props.job.salary_max,
    salary_currency: props.job.salary_currency || 'INR',
    valid_through: props.job.valid_through ? props.job.valid_through.split('T')[0] : '', 
    status: props.job.status,
    skills: props.job.skills || [], // NEW
    notification_config: {
        loops: Array.isArray(props.job.notification_config?.loops) ? props.job.notification_config.loops : [],
        teams: Array.isArray(props.job.notification_config?.teams) ? props.job.notification_config.teams : []
    }
});

// Category Modal Logic
const showCategoryModal = ref(false);
const categoryForm = useForm({
    name: '',
    parent_id: '',
});

const submitCategory = () => {
    categoryForm.post(route('talent.categories.store'), {
        onSuccess: () => {
            showCategoryModal.value = false;
            categoryForm.reset();
        }
    });
};

const submit = () => {
    form.put(route('talent.jobs.update', props.job.id));
};
</script>
