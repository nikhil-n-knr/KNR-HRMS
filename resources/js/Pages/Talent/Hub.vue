<template>
    <TalentLayout>
        <!-- Stats Overview -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-6 mb-8">
            <div class="bg-white/60 backdrop-blur-xl rounded-2xl p-5 md:p-6 border border-gray-200 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
                 <div class="absolute right-0 top-0 h-full w-1.5 bg-gradient-to-b from-indigo-400 to-indigo-600"></div>
                 <div class="text-gray-400 text-sm font-black uppercase tracking-[0.2em]">Active Roles</div>
                 <div class="text-2xl md:text-3xl font-black text-slate-900 mt-2">{{ stats.active_jobs }}</div>
                 <div class="mt-4 flex items-center text-sm font-bold text-indigo-600 bg-indigo-50 w-fit px-2 py-0.5 rounded-full uppercase tracking-widest">Hiring Now</div>
            </div>
             <div class="bg-white/60 backdrop-blur-xl rounded-2xl p-5 md:p-6 border border-gray-200 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
                  <div class="absolute right-0 top-0 h-full w-1.5 bg-gradient-to-b from-emerald-400 to-emerald-600"></div>
                  <div class="text-gray-400 text-sm font-black uppercase tracking-[0.2em]">Candidates</div>
                  <div class="text-2xl md:text-3xl font-black text-slate-900 mt-2">{{ stats.total_candidates }}</div>
                  <div class="mt-4 flex items-center text-sm font-bold text-emerald-600 bg-emerald-50 w-fit px-2 py-0.5 rounded-full uppercase tracking-widest">+ 24 New</div>
            </div>
             <div class="bg-white/60 backdrop-blur-xl rounded-2xl p-5 md:p-6 border border-gray-200 shadow-sm relative overflow-hidden group hover:shadow-md transition-all col-span-2 md:col-span-1">
                  <div class="absolute right-0 top-0 h-full w-1.5 bg-gradient-to-b from-amber-400 to-amber-600"></div>
                  <div class="text-gray-400 text-sm font-black uppercase tracking-[0.2em]">Interviews</div>
                  <div class="text-2xl md:text-3xl font-black text-slate-900 mt-2">{{ stats.interviews_today }}</div>
                  <div class="mt-4 flex items-center text-sm font-bold text-amber-600 bg-amber-50 w-fit px-2 py-0.5 rounded-full uppercase tracking-widest">Today's Schedule</div>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-xl rounded-3xl border border-gray-100 shadow-2xl p-6 md:p-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-6">
                 <div class="flex items-center gap-4">
                    <div class="p-4 bg-indigo-50 border border-indigo-100 rounded-2xl text-indigo-600 shadow-inner">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                            Recent Job Postings
                            <button @click="openInfoModal" class="text-slate-300 hover:text-indigo-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </button>
                        </h3>
                        <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] mt-1">Manage your active recruitment drives</p>
                    </div>
                 </div>
                 <div class="flex items-center gap-3 w-full md:w-auto">
                    <a :href="route('careers.index')" target="_blank" class="flex-1 md:flex-none inline-flex items-center justify-center px-5 py-3 border border-gray-200 shadow-sm text-sm font-black uppercase tracking-widest rounded-xl text-slate-600 bg-white hover:bg-indigo-50 hover:border-indigo-100 hover:text-indigo-600 transition-all">
                        Careers Page <span class="ml-2">&rarr;</span>
                    </a>
                    <Link :href="route('talent.jobs.create')" class="flex-1 md:flex-none inline-flex items-center justify-center px-6 py-3 bg-indigo-600 text-white rounded-xl text-sm font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100 hover:-translate-y-0.5 active:translate-y-0">
                        + New Job
                    </Link>
                 </div>
            </div>

            <BaseDataTable
                :columns="columns"
                :data="jobs.data"
                :meta="jobs"
                :loading="false"
                selectable
                v-model="selectedIds"
                @page-change="onPageChange"
            >
                <!-- Bulk Actions -->
                <template #actions>
                    <div v-if="selectedIds.length > 0" class="flex items-center gap-2 animate-in fade-in slide-in-from-right-4">
                        <span class="text-sm font-black text-slate-400 uppercase mr-2">{{ selectedIds.length }} Selected</span>
                        <div class="flex bg-gray-50 p-1 rounded-xl border border-gray-200">
                            <button @click="bulkAction('activate')" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Activate">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </button>
                            <button @click="bulkAction('deactivate')" class="p-2 text-slate-500 hover:bg-gray-200 rounded-lg transition-colors" title="Deactivate">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </button>
                            <button @click="openExtendModal" class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Extend Deadline">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </button>
                            <button @click="openAssignModal" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Assign Template">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </button>
                             <button @click="bulkAction('delete')" class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Columns Slots -->
                <template #cell-job_code="{ item }">
                    <div class="flex items-center gap-3">
                         <div class="h-10 w-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-sm font-black text-slate-500 group-hover:bg-indigo-50 group-hover:border-indigo-100 group-hover:text-indigo-600 transition-all">
                             {{ item.job_code.split('-').pop() }}
                         </div>
                        <div>
                            <div class="text-base font-black text-slate-900 group-hover:text-indigo-600 transition-colors uppercase tracking-tight">{{ item.job_code }}</div>
                            <div class="mt-0.5">
                                 <span v-if="item.screening_template" class="inline-flex items-center px-2 py-0.5 rounded-lg text-sm font-black uppercase tracking-widest bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ item.screening_template.name }}
                                </span>
                                <span v-else class="text-sm font-black uppercase tracking-widest text-rose-500/60 italic">
                                    No Screening
                                </span>
                            </div>
                        </div>
                    </div>
                </template>
                
                <template #cell-title="{ value }">
                    <div class="text-lg font-black text-slate-700 group-hover:text-slate-900 transition-colors">{{ value }}</div>
                </template>

                <template #cell-dept_loc="{ item }">
                    <div>
                        <div class="text-base font-bold text-slate-700 uppercase tracking-tight">{{ item.department?.name }}</div>
                        <div class="text-sm text-slate-400 font-medium">{{ item.location?.name }}</div>
                    </div>
                </template>

                 <template #cell-category="{ item }">
                    <span class="text-base font-bold text-slate-600">{{ item.category?.name || '--' }}</span>
                </template>

                <template #cell-valid_through="{ value }">
                    <span class="text-base font-bold text-slate-500">{{ value ? new Date(value).toLocaleDateString() : '--' }}</span>
                </template>

                <template #cell-experience="{ item }">
                    <div v-if="item.min_experience || item.max_experience" class="text-base font-bold text-slate-600">
                        {{ item.min_experience ?? 0 }} - {{ item.max_experience ?? 'Any' }} <span class="text-sm text-slate-400 uppercase tracking-widest ml-1">Years</span>
                    </div>
                    <span v-else class="text-gray-400">--</span>
                </template>

                <template #cell-status="{ item }">
                     <span v-if="item.status === 'Published'" class="px-3 py-1 text-sm font-black uppercase tracking-widest rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 shadow-sm">
                        Live
                    </span>
                        <span v-else class="px-3 py-1 text-sm font-black uppercase tracking-widest rounded-full bg-slate-50 text-slate-500 border border-slate-100">
                        {{ item.status }}
                    </span>
                </template>

                <template #rowActions="{ item }">
                    <div class="flex items-center justify-end">
                        <Link :href="route('talent.jobs.edit', item.id)" class="p-2 text-slate-400 hover:text-indigo-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </Link>
                    </div>
                </template>
            </BaseDataTable>
        </div>
        
        <!-- Extend Date Modal -->
        <Modal :show="showExtendModal" @close="showExtendModal = false">
            <div class="p-8">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Shift Deadline</h2>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-1">Extending validity for {{ selectedIds.length }} postings</p>
                
                <div class="mt-8 space-y-6">
                    <div>
                        <InputLabel value="New Expiry Date" class="text-sm uppercase font-black tracking-widest text-slate-500 mb-2" />
                        <TextInput type="date" v-model="extendDate" class="w-full !rounded-2xl !border-gray-200 focus:!ring-indigo-500/20" :min="new Date().toISOString().split('T')[0]" />
                    </div>
                </div>
                
                <div class="mt-10 flex items-center justify-end gap-3">
                    <button @click="showExtendModal = false" class="px-6 py-3 text-sm font-black uppercase tracking-widest text-slate-500 hover:text-slate-700 transition-colors">Cancel</button>
                    <button @click="confirmExtend" class="px-8 py-3 bg-indigo-600 text-white rounded-2xl text-sm font-black uppercase tracking-widest hover:bg-indigo-700 shadow-xl shadow-indigo-100 transition-all">Update Timeline</button>
                </div>
            </div>
        </Modal>

        <!-- Assign Template Modal -->
        <Modal :show="showAssignModal" @close="showAssignModal = false">
            <div class="p-8">
                 <h2 class="text-2xl font-black text-slate-900 tracking-tight text-indigo-600">Protocol Assignment</h2>
                 <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-1">Inject screening logic into {{ selectedIds.length }} units</p>
                 
                 <div class="mt-8 space-y-6">
                    <div>
                        <InputLabel value="Select Intelligence Template" class="text-sm uppercase font-black tracking-widest text-slate-500 mb-2" />
                        <select v-model="assignTemplateId" class="w-full h-14 pl-4 pr-10 text-sm border-gray-200 focus:ring-indigo-500/20 rounded-2xl appearance-none bg-no-repeat bg-[right_1rem_center] bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236b7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] font-bold text-slate-700 shadow-sm">
                            <option value="">None (Base Form Only)</option>
                            <option v-for="t in screening_templates" :key="t.id" :value="t.id">{{ t.name }}</option>
                        </select>
                    </div>
                </div>

                 <div class="mt-10 flex items-center justify-end gap-3">
                    <button @click="showAssignModal = false" class="px-6 py-3 text-sm font-black uppercase tracking-widest text-slate-500 hover:text-slate-700 transition-colors">Cancel</button>
                    <button @click="confirmAssign" class="px-8 py-3 bg-indigo-600 text-white rounded-2xl text-sm font-black uppercase tracking-widest hover:bg-indigo-700 shadow-xl shadow-indigo-100 transition-all">Synchronize Template</button>
                </div>
            </div>
        </Modal>

        <!-- Info Modal -->
        <Modal :show="showInfoModal" @close="showInfoModal = false">
             <div class="p-8">
                 <div class="flex items-center gap-4 mb-8">
                    <div class="bg-indigo-50 p-4 rounded-2xl text-indigo-600 border border-indigo-100 shadow-inner">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Hiring Blueprint</h2>
                        <p class="text-sm font-black text-gray-400 uppercase tracking-widest mt-1">Understanding the automated pipeline</p>
                    </div>
                 </div>
                 
                 <div class="space-y-4">
                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 group transition-all">
                        <div class="flex items-start gap-4">
                            <div class="text-2xl font-black text-indigo-200 group-hover:text-indigo-400 transition-colors">01</div>
                            <div>
                                <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider mb-2">Architecting the Role</h4>
                                <p class="text-xs text-slate-500 italic leading-relaxed">Map roles to "Intelligence Templates" to trigger adaptive questionnaires (video, technical, psycho-metric) on application.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 group transition-all">
                         <div class="flex items-start gap-4">
                            <div class="text-2xl font-black text-emerald-200 group-hover:text-emerald-400 transition-colors">02</div>
                            <div>
                                <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider mb-2">Pulse Check Capture</h4>
                                <p class="text-xs text-slate-500 italic leading-relaxed">Candidate data streams into the <a href="/careers" target="_blank" class="text-indigo-600 font-bold hover:underline">Career Hub</a>. The engine validates IDs, skills, and screening depth.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 group transition-all">
                         <div class="flex items-start gap-4">
                            <div class="text-2xl font-black text-amber-200 group-hover:text-amber-400 transition-colors">03</div>
                            <div>
                                <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider mb-2">Stage Orchestration</h4>
                                <p class="text-xs text-slate-500 italic leading-relaxed">Finalize review in the "Candidates" portal. Execute drag-and-drop movement to trigger automated interview sessions.</p>
                            </div>
                        </div>
                    </div>
                 </div>

                 <div class="mt-10 flex justify-end">
                    <button @click="showInfoModal = false" class="px-10 py-4 bg-indigo-600 text-white rounded-2xl text-sm font-black uppercase tracking-widest hover:bg-slate-900 transition-all font-outfit shadow-xl shadow-indigo-100">Proceed to Command</button>
                </div>
            </div>
        </Modal>

    </TalentLayout>
