<template>
    <Modal :show="show" @close="$emit('close')" maxWidth="2xl">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Raise a New Ticket</h2>
            
            <form @submit.prevent="submit">
                <div class="space-y-4">
                    <!-- Project & Module Row -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Project" />
                            <select v-model="form.project_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                <option value="" disabled>Select Project</option>
                                <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                            <InputError :message="form.errors.project_id" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel value="Module (Optional)" />
                            <select v-model="form.module_id" :disabled="!form.project_id || loadingModules" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm disabled:bg-gray-100">
                                <option value="">-- General --</option>
                                <option v-for="m in modules" :key="m.id" :value="m.id">{{ m.name }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Subject -->
                    <div>
                        <InputLabel value="Subject" />
                        <TextInput v-model="form.subject" class="mt-1 block w-full" placeholder="Brief summary of the issue" />
                        <InputError :message="form.errors.subject" class="mt-2" />
                    </div>

                    <!-- Severity & Priority Row -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Severity (Business Impact)" />
                            <select v-model="form.severity" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                <option value="low">Low (Minor/Cosmetic)</option>
                                <option value="medium">Medium (Standard)</option>
                                <option value="high">High (Major Functionality)</option>
                                <option value="critical">Critical (Crash/Data Loss)</option>
                            </select>
                        </div>
                        <div>
                             <InputLabel value="Priority (Urgency)" />
                             <select v-model="form.priority" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                <option value="low">Low (When possible)</option>
                                <option value="normal">Normal (Standard)</option>
                                <option value="high">High (ASAP)</option>
                                <option value="urgent">Urgent (Immediate)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <InputLabel value="Description" />
                        <textarea v-model="form.description" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" placeholder="Detailed explanation..."></textarea>
                        <InputError :message="form.errors.description" class="mt-2" />
                    </div>

                    <!-- Steps to Reproduce -->
                    <div>
                        <InputLabel value="Steps to Reproduce" />
                        <textarea v-model="form.steps_to_reproduce" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" placeholder="1. Go to... 2. Click..."></textarea>
                    </div>

                    <!-- Attachments -->
                    <div>
                        <InputLabel value="Attachments" />
                        <input type="file" multiple @change="handleFileUpload" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100" />
                        <p class="mt-1 text-xs text-gray-500" v-if="form.attachments.length">{{ form.attachments.length }} files selected</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="$emit('close')">Cancel</SecondaryButton>
                    <PrimaryButton :disabled="form.processing">Create Ticket</PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useBugTrackerStore } from '@/Stores/bugTrackerStore';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import axios from 'axios';

const props = defineProps({
    show: Boolean,
    projects: Array
});

const emit = defineEmits(['close']);

const bugStore = useBugTrackerStore();

const form = useForm({
    project_id: bugStore.selectedProject || '',
    module_id: bugStore.selectedModule || '',
    subject: '',
    description: '',
    steps_to_reproduce: '',
    severity: 'medium',
    priority: 'normal',
    assignee_id: null,
    attachments: []
});

const modules = ref([]);
const loadingModules = ref(false);

const handleFileUpload = (e) => {
    form.attachments = Array.from(e.target.files);
};

onMounted(() => {
    if (form.project_id) {
        loadModules(form.project_id);
    }
});

watch(() => form.project_id, async (newId) => {
    if (!newId) {
        modules.value = [];
        return;
    }
    loadModules(newId);
});

watch(() => bugStore.selectedProject, (newVal) => {
    if (newVal && props.show) {
        form.project_id = newVal;
    }
});

watch(() => bugStore.selectedModule, (newVal) => {
    if (newVal && props.show) {
        form.module_id = newVal;
    }
});

async function loadModules(newId) {
    loadingModules.value = true;
    try {
        const { data } = await axios.get(route('projects.modules.tree', newId));
        modules.value = Array.isArray(data) ? data : (data.data || []);
    } catch (e) {
        console.error("Failed to load modules", e);
    } finally {
        loadingModules.value = false;
    }
}

const submit = () => {
    form.post(route('bugs.store'), {
        onSuccess: () => {
            form.reset();
            emit('close');
        }
    });
};
</script>
