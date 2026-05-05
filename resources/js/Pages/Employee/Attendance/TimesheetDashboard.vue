<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Combobox from '@/Components/Combobox.vue';
import Modal from '@/Components/Modal.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import BaseInput from '@/Components/BaseInput.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import BaseTextarea from '@/Components/BaseTextarea.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { PlusIcon, PencilIcon, TrashIcon, ClockIcon, ListBulletIcon, TableCellsIcon } from '@heroicons/vue/24/outline';
import { useToastStore } from '@/stores/toast';
import WeeklyTimesheet from './WeeklyTimesheet.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    timesheets: Object,
    projects: Array,
    all_projects: Array, // Added all_projects prop
    filters: Object
});

const user = usePage().props.auth.user; // Added user variable

const toast = useToastStore();

// State
const timesheets = ref({ data: [], meta: {} });
const viewMode = ref('weekly'); // Default to Weekly as requested "Faster Easy"
const projects = computed(() => props.projects || []);
const allProjects = computed(() => props.all_projects || []);
const filters = ref({});
const loading = ref(false);
const showModal = ref(false);
const isEditing = ref(false);
const editId = ref(null);
const processing = ref(false);

const form = ref({
    date: new Date().toISOString().split('T')[0],
    project_id: '',
    task_description: '',
    hours_spent: '',
    show_all_projects: false, // UI State
    show_all_tasks: false,    // UI State
    is_other: false           // UI State: "Other" activity not linked to a specific task ID
});

const errors = ref({}); // Validation Errors

// Modal Logic
const modalTasks = ref([]);

const fetchModalTasks = async () => {
    if (!form.value.project_id) {
        modalTasks.value = [];
        return;
    }
    try {
        const res = await axios.get(`/api/employee/attendance/timesheets/project-tasks/${form.value.project_id}?all=${form.value.show_all_tasks ? '1' : '0'}`);
        // API returns { assigned_tasks: [], all_tasks: [] }
         if (form.value.show_all_tasks) {
            modalTasks.value = res.data.all_tasks;
        } else {
            modalTasks.value = res.data.assigned_tasks;
        }
    } catch (e) {
        console.error(e);
    }
};

watch(() => form.value.project_id, fetchModalTasks);
watch(() => form.value.show_all_tasks, fetchModalTasks);

const columns = {
    date: { label: 'Date', class: 'text-left' },
    project_name: { label: 'Project', class: 'text-left' },
    task_description: { label: 'Task', class: 'text-left w-1/3' },
    hours_spent: { label: 'Hours', class: 'text-center' },
    status: { label: 'Status', class: 'text-center' },
};

// Fetch Data
const fetchData = async (page = 1) => {
    loading.value = true;
    try {
        const response = await axios.get('/api/employee/attendance/timesheets', {
            params: { page, ...filters.value }
        });
        timesheets.value = response.data.timesheets;
    } catch (e) {
        console.error(e);
        toast.error("Failed to load timesheets");
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    // Initial load from Props
    if (props.timesheets) {
        timesheets.value = props.timesheets;
    } else {
        fetchData();
    }
});

const openCreateModal = () => {
    isEditing.value = false;
    editId.value = null;
    form.value = {
        date: new Date().toISOString().split('T')[0],
        project_id: '',
        task_description: '',
        hours_spent: '',
        show_all_projects: false,
        show_all_tasks: false,
        is_other: false
    };
    modalTasks.value = [];
    showModal.value = true;
};

const openEditModal = (item) => {
    isEditing.value = true;
    editId.value = item.id;
    // Handle case where project_id might be missing if loaded from old API, try to find by name or default
    const pId = item.project_id || (item.project ? item.project.id : '');
    form.value = {
        date: item.date,
        project_id: pId, 
        task_description: item.task_description,
        hours_spent: item.hours_spent,
        is_other: !item.task_id // If no task_id, it's 'Other'
    };
    showModal.value = true;
};

const showConflictModal = ref(false);
const conflictMessage = ref('');

const closeModal = () => {
    showModal.value = false;
    form.value = { date: '', project_id: '', task_description: '', hours_spent: '' };
    errors.value = {};
};

const closeConflictModal = () => {
    showConflictModal.value = false;
    conflictMessage.value = '';
};

// Clear task_id if is_other is selected
watch(() => form.value.is_other, (newVal) => {
    if (newVal) {
        form.value.task_id = null;
    }
});

