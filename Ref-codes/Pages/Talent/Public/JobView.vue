<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-200 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <!-- Job Header -->
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl overflow-hidden mb-8 border border-white/50">
                <div class="bg-indigo-600 px-8 py-10 text-white relative overflow-hidden">
                    <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] hover:opacity-20 transition-opacity duration-700"></div>
                    <div class="relative z-10">
                        <h1 class="text-4xl font-extrabold tracking-tight">{{ job.title }}</h1>
                        <div class="mt-4 flex flex-wrap gap-4 text-indigo-100 text-sm font-medium uppercase tracking-wider">
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                {{ job.department?.name }}
                            </span>
                             <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                {{ job.location?.city }}
                             </span>
                             <span class="bg-white/20 px-2 py-0.5 rounded">{{ job.type }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="px-8 py-8">
                     <div class="prose prose-indigo max-w-none text-gray-600">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">About the Role</h3>
                        <p class="whitespace-pre-line">{{ job.description }}</p>
                    </div>

                    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm bg-gray-50 p-4 rounded-lg border border-gray-100">
                         <div v-if="job.min_experience || job.max_experience">
                            <span class="block text-gray-400 font-medium">Experience</span>
                            <span class="font-semibold text-gray-800">{{ job.min_experience ?? 0 }} - {{ job.max_experience ?? '+' }} Years</span>
                         </div>
                         <div v-if="job.salary_min">
                            <span class="block text-gray-400 font-medium">Salary Range</span>
                            <span class="font-semibold text-gray-800">{{ job.salary_currency }} {{ job.salary_min }} - {{ job.salary_max }}</span>
                         </div>
                    </div>
                </div>
            </div>

            <!-- Application Form -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-xl font-bold text-gray-900">Apply for this position</h2>
                    <p class="text-sm text-gray-500">Please fill out the details below.</p>
                </div>

                <div class="p-8">
                     <!-- Success Message -->
                     <div v-if="$page.props.flash.success" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        {{ $page.props.flash.success }}
                    </div>

                    <form @submit.prevent="submit" v-else>
                        <!-- Basic Info -->
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2 mb-8">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700">First Name <span class="text-red-500">*</span></label>
                                <input id="first_name" v-model="form.first_name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <p v-if="form.errors.first_name" class="text-red-500 text-xs mt-1">{{ form.errors.first_name }}</p>
                            </div>

                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name <span class="text-red-500">*</span></label>
                                <input id="last_name" v-model="form.last_name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <p v-if="form.errors.last_name" class="text-red-500 text-xs mt-1">{{ form.errors.last_name }}</p>
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email Address <span class="text-red-500">*</span></label>
                                <input id="email" v-model="form.email" type="email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number <span class="text-red-500">*</span></label>
                                <input id="phone" v-model="form.phone" type="tel" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <p v-if="form.errors.phone" class="text-red-500 text-xs mt-1">{{ form.errors.phone }}</p>
                            </div>
                            
                            <div class="sm:col-span-2">
                                <label for="resume" class="block text-sm font-medium text-gray-700">Resume/CV (PDF) <span class="text-red-500">*</span></label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:bg-gray-50 transition-colors cursor-pointer" @click="$refs.resumeInput.click()">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600 justify-center">
                                            <label class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none">
                                                <span>Upload a file</span>
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500" v-if="!form.resume">PDF up to 5MB</p>
                                        <p class="text-sm font-bold text-indigo-600" v-else>Selected: {{ form.resume.name }}</p>
                                    </div>
                                    <input ref="resumeInput" type="file" class="sr-only" accept=".pdf,.docx,.doc" @change="e => form.resume = e.target.files[0]">
                                </div>
                                <p v-if="form.errors.resume" class="text-red-500 text-xs mt-1">{{ form.errors.resume }}</p>
                            </div>
                        </div>

                        <!-- Dynamic Questions -->
                        <div v-if="job.screening_template?.questions?.length > 0" class="border-t border-gray-200 pt-8 mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Initial Screening Questions</h3>
                            
                            <div class="space-y-6">
                                <div v-for="q in job.screening_template.questions" :key="q.id">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ q.label }} <span v-if="q.validation.required" class="text-red-500">*</span>
                                    </label>

                                    <!-- Text Input -->
                                    <input v-if="['text','email','url'].includes(q.type)" 
                                        type="text" 
                                        v-model="form.answers[q.id]" 
                                        :required="q.validation.required"
                                        :placeholder="q.placeholder"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >

                                    <!-- Text Area -->
                                    <textarea v-if="q.type === 'textarea'" 
                                        v-model="form.answers[q.id]"
                                        :required="q.validation.required"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    ></textarea>

                                    <!-- Number -->
                                    <input v-if="q.type === 'number'" 
                                        type="number" 
                                        v-model="form.answers[q.id]"
                                        :required="q.validation.required"
                                        :min="q.validation.min"
                                        :max="q.validation.max"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >

                                    <!-- Select -->
                                    <select v-if="q.type === 'select'" 
                                        v-model="form.answers[q.id]"
                                        :required="q.validation.required"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >
                                        <option value="">Please select...</option>
                                        <option v-for="opt in q.options" :key="opt" :value="opt">{{ opt }}</option>
                                    </select>

                                    <!-- Checkboxes -->
                                    <div v-if="q.type === 'checkbox'" class="mt-2 space-y-2">
                                        <div v-for="opt in q.options" :key="opt" class="flex items-center">
                                            <input :id="q.id+opt" type="checkbox" :value="opt" v-model="form.answers[q.id]" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <label :for="q.id+opt" class="ml-2 block text-sm text-gray-900">{{ opt }}</label>
                                        </div>
                                    </div>
                                    
                                     <!-- Radio -->
                                    <div v-if="q.type === 'radio'" class="mt-2 space-y-2">
                                        <div v-for="opt in q.options" :key="opt" class="flex items-center">
                                            <input :id="q.id+opt" type="radio" :name="q.id" :value="opt" v-model="form.answers[q.id]" class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <label :for="q.id+opt" class="ml-2 block text-sm text-gray-900">{{ opt }}</label>
                                        </div>
                                    </div>

                                    <!-- Video Answer (Placeholder) -->
                                    <div v-if="q.type === 'video'" class="mt-1 p-4 bg-purple-50 rounded-lg border border-purple-100 border-dashed">
                                        <p class="text-sm font-medium text-purple-700 mb-2">📹 Video Answer Required</p>
                                        <input type="file" accept="video/*" capture="user" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                                        <p class="text-xs text-gray-500 mt-1">Upload or record a video response.</p>
                                    </div>
                                    
                                    <p v-if="form.errors['answers.'+q.id]" class="text-red-500 text-xs mt-1">This field is required.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Cover Letter -->
                        <div class="mb-8">
                             <label for="cover_letter" class="block text-sm font-medium text-gray-700">Cover Letter (Optional)</label>
                             <textarea id="cover_letter" v-model="form.cover_letter" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Tell us why you are a great fit..."></textarea>
                        </div>

                        <div class="flex justify-end pt-5">
                            <button type="submit" :disabled="form.processing" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-3 px-8 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all disabled:opacity-50">
                                <span v-if="form.processing">Submitting...</span>
                                <span v-else>Submit Application</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="text-center mt-8 text-gray-400 text-sm">
                &copy; {{ new Date().getFullYear() }} All rights reserved.
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps({
    job: Object
});

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    cover_letter: '',
    resume: null,
    answers: {} // Dynamic answers
});

// Initialize answers for reactivity
onMounted(() => {
    if (props.job.screening_template?.questions) {
        props.job.screening_template.questions.forEach(q => {
             // For checkboxes, init as array
             if (q.type === 'checkbox') {
                 form.answers[q.id] = [];
             } else {
                 form.answers[q.id] = '';
             }
        });
    }
});

const submit = () => {
    console.log('Submitting application...', form.data());
    form.post(route('careers.apply', props.job.id), {
        forceFormData: true, // Important for file upload
        preserveScroll: true,
        onError: (errors) => {
            console.error('Submission Errors:', errors);
        },
        onSuccess: () => {
             console.log('Submission Successful!');
        }
    });
};
</script>
