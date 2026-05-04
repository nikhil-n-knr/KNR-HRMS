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
    timesheets:   Object,
    projects:     Array,
    all_projects: Array,
    filters:      Object
});

const user        = usePage().props.auth.user;
const toast       = useToastStore();

// ── State ──
const timesheets  = ref({ data: [], meta: {} });
const viewMode    = ref('weekly');
const projects    = computed(() => props.projects    || []);
const allProjects = computed(() => props.all_projects || []);
const filters     = ref({});
const loading     = ref(false);
const showModal   = ref(false);
const isEditing   = ref(false);
const editId      = ref(null);
const processing  = ref(false);

const form = ref({
    date:              new Date().toISOString().split('T')[0],
    project_id:        '',
    task_description:  '',
    hours_spent:       '',
    show_all_projects: false,
    show_all_tasks:    false,
    is_other:          false
});

const errors            = ref({});
const modalTasks        = ref([]);
const showConflictModal = ref(false);
const conflictMessage   = ref('');

// ── Task fetch ──
const fetchModalTasks = async () => {
    if (!form.value.project_id) { modalTasks.value = []; return; }
    try {
        const res = await axios.get(
            `/api/employee/attendance/timesheets/project-tasks/${form.value.project_id}?all=${form.value.show_all_tasks ? '1' : '0'}`
        );
        modalTasks.value = form.value.show_all_tasks ? res.data.all_tasks : res.data.assigned_tasks;
    } catch (e) { console.error(e); }
};

watch(() => form.value.project_id,   fetchModalTasks);
watch(() => form.value.show_all_tasks, fetchModalTasks);
watch(() => form.value.is_other, (v) => { if (v) form.value.task_id = null; });

// ── Columns ──
const columns = {
    date:             { label: 'Date',    class: 'text-left' },
    project_name:     { label: 'Project', class: 'text-left' },
    task_description: { label: 'Task',    class: 'text-left w-1/3' },
    hours_spent:      { label: 'Hours',   class: 'text-center' },
    status:           { label: 'Status',  class: 'text-center' },
};

// ── Data fetch ──
const fetchData = async (page = 1) => {
    loading.value = true;
    try {
        const response = await axios.get('/api/employee/attendance/timesheets', {
            params: { page, ...filters.value }
        });
        timesheets.value = response.data.timesheets;
    } catch (e) {
        console.error(e);
        toast.error('Failed to load timesheets');
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    if (props.timesheets) timesheets.value = props.timesheets;
    else fetchData();
});

// ── Modal helpers ──
const openCreateModal = () => {
    isEditing.value = false;
    editId.value    = null;
    form.value      = {
        date: new Date().toISOString().split('T')[0],
        project_id: '', task_description: '', hours_spent: '',
        show_all_projects: false, show_all_tasks: false, is_other: false
    };
    modalTasks.value = [];
    showModal.value  = true;
};

const openEditModal = (item) => {
    isEditing.value = true;
    editId.value    = item.id;
    const pId       = item.project_id || (item.project ? item.project.id : '');
    form.value      = {
        date: item.date, project_id: pId,
        task_description: item.task_description,
        hours_spent: item.hours_spent,
        is_other: !item.task_id
    };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.value      = { date: '', project_id: '', task_description: '', hours_spent: '' };
    errors.value    = {};
};

const closeConflictModal = () => {
    showConflictModal.value = false;
    conflictMessage.value   = '';
};

// ── Submit ──
const submit = async () => {
    processing.value = true;
    try {
        const payload = { ...form.value };
        if (isEditing.value) {
            await axios.put(`/api/employee/attendance/timesheets/${editId.value}`, payload);
            toast.success('Timesheet updated successfully');
        } else {
            await axios.post('/api/employee/attendance/timesheets', payload);
            toast.success('Timesheet entry added');
        }
        closeModal();
        fetchData();
    } catch (e) {
        if (e.response?.status === 409) {
            conflictMessage.value   = e.response.data.conflict;
            showConflictModal.value = true;
        } else if (e.response?.status === 422) {
            errors.value = e.response.data.errors;
            toast.error('Please fix the validation errors.');
        } else {
            toast.error(e.response?.data?.message || 'Operation failed');
        }
    } finally {
        processing.value = false;
    }
};

