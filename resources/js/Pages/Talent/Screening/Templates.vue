<template>
    <TalentLayout>
        <div class="py-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 px-4 sm:px-0">
                <div>
                    <h2 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight">Intelligence Protocols</h2>
                    <p class="text-sm font-black text-gray-400 uppercase tracking-widest mt-1">Re-usable screening logic for automated assessments</p>
                </div>
                <button @click="openCreateModal" class="w-full md:w-auto px-6 py-3 bg-indigo-600 text-white rounded-2xl text-sm font-black uppercase tracking-widest hover:bg-indigo-700 shadow-xl shadow-indigo-100 transition-all">
                    + Design Protocol
                </button>
            </div>
 
            <!-- List of Templates -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="template in templates" :key="template.id" 
                     class="bg-white/80 backdrop-blur-xl rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-xl hover:border-indigo-200 transition-all duration-300 group relative flex flex-col">
                    
                    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-indigo-500 to-purple-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-4">
                            <div class="h-10 w-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 text-sm font-black group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                {{ template.questions.length }}
                            </div>
                            <span class="text-sm font-black text-gray-400 uppercase tracking-widest">Active System</span>
                        </div>
                        
                        <h3 class="text-base font-black text-slate-900 tracking-tight group-hover:text-indigo-600 transition-colors">{{ template.name }}</h3>
                        <p class="text-sm font-bold text-gray-400 uppercase tracking-widest mt-1">Questions: {{ template.questions.length }} Units</p>
                        
                        <div class="mt-4 pt-4 border-t border-gray-50 flex items-center gap-2">
                            <div class="h-6 w-6 rounded-full bg-slate-100 border border-white shadow-sm flex items-center justify-center text-xs font-black text-slate-500">
                                {{ template.creator?.name?.[0] }}
                            </div>
                            <span class="text-sm font-bold text-slate-500 tracking-tight">Lead: {{ template.creator?.name }}</span>
                        </div>
                    </div>
 
                    <div class="mt-6 flex gap-2">
                        <button @click="editTemplate(template)" class="flex-1 px-4 py-2.5 bg-white border border-gray-200 text-indigo-600 text-sm font-black uppercase tracking-widest rounded-xl shadow-sm hover:bg-indigo-50 hover:border-indigo-100 transition-all">Edit Layout</button>
                        <button @click="deleteTemplate(template)" class="px-4 py-2.5 text-rose-400 hover:text-rose-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                </div>
 
                <div v-if="templates.length === 0" class="col-span-full py-24 text-center bg-white/50 backdrop-blur rounded-3xl border-2 border-dashed border-gray-200">
                    <div class="mx-auto h-16 w-16 text-gray-200 mb-4 font-black">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </div>
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Zero Protocols Found</h3>
                    <p class="mt-1 text-xs text-gray-400 font-medium">Design your first screening protocol to begin automation.</p>
                </div>
            </div>
        </div>

            <!-- Builder Modal (Full Screen) -->
            <div v-if="isModalOpen" class="fixed inset-0 z-50 overflow-hidden flex flex-col bg-gray-100">
                <!-- Modal Header -->
                <div class="bg-white border-b px-6 py-4 flex justify-between items-center shadow-sm z-10">
                    <div>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            placeholder="Template Name (e.g. Sales Screening)" 
                            class="text-xl font-bold border-none focus:ring-0 p-0 placeholder-gray-300 w-96 bg-transparent"
                        >
                        <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
                    </div>
                    <div class="flex gap-3">
                        <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                        <PrimaryButton @click="saveTemplate" :disabled="form.processing">Save Template</PrimaryButton>
                    </div>
                </div>

                <!-- Builder Body -->
                <div class="flex-1 flex overflow-hidden">
                    <!-- Sidebar (Toolbox) -->
                    <div class="w-64 bg-white border-r border-gray-200 p-4 overflow-y-auto">
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Field Types</h4>
                        <div class="space-y-2">
                            <button @click="addField('text')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-indigo-50 text-sm font-medium text-gray-700 flex items-center gap-2 border border-transparent hover:border-indigo-100 transition-all">
                                <span>📝</span> Short Text
                            </button>
                            <button @click="addField('textarea')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-indigo-50 text-sm font-medium text-gray-700 flex items-center gap-2 border border-transparent hover:border-indigo-100 transition-all">
                                <span>📄</span> Long Text
                            </button>
                            <button @click="addField('number')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-indigo-50 text-sm font-medium text-gray-700 flex items-center gap-2 border border-transparent hover:border-indigo-100 transition-all">
                                <span>🔢</span> Number
                            </button>
                            <div class="h-px bg-gray-100 my-2"></div>
                            <button @click="addField('select')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-indigo-50 text-sm font-medium text-gray-700 flex items-center gap-2 border border-transparent hover:border-indigo-100 transition-all">
                                <span>▼</span> Dropdown
                            </button>
                            <button @click="addField('checkbox')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-indigo-50 text-sm font-medium text-gray-700 flex items-center gap-2 border border-transparent hover:border-indigo-100 transition-all">
                                <span>☑</span> Checkboxes
                            </button>
                            <button @click="addField('radio')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-indigo-50 text-sm font-medium text-gray-700 flex items-center gap-2 border border-transparent hover:border-indigo-100 transition-all">
                                <span>⭕</span> Radio Buttons
                            </button>
                             <div class="h-px bg-gray-100 my-2"></div>
                             <button @click="addField('file')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-indigo-50 text-sm font-medium text-gray-700 flex items-center gap-2 border border-transparent hover:border-indigo-100 transition-all">
                                <span>📎</span> File Upload
                            </button>
                             <button @click="addField('video')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-purple-50 text-sm font-medium text-purple-700 flex items-center gap-2 border border-transparent hover:border-purple-100 transition-all">
                                <span>📹</span> Video Answer
                            </button>
                        </div>
                    </div>

                    <!-- Canvas (Preview) -->
                    <div class="flex-1 bg-gray-100 p-8 overflow-y-auto">
                        <div class="max-w-2xl mx-auto space-y-4">
                            <div v-if="form.questions.length === 0" class="text-center py-12 text-gray-400 border-2 border-dashed border-gray-300 rounded-xl">
                                Select a field type from the left to start building.
                            </div>

                            <div 
                                v-for="(q, index) in form.questions" 
                                :key="q.id" 
                                class="bg-white p-4 rounded-xl shadow-sm border-2 transition-all cursor-pointer relative group"
                                :class="selectedIdx === index ? 'border-indigo-500 ring-4 ring-indigo-500/10' : 'border-transparent hover:border-gray-200'"
                                @click="selectedIdx = index"
                            >
                                <!-- Field Preview -->
                                <div class="pointer-events-none">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ q.label }} <span v-if="q.validation.required" class="text-red-500">*</span>
                                    </label>
                                    
                                    <!-- Input types -->
                                    <input v-if="['text','email','url'].includes(q.type)" type="text" disabled class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm bg-gray-50">
                                    <textarea v-if="q.type === 'textarea'" disabled rows="3" class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm bg-gray-50"></textarea>
                                    <input v-if="q.type === 'number'" type="number" disabled class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm bg-gray-50">
                                    
                                    <div v-if="['select'].includes(q.type)" class="relative">
                                         <div class="block w-full rounded-md border border-gray-300 shadow-sm sm:text-sm bg-gray-50 py-2 px-3 text-gray-500">Select an option...</div>
                                    </div>
                                    
                                    <div v-if="['radio', 'checkbox'].includes(q.type)" class="space-y-2">
                                        <div v-for="opt in q.options" :key="opt" class="flex items-center">
                                            <div class="h-4 w-4 border-gray-300 rounded bg-gray-50"></div>
                                            <span class="ml-2 text-sm text-gray-500">{{ opt }}</span>
                                        </div>
                                    </div>

                                    <div v-if="q.type === 'video'" class="bg-purple-50 rounded-lg p-4 text-center border border-purple-100">
                                        Video Recorder Placeholder
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click.stop="form.questions.splice(index, 1); selectedIdx = null" class="p-1 text-red-400 hover:text-red-600 rounded">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Panel (Properties) -->
                    <div class="w-72 bg-white border-l border-gray-200 p-4 overflow-y-auto" v-if="selectedQuestion">
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Properties</h4>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="text-xs font-medium text-gray-700">Question Label</label>
                                <input v-model="selectedQuestion.label" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div class="flex items-center justify-between">
                                <label class="text-xs font-medium text-gray-700">Required</label>
                                <input v-model="selectedQuestion.validation.required" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            </div>

                            <div v-if="['select', 'radio', 'checkbox'].includes(selectedQuestion.type)">
                                <label class="text-xs font-medium text-gray-700 mb-2 block">Options</label>
                                <div class="space-y-2">
                                    <div v-for="(opt, idx) in selectedQuestion.options" :key="idx" class="flex gap-2">
                                        <input v-model="selectedQuestion.options[idx]" type="text" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs py-1">
                                        <button @click="selectedQuestion.options.splice(idx, 1)" class="text-red-400 hover:text-red-600">×</button>
                                    </div>
                                    <button @click="selectedQuestion.options.push('New Option')" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">+ Add Option</button>
                                </div>
                            </div>
                            
                            <div v-if="selectedQuestion.type === 'number'">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="text-xs text-gray-500">Min</label>
                                        <input v-model="selectedQuestion.validation.min" type="number" class="w-full text-xs rounded border-gray-300">
                                    </div>
                                     <div>
                                        <label class="text-xs text-gray-500">Max</label>
                                        <input v-model="selectedQuestion.validation.max" type="number" class="w-full text-xs rounded border-gray-300">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="w-72 bg-gray-50 border-l border-gray-200 p-4 flex items-center justify-center text-gray-400 text-sm" v-else>
                        Select a field to edit properties
                    </div>
                </div>
            </div>
    </TalentLayout>
