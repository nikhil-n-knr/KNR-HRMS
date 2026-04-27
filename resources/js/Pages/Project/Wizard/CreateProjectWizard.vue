<template>
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-800 to-purple-700">
                Initialize New Project
            </h1>
            <div class="text-sm text-gray-500">
                Step {{ currentStep }} of 3
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden">
            <div 
                class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 transition-all duration-500 ease-out"
                :style="{ width: `${(currentStep / 3) * 100}%` }"
            ></div>
        </div>

        <!-- Wizard Content -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-xl overflow-hidden min-h-[500px] flex flex-col">
            <div class="flex-1 p-8">
                <Transition name="fade" mode="out-in">
                    <component 
                        :is="steps[currentStep - 1]" 
                        v-model:form="form"
                        :clients="clients"
                        :existingProjects="existingProjects"
                        :managers="managers"
                    />
                </Transition>
            </div>

            <!-- Footer / Navigation -->
            <div class="bg-gray-50/50 border-t border-gray-100 p-6 flex justify-between items-center">
                <button 
                    v-if="currentStep > 1"
                    @click="prevStep"
                    class="px-5 py-2.5 text-gray-600 hover:text-gray-900 font-medium transition-colors"
                >
                    Back
                </button>
                <div v-else></div> <!-- Spacer -->

                <button 
                    @click="nextStep"
                    :disabled="form.processing"
                    class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-semibold shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/40 hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                >
                    <span v-if="form.processing" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                    {{ currentStep === 3 ? 'Launch Project 🚀' : 'Continue' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, markRaw } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useToastStore } from '@/stores/toast';
import MainLayout from '@/Layouts/MainLayout.vue';
import StepBasics from './StepBasics.vue';
import StepModules from './StepModules.vue';
import StepReview from './StepReview.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    clients: Array,
    existingProjects: Array,
    managers: Array
});

const toast = useToastStore();
const currentStep = ref(1);
const steps = [markRaw(StepBasics), markRaw(StepModules), markRaw(StepReview)];

const form = useForm({
    client_id: '',
    name: '',
    code: '',
    visibility: 'team_locked',
    status: 'planning',
    owners: [],
    dates: {
        start: '',
        end: ''
    },
    modules: [], // Array of { name, description, children: [] }
    clone_from_id: null, // New field for backend cloning if preferred, or just used in frontend
    
    // UI Logic
    processing: false
});

const nextStep = () => {
    if (currentStep.value < 3) {
        if (!validateStep(currentStep.value)) return;
        currentStep.value++;
    } else {
        submit();
    }
};

const validateStep = (step) => {
    form.clearErrors();
    let valid = true;
    
    if (step === 1) {
        if (!form.name) { Object.assign(form.errors, { name: 'Name is required' }); valid = false; }
        if (!form.client_id) { Object.assign(form.errors, { client_id: 'Client is required' }); valid = false; }
        if (!form.code) { Object.assign(form.errors, { code: 'Code is required' }); valid = false; }
        if (!form.dates.start) { Object.assign(form.errors, { 'dates.start': 'Start Date is required' }); valid = false; }
        
        if (!valid) {
            toast.error("Please provide all required project essentials.");
        }
    }
    return valid;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const submit = () => {
    form.post(route('projects.store'), {
        onSuccess: () => {
            // Toast handled by global flash
        }
    });
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