const submit = async () => {
    if (new Date(form.value.date) > new Date()) {
        toast.error("Cannot log time for future dates.");
        return;
    }
    processing.value = true;
    try {
        const payload = { ...form.value }; 
        if (isEditing.value) {
            await axios.put(`/api/employee/attendance/timesheets/${editId.value}`, payload);
            toast.success("Timesheet updated successfully");
        } else {
            await axios.post('/api/employee/attendance/timesheets', payload);
            toast.success("Timesheet entry added");
        }
        closeModal();
        fetchData();
    } catch (e) {
        if (e.response && e.response.status === 409) {
            // Conflict detected
            conflictMessage.value = e.response.data.conflict;
            showConflictModal.value = true;
            // Keep main modal open or close? Usually keep open so they see context, but we show overlay on overlay?
            // Let's close main modal for clarity, or just stack. Stacking is complex with current Modal.
            // Let's hide main modal temporarily? No, user needs to know what they submitted.
            // We'll stack it (z-index should handle it).
        } else if (e.response && e.response.status === 422) {
             errors.value = e.response.data.errors;
             toast.error("Please fix the validation errors.");
        } else {
            toast.error(e.response?.data?.message || "Operation failed");
        }
    } finally {
        processing.value = false;
    }
};

const forceSubmit = async () => {
    processing.value = true;
    try {
         const payload = { ...form.value, force: true };
         await axios.post('/api/employee/attendance/timesheets', payload);
         toast.success("Timesheet entry forced successfully");
         closeConflictModal();
         closeModal();
         fetchData();
    } catch (e) {
        toast.error("Force submit failed");
    } finally {
        processing.value = false;
    }
};

const deleteEntry = async (item) => {
    if (!confirm('Are you sure you want to delete this entry?')) return;
    try {
        await axios.delete(`/api/employee/attendance/timesheets/${item.id}`);
        toast.success("Entry deleted");
        fetchData();
    } catch (e) {
        toast.error("Failed to delete entry");
    }
};

const submitForApproval = async (item) => {
    if (!confirm('Submit this timesheet for approval? You cannot edit it afterwards.')) return;
    try {
        await axios.post(`/api/employee/attendance/timesheets/${item.id}/submit`);
        toast.success("Timesheet submitted for approval");
        fetchData();
    } catch (e) {
        toast.error("Failed to submit timesheet");
    }
};

const totalHours = computed(() => {
    if (!timesheets.value.data) return "0.00";
    return timesheets.value.data.reduce((sum, item) => sum + parseFloat(item.hours_spent || 0), 0).toFixed(2);
});
</script>