const forceSubmit = async () => {
    processing.value = true;
    try {
        await axios.post('/api/employee/attendance/timesheets', { ...form.value, force: true });
        toast.success('Timesheet entry forced successfully');
        closeConflictModal();
        closeModal();
        fetchData();
    } catch (e) {
        toast.error('Force submit failed');
    } finally {
        processing.value = false;
    }
};

const deleteEntry = async (item) => {
    if (!confirm('Are you sure you want to delete this entry?')) return;
    try {
        await axios.delete(`/api/employee/attendance/timesheets/${item.id}`);
        toast.success('Entry deleted');
        fetchData();
    } catch (e) {
        toast.error('Failed to delete entry');
    }
};

const submitForApproval = async (item) => {
    if (!confirm('Submit this timesheet for approval? You cannot edit it afterwards.')) return;
    try {
        await axios.post(`/api/employee/attendance/timesheets/${item.id}/submit`);
        toast.success('Timesheet submitted for approval');
        fetchData();
    } catch (e) {
        toast.error('Failed to submit timesheet');
    }
};

const totalHours = computed(() => {
    if (!timesheets.value.data) return '0.00';
    return timesheets.value.data
        .reduce((sum, item) => sum + parseFloat(item.hours_spent || 0), 0)
        .toFixed(2);
});
</script>

