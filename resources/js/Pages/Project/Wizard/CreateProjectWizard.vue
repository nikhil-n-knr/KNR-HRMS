<template>
    <div class="min-h-screen bg-slate-50">
        <div class="w-full">
            <section class="bg-white border border-slate-200/70 shadow-sm rounded-2xl p-4 overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-8 text-white rounded-2xl">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                        <div class="space-y-3">
                            <p class="text-sm uppercase tracking-[0.35em] text-indigo-100/75">Projects</p>
                            <h1 class="text-3xl lg:text-4xl font-black tracking-tight">Create New Project</h1>
                            <p class="max-w-2xl text-sm leading-6 text-indigo-100/85">Launch your next initiative with a guided setup experience for essentials, modules, and launch readiness.</p>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full sm:w-auto">
                            <div class="rounded-3xl bg-white/10 border border-white/20 p-4">
                                <p class="text-xs uppercase tracking-[0.3em] text-indigo-100/80">Current Step</p>
                                <p class="mt-2 text-3xl font-black">{{ currentStep }} / 3</p>
                            </div>
                            <div class="rounded-3xl bg-white/10 border border-white/20 p-4">
                                <p class="text-xs uppercase tracking-[0.3em] text-indigo-100/80">Phase</p>
                                <p class="mt-2 text-3xl font-black capitalize">{{ stepLabels[currentStep - 1] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            

            <section class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden mt-1">
                <div class="px-6 py-5 sm:px-8 sm:py-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.35em] text-slate-400">Setup progress</p>
                            <h2 class="mt-2 text-xl font-semibold text-slate-900">Project wizard</h2>
                        </div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-600 shadow-sm">
                            <span class="h-2.5 w-2.5 rounded-full bg-indigo-500"></span>
                            {{ currentStep }} of 3 completed
                        </div>
                    </div>
                </div>
                <div class="h-2 w-full bg-slate-100">
                    <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-purple-500 transition-all duration-500" :style="{ width: `${(currentStep / 3) * 100}%` }"></div>
                </div>
            </section>

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

                <div class="bg-slate-50/90 border-t border-slate-200 p-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-700">Ready to move forward?</p>
                        <p class="text-sm text-slate-500">Use the buttons below to move through the setup steps.</p>
                    </div>
                    <div class="flex flex-wrap gap-3 justify-end">
                        <button
                            v-if="currentStep > 1"
                            @click="prevStep"
                            class="rounded-3xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                        >
                            Back
                        </button>
                        <button
                            @click="nextStep"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 rounded-3xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-xl shadow-indigo-500/20 transition hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="form.processing" class="animate-spin h-4 w-4 rounded-full border-2 border-white border-t-transparent"></span>
                            {{ currentStep === 3 ? 'Launch Project 🚀' : 'Continue' }}
                        </button>
                    </div>
                </div>
                </div>
            </section>
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
const stepLabels = ['Basics', 'Modules', 'Review'];

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