</template>

<script setup>
import TalentLayout from '@/Layouts/TalentLayout.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    jobs: Object,
    stats: Object,
    filters: Object,
    screening_templates: Array // NEW
});

const selectedIds = ref([]);
const showExtendModal = ref(false);
const showAssignModal = ref(false); 
const showInfoModal = ref(false); // NEW
const extendDate = ref('');
const assignTemplateId = ref(''); 

const columns = [
    { key: 'job_code', label: 'Code' },
    { key: 'title', label: 'Position Blueprint' },
    { key: 'dept_loc', label: 'Zone / Region' },
    { key: 'category', label: 'Class' },
    { key: 'experience', label: 'Seniority' },
    { key: 'valid_through', label: 'End Cycle' },
    { key: 'status', label: 'Status' }
];

const onPageChange = (page) => {
    router.get(route('talent.jobs.index'), { page }, { preserveState: true });
};

const bulkAction = (action) => {
    if(!confirm('Are you sure?')) return;
    
    router.post(route('talent.jobs.bulk'), {
        ids: selectedIds.value,
        action: action
    }, {
        onSuccess: () => {
             selectedIds.value = [];
        }
    });
};

const openExtendModal = () => {
    showExtendModal.value = true;
};

const openAssignModal = () => {
    showAssignModal.value = true;
};

const openInfoModal = () => {
    showInfoModal.value = true;
};

const confirmExtend = () => {
    router.post(route('talent.jobs.bulk'), {
        ids: selectedIds.value,
        action: 'extend',
        date: extendDate.value
    }, {
        onSuccess: () => {
             showExtendModal.value = false;
              selectedIds.value = [];
             extendDate.value = '';
        }
    });
};

const confirmAssign = () => {
     router.post(route('talent.jobs.bulk'), {
        ids: selectedIds.value,
        action: 'assign_template',
        template_id: assignTemplateId.value
    }, {
        onSuccess: () => {
             showAssignModal.value = false;
             selectedIds.value = [];
             assignTemplateId.value = '';
        }
    });
};
</script>