<template>
    <!-- <Head title="My Timesheets" /> -->

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">My Timesheets</h2>
                <p class="text-sm text-gray-500">Track your daily work hours by project.</p>
            </div>
            <div class="flex items-center gap-3">
                 <!-- View Switcher -->
                 <div class="bg-gray-100 p-1 rounded-lg flex items-center">
                     <button @click="viewMode = 'list'" class="p-2 rounded-md transition-all text-sm font-medium flex items-center gap-2" :class="viewMode === 'list' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'">
                         <ListBulletIcon class="w-4 h-4" /> List
                     </button>
                     <button @click="viewMode = 'weekly'" class="p-2 rounded-md transition-all text-sm font-medium flex items-center gap-2" :class="viewMode === 'weekly' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'">
                         <TableCellsIcon class="w-4 h-4" /> Weekly Grid
                     </button>
                 </div>
                 
                <PrimaryButton v-if="viewMode === 'list'" @click="openCreateModal" class="flex items-center">
                    <PlusIcon class="w-4 h-4 mr-2" />
                    Log Time
                </PrimaryButton>
            </div>
        </div>

        <!-- Stats/Summary -->
            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 p-4 rounded-lg border border-emerald-100 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-white rounded-full text-emerald-600 shadow-sm">
                    <ClockIcon class="w-5 h-5" />
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Total Hours (This Page)</p>
                    <p class="text-lg font-bold text-gray-800">{{ totalHours }} Hours</p>
                </div>
            </div>
        </div>


        <!-- Data Table (List View) -->
        <div v-if="viewMode === 'list'">
        <BaseDataTable 
            :columns="columns"
            :data="timesheets.data || []"
            :pagination="timesheets"
            :loading="loading"
            @page-change="fetchData"
        >
            <template #cell-date="{ item }">
                {{ new Date(item.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}
            </template>
            <template #cell-status="{ item }">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                    :class="{
                        'bg-gray-100 text-gray-800': item.status === 'Draft',
                        'bg-green-100 text-green-800': item.status === 'Approved',
                        'bg-red-100 text-red-800': item.status === 'Rejected',
                        'bg-yellow-100 text-yellow-800': item.status === 'Submitted'
                    }">
                    {{ item.status }}
                </span>
            </template>

                <template #rowActions="{ item }">
                <div class="flex space-x-2" v-if="item.status !== 'Approved'">
                    <button v-if="['Draft', 'Rejected'].includes(item.status)" @click="submitForApproval(item)" class="text-emerald-600 hover:text-emerald-900 border border-emerald-200 bg-emerald-50 px-2 py-0.5 rounded text-xs flex items-center" title="Submit for Approval">
                        Submit
                    </button>
                        <button v-if="['Draft', 'Rejected', 'Submitted'].includes(item.status)" @click="openEditModal(item)" class="text-indigo-600 hover:text-indigo-900" title="Edit">
                        <PencilIcon class="w-4 h-4" />
                    </button>
                    <button v-if="item.status === 'Draft'" @click="deleteEntry(item)" class="text-red-600 hover:text-red-900" title="Delete">
                        <TrashIcon class="w-4 h-4" />
                    </button>
                </div>
            </template>
        </BaseDataTable>
        </div>
        
        <!-- Weekly Grid View -->
        <div v-if="viewMode === 'weekly'">
            <WeeklyTimesheet :projects="projects" :all_projects="allProjects" />
        </div>

            <!-- Modal -->
        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ isEditing ? 'Edit Entry' : 'Log Time' }}</h3>
                
                <form @submit.prevent="submit" class="space-y-4">
                    <BaseInput
                        type="date"
                        v-model="form.date"
                        label="Date"
                        required
                        :max="new Date().toISOString().split('T')[0]"
                    />
                <!-- Project -->
                <div>
                   <div class="flex justify-between mb-1">
                    <InputLabel for="project_id" value="Project" />
                    <button type="button" @click="form.show_all_projects = !form.show_all_projects" class="text-xs text-indigo-600 hover:underline">
                        {{ form.show_all_projects ? 'Assigned' : 'Show All' }}
                    </button>
                   </div>
                    <Combobox
                        v-model="form.project_id"
                        :items="form.show_all_projects ? allProjects : projects"
                        labelKey="name"
                        valueKey="id"
                        placeholder="Select Project..."
                    />
                    <InputError class="mt-2" :message="errors.project_id" />
                </div>
                
                <!-- Task Selection Section -->
                <div class="border-t border-b border-gray-100 py-3 my-3">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-gray-700">Activity Type</label>
                        <div class="flex items-center gap-3 text-sm">
                            <label class="flex items-center gap-1 cursor-pointer">
                                <input type="radio" :value="false" v-model="form.is_other" class="text-indigo-600 focus:ring-indigo-500">
                                Project Task
                            </label>
                            <label class="flex items-center gap-1 cursor-pointer">
                                <input type="radio" :value="true" v-model="form.is_other" class="text-indigo-600 focus:ring-indigo-500">
                                Other / Ad-hoc
                            </label>
                        </div>
                    </div>

                    <!-- Task Select (If Project Task) -->
                    <div v-if="!form.is_other">
                        <div class="flex justify-between mb-1">
                            <InputLabel for="task_id" value="Select Task" />
                            <button type="button" @click="form.show_all_tasks = !form.show_all_tasks" class="text-xs text-indigo-600 hover:underline">
                                {{ form.show_all_tasks ? 'Assigned Only' : 'Show All Tasks' }}
                            </button>
                        </div>
                        <Combobox
                            v-model="form.task_id"
                            :items="modalTasks"
                            :displayFormat="(t) => t.code ? `${t.code} - ${t.title}` : t.title"
                            valueKey="id"
                            placeholder="Search & Select Task..."
                            :disabled="!form.project_id"
                        />
                        <InputError class="mt-2" :message="errors.task_id" />
                    </div>

                    <!-- Other Title (If Other) -->
                    <div v-else>
                         <div class="bg-yellow-50 border border-yellow-200 rounded p-2 mb-2 text-xs text-yellow-700">
                            Logging time for work not defined in the project plan.
                         </div>
                    </div>
                </div>

                <!-- Description / Log Details -->
                <div>
                    <InputLabel for="task_description" :value="form.is_other ? 'Description (Required)' : 'Log Remarks (Optional)'" />
                    <BaseTextarea
                        id="task_description"
                        v-model="form.task_description"
                        class="mt-1 block w-full"
                        :required="form.is_other"
                        placeholder="Details of work done..."
                    />
                    <InputError class="mt-2" :message="errors.task_description" />
                </div>
                    <BaseInput
                        type="number"
                        step="0.1"
                        v-model="form.hours_spent"
                        label="Hours Spent"
                        required
                        min="0.1"
                        max="24"
                    />
                    <InputError class="mt-2" :message="errors.hours_spent" />
                    <div class="flex justify-end gap-2 mt-4">
                        <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                        <PrimaryButton :disabled="processing">
                            {{ isEditing ? 'Update' : 'Save Entry' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Conflict Confirmation Modal -->
        <Modal :show="showConflictModal" @close="closeConflictModal">
            <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-center text-gray-900 mb-2">Attendance Conflict Detected</h3>
                <p class="text-sm text-center text-gray-600 mb-6">
                    {{ conflictMessage }}
                    <br><span class="font-bold text-gray-800">Do you want to force submit this entry?</span>
                </p>
                <div class="flex justify-center gap-3">
                    <SecondaryButton @click="closeConflictModal">Cancel</SecondaryButton>
                    <button @click="forceSubmit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 shadow-md">
                        Yes, Force Submit
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>