</template>

<script setup>
import TalentLayout from '@/Layouts/TalentLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    templates: Array
});

const isModalOpen = ref(false);
const editingId = ref(null);
const selectedIdx = ref(null);

const form = useForm({
    name: '',
    questions: []
});

const selectedQuestion = computed(() => {
    if (selectedIdx.value === null) return null;
    return form.questions[selectedIdx.value];
});

const openCreateModal = () => {
    editingId.value = null;
    form.reset();
    form.questions = [];
    isModalOpen.value = true;
};

const editTemplate = (template) => {
    editingId.value = template.id;
    form.name = template.name;
    // Deep copy to avoid mutating prop
    form.questions = JSON.parse(JSON.stringify(template.questions)); 
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    selectedIdx.value = null;
};

const addField = (type) => {
    const id = 'q_' + Math.random().toString(36).substr(2, 9);
    form.questions.push({
        id,
        type,
        label: 'New Question',
        options: ['Option 1', 'Option 2'], // Default for choice types
        validation: {
            required: false
        }
    });
    // Select the new field
    selectedIdx.value = form.questions.length - 1;
};

const saveTemplate = () => {
    const url = editingId.value 
        ? route('talent.screening-templates.update', editingId.value)
        : route('talent.screening-templates.store');
    
    const method = editingId.value ? 'put' : 'post';

    form.submit(method, url, {
        onSuccess: () => closeModal()
    });
};

const deleteTemplate = (template) => {
    if (confirm('Are you sure?')) {
        router.delete(route('talent.screening-templates.destroy', template.id));
    }
};
</script>