<template>
    <!-- ░░ Outer Page Shell ░░ -->
    <div class="min-h-screen bg-[#f4f5fa]">

        <!-- ▓▓ GRADIENT HERO HEADER ▓▓ -->
        <div class="relative overflow-hidden sm:rounded-2xl bg-gradient-to-br from-[#3d27b4] via-[#6b3fd4] to-[#a855f7]">
            <div class="absolute -top-20 -right-20 w-80 h-80 bg-white/5 rounded-full pointer-events-none"></div>
            <div class="absolute bottom-0 left-1/3 w-56 h-56 bg-white/5 rounded-full pointer-events-none"></div>

            <div class="relative z-10 px-6 sm:px-10 py-8
                        flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <!-- Left -->
                <div>
                    <p class="text-xs font-bold text-white/50 uppercase tracking-widest mb-2">Attendance</p>
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight">
                        My Timesheets
                    </h1>
                    <p class="mt-2 text-sm text-white/60 max-w-md leading-relaxed">
                        Track your daily work hours by project and task.
                    </p>
                </div>

                <!-- Right: stat pills + controls -->
                <div class="flex flex-wrap items-center gap-3 shrink-0">

                    <!-- Total hours pill -->
                    <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[140px]">
                        <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Hours (This Page)</p>
                        <p class="text-3xl font-extrabold text-white leading-none">{{ totalHours }}<span class="text-base font-bold text-white/60 ml-1">hrs</span></p>
                    </div>

                    <!-- View switcher -->
                    <div class="flex items-center gap-0.5 bg-white/10 border border-white/20 rounded-2xl p-1">
                        <button
                            @click="viewMode = 'list'"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-extrabold uppercase tracking-widest transition-all"
                            :class="viewMode === 'list' ? 'bg-white text-indigo-700 shadow-sm' : 'text-white/60 hover:text-white hover:bg-white/10'"
                        >
                            <ListBulletIcon class="w-4 h-4" /> List
                        </button>
                        <button
                            @click="viewMode = 'weekly'"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-extrabold uppercase tracking-widest transition-all"
                            :class="viewMode === 'weekly' ? 'bg-white text-indigo-700 shadow-sm' : 'text-white/60 hover:text-white hover:bg-white/10'"
                        >
                            <TableCellsIcon class="w-4 h-4" /> Weekly
                        </button>
                    </div>

                    <!-- Log Time (list mode only) -->
                    <button
                        v-if="viewMode === 'list'"
                        @click="openCreateModal"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl
                               bg-white text-indigo-700 text-sm font-extrabold
                               hover:bg-indigo-50 active:scale-[0.97] transition-all shadow-lg shadow-black/10"
                    >
                        <PlusIcon class="w-4 h-4" />
                        Log Time
                    </button>
                </div>
            </div>
        </div>

        <!-- ▓▓ INNER CONTENT BODY ▓▓ -->
        <div class="mx-0 sm:mx-6 mt-5 pb-12 space-y-5">

            <!-- List View -->
            <div v-if="viewMode === 'list'" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <BaseDataTable
                    :columns="columns"
                    :data="timesheets.data || []"
                    :pagination="timesheets"
                    :loading="loading"
                    @page-change="fetchData"
                >
                    <!-- Date -->
                    <template #cell-date="{ item }">
                        <span class="text-sm font-medium text-slate-700">
                            {{ new Date(item.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}
                        </span>
                    </template>

                    <!-- Status -->
                    <template #cell-status="{ item }">
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border"
                            :class="{
                                'bg-slate-50   text-slate-600  border-slate-200':  item.status === 'Draft',
                                'bg-emerald-50 text-emerald-700 border-emerald-100': item.status === 'Approved',
                                'bg-rose-50    text-rose-700   border-rose-100':   item.status === 'Rejected',
                                'bg-amber-50   text-amber-700  border-amber-100':  item.status === 'Submitted',
                            }"
                        >
                            <span
                                class="w-1.5 h-1.5 rounded-full"
                                :class="{
                                    'bg-slate-400':   item.status === 'Draft',
                                    'bg-emerald-500': item.status === 'Approved',
                                    'bg-rose-500':    item.status === 'Rejected',
                                    'bg-amber-500':   item.status === 'Submitted',
                                }"
                            ></span>
                            {{ item.status }}
                        </span>
                    </template>

                    <!-- Row Actions -->
                    <template #rowActions="{ item }">
                        <div v-if="item.status !== 'Approved'" class="flex items-center gap-2">
                            <button
                                v-if="['Draft', 'Rejected'].includes(item.status)"
                                @click="submitForApproval(item)"
                                class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold
                                       bg-emerald-50 text-emerald-700 border border-emerald-100
                                       hover:bg-emerald-600 hover:text-white hover:border-emerald-600 transition-all"
                            >
                                Submit
                            </button>
                            <button
                                v-if="['Draft', 'Rejected', 'Submitted'].includes(item.status)"
                                @click="openEditModal(item)"
                                class="w-8 h-8 rounded-xl border border-slate-200 flex items-center justify-center
                                       text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 hover:border-indigo-200 transition-all"
                                title="Edit"
                            >
                                <PencilIcon class="w-4 h-4" />
                            </button>
                            <button
                                v-if="item.status === 'Draft'"
                                @click="deleteEntry(item)"
                                class="w-8 h-8 rounded-xl border border-slate-200 flex items-center justify-center
                                       text-slate-400 hover:text-rose-600 hover:bg-rose-50 hover:border-rose-200 transition-all"
                                title="Delete"
                            >
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

        </div><!-- /inner body -->
    </div><!-- /outer shell -->

    <!-- ── Log Time Modal ── -->
    <Modal :show="showModal" @close="closeModal">
        <div class="p-6 sm:p-8">

            <!-- Modal Header -->
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
                    <ClockIcon class="w-5 h-5 text-indigo-600" />
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">{{ isEditing ? 'Edit Entry' : 'Log Time' }}</h3>
                    <p class="text-xs text-slate-400 font-medium mt-0.5">Record your work hours for a project task.</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-4">

                <!-- Date -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-600">Date <span class="text-rose-500">*</span></label>
                    <input
                        type="date"
                        v-model="form.date"
                        required
                        class="w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-medium text-slate-800
                               bg-slate-50 outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/10 transition-all"
                    />
                </div>

                <!-- Project -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-600">Project</label>
                        <button
                            type="button"
                            @click="form.show_all_projects = !form.show_all_projects"
                            class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors"
                        >
                            {{ form.show_all_projects ? 'Show Assigned' : 'Show All Projects' }}
                        </button>
                    </div>
                    <Combobox
                        v-model="form.project_id"
                        :items="form.show_all_projects ? allProjects : projects"
                        labelKey="name"
                        valueKey="id"
                        placeholder="Select Project…"
                    />
                    <InputError :message="errors.project_id" />
                </div>

                <!-- Activity Type -->
                <div class="border border-slate-100 rounded-xl p-4 space-y-3 bg-slate-50/50">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-600 uppercase tracking-wide">Activity Type</label>
                        <div class="flex items-center gap-4 text-sm">
                            <label class="flex items-center gap-1.5 cursor-pointer font-semibold text-slate-600">
                                <input type="radio" :value="false" v-model="form.is_other"
                                    class="text-indigo-600 focus:ring-indigo-500" />
                                Project Task
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer font-semibold text-slate-600">
                                <input type="radio" :value="true" v-model="form.is_other"
                                    class="text-indigo-600 focus:ring-indigo-500" />
                                Other / Ad-hoc
                            </label>
                        </div>
                    </div>

                    <!-- Task Select -->
                    <div v-if="!form.is_other" class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-600">Select Task</label>
                            <button
                                type="button"
                                @click="form.show_all_tasks = !form.show_all_tasks"
                                class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors"
                            >
                                {{ form.show_all_tasks ? 'Assigned Only' : 'Show All Tasks' }}
                            </button>
                        </div>
                        <Combobox
                            v-model="form.task_id"
                            :items="modalTasks"
                            :displayFormat="(t) => t.code ? `${t.code} - ${t.title}` : t.title"
                            valueKey="id"
                            placeholder="Search & Select Task…"
                            :disabled="!form.project_id"
                        />
                        <InputError :message="errors.task_id" />
                    </div>

                    <!-- Other notice -->
                    <div v-else class="flex items-start gap-2.5 bg-amber-50 border border-amber-200 rounded-xl p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-xs text-amber-700 font-medium">Logging time for work not defined in the project plan.</p>
                    </div>
                </div>

                <!-- Description -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-600">
                        {{ form.is_other ? 'Description (Required)' : 'Log Remarks (Optional)' }}
                    </label>
                    <BaseTextarea
                        v-model="form.task_description"
                        :required="form.is_other"
                        placeholder="Details of work done…"
                        class="w-full rounded-xl border border-slate-200 text-sm bg-slate-50 focus:bg-white focus:border-indigo-400 transition-all"
                    />
                    <InputError :message="errors.task_description" />
                </div>

                <!-- Hours -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-600">Hours Spent <span class="text-rose-500">*</span></label>
                    <input
                        type="number"
                        step="0.1"
                        min="0.1"
                        max="24"
                        v-model="form.hours_spent"
                        required
                        placeholder="e.g. 2.5"
                        class="w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-medium text-slate-800
                               bg-slate-50 outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/10 transition-all"
                    />
                    <InputError :message="errors.hours_spent" />
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-2 border-t border-slate-100">
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-all"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="processing"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl
                               bg-indigo-600 text-white text-sm font-bold
                               hover:bg-indigo-700 active:scale-[0.98] transition-all
                               shadow-sm shadow-indigo-200 disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                        <div v-if="processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ isEditing ? 'Update Entry' : 'Save Entry' }}
                    </button>
                </div>

            </form>
        </div>
    </Modal>

    <!-- ── Conflict Modal ── -->
    <Modal :show="showConflictModal" @close="closeConflictModal">
        <div class="p-6 sm:p-8">
            <div class="flex flex-col items-center text-center mb-6">
                <div class="w-14 h-14 rounded-2xl bg-rose-50 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-base font-extrabold text-slate-900 mb-2">Attendance Conflict Detected</h3>
                <p class="text-sm text-slate-500 leading-relaxed max-w-sm">
                    {{ conflictMessage }}
                </p>
                <p class="text-sm font-bold text-slate-800 mt-2">Do you want to force submit this entry?</p>
            </div>
            <div class="flex justify-center gap-3">
                <button
                    @click="closeConflictModal"
                    class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-all"
                >
                    Cancel
                </button>
                <button
                    @click="forceSubmit"
                    :disabled="processing"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl
                           bg-rose-600 text-white text-sm font-bold
                           hover:bg-rose-700 active:scale-[0.98] transition-all
                           shadow-sm shadow-rose-200 disabled:opacity-60"
                >
                    <div v-if="processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                    Yes, Force Submit
                </button>
            </div>
        </div>
    </Modal>
</template>