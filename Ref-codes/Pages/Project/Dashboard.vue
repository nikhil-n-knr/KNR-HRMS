<template>
    <div class="min-h-screen bg-slate-50">
        <div class="w-full">
            <section class="bg-white border border-slate-200/70 shadow-sm rounded-2xl p-4 overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-8 text-white rounded-2xl">
                    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                        <div class="space-y-3">
                            <p class="text-sm uppercase tracking-[0.35em] text-indigo-100/80">Projects</p>
                            <h1 class="text-3xl lg:text-4xl font-black tracking-tight">Project Command Center</h1>
                            <p class="max-w-2xl text-sm text-indigo-100/85 leading-6">A unified workspace for your active initiatives, archived work, and project health metrics.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full lg:w-auto">
                            <div class="rounded-3xl bg-white/10 border border-white/20 p-4">
                                <p class="text-xs uppercase tracking-[0.3em] text-indigo-100/80">Total Projects</p>
                                <p class="mt-2 text-3xl font-black">{{ stats?.total ?? 0 }}</p>
                            </div>
                            <div class="rounded-3xl bg-white/10 border border-white/20 p-4">
                                <p class="text-xs uppercase tracking-[0.3em] text-indigo-100/80">Current Focus</p>
                                <p class="mt-2 text-3xl font-black capitalize">{{ currentView }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6 lg:p-8 bg-slate-50">
                    <div class="grid gap-4 lg:grid-cols-[1.4fr_1fr] xl:grid-cols-[1.6fr_1fr] items-center">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                            <div class="relative w-full sm:w-80">
                                <span class="absolute inset-y-0 left-4 flex items-center text-slate-400">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8" /><path d="M21 21l-4.35-4.35" /></svg>
                                </span>
                                <input
                                    v-model="searchQuery"
                                    @input="handleSearch"
                                    type="search"
                                    placeholder="Search projects, codes, clients..."
                                    class="w-full rounded-3xl border border-slate-200 bg-white py-3 pl-12 pr-4 text-sm text-slate-700 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                                />
                            </div>

                            <div class="flex items-center gap-2 flex-wrap">
                                <button
                                    @click="toggleView('active')"
                                    :class="currentView === 'active' ? 'bg-indigo-600 text-white shadow-lg' : 'bg-white text-slate-600 hover:bg-slate-100'"
                                    class="rounded-3xl px-5 py-3 text-sm font-semibold transition"
                                >
                                    Active
                                </button>
                                <button
                                    @click="toggleView('archived')"
                                    :class="currentView === 'archived' ? 'bg-indigo-600 text-white shadow-lg' : 'bg-white text-slate-600 hover:bg-slate-100'"
                                    class="rounded-3xl px-5 py-3 text-sm font-semibold transition"
                                >
                                    Archived
                                </button>
                                <button
                                    @click="toggleView('all')"
                                    :class="currentView === 'all' ? 'bg-indigo-600 text-white shadow-lg' : 'bg-white text-slate-600 hover:bg-slate-100'"
                                    class="rounded-3xl px-5 py-3 text-sm font-semibold transition"
                                >
                                    All
                                </button>
                            </div>
                        </div>

                        <div class="flex justify-start lg:justify-end">
                            <Link
                                :href="route('projects.create')"
                                class="inline-flex items-center justify-center gap-2 rounded-3xl bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-3 text-sm font-semibold text-white shadow-xl shadow-indigo-500/20 hover:from-indigo-700 hover:to-purple-700 transition"
                            >
                                <span>Create Project</span>
                            </Link>
                        </div>
                    </div>
                </div>
            

            <!-- Snapshot Cards -->
            <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Total</p>
                            <p class="mt-3 text-3xl font-black text-slate-900">{{ stats?.total ?? 0 }}</p>
                        </div>
                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h18" /><path d="M6 12h12" /><path d="M10 17h4" /></svg>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">All projects visible to your role.</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Active</p>
                            <p class="mt-3 text-3xl font-black text-slate-900">{{ stats?.active ?? 0 }}</p>
                        </div>
                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7" /></svg>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">Projects currently in progress.</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Archived</p>
                            <p class="mt-3 text-3xl font-black text-slate-900">{{ stats?.archived ?? 0 }}</p>
                        </div>
                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4l2 3h6l2-3h4a2 2 0 012 2v14a2 2 0 01-2 2z" /></svg>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">Historic projects you can restore anytime.</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Current View</p>
                            <p class="mt-3 text-3xl font-black capitalize text-slate-900">{{ currentView }}</p>
                        </div>
                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-700">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16" /><path d="M4 12h16" /><path d="M4 18h16" /></svg>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">Filter the dashboard to focus on the right projects.</p>
                </div>
            </section>

            <!-- Projects List -->
            <section class="space-y-6">
                <div class="grid gap-4 lg:grid-cols-[1fr_280px] items-center">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-slate-500">Projects</p>
                        <h2 class="text-2xl font-black text-slate-900">Deliverables overview</h2>
                    </div>
                    <div class="flex flex-wrap items-center justify-start gap-3">
                        <span class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-medium text-slate-600 border border-slate-200 shadow-sm">
                            <span class="h-2.5 w-2.5 rounded-full bg-indigo-500"></span>
                            Showing {{ projectItems.length }} projects
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-medium text-slate-600 border border-slate-200 shadow-sm">
                            <span class="text-slate-400">View mode</span>
                            <span class="font-semibold capitalize">{{ currentView }}</span>
                        </span>
                    </div>
                </div>

                <div v-if="projectItems.length > 0" class="grid gap-6 xl:grid-cols-3 lg:grid-cols-2">
                    <div v-for="project in projectItems" :key="project.id" class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-2xl">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">{{ project.code }}</p>
                                <h3 class="mt-3 text-xl font-semibold text-slate-900 line-clamp-2">{{ project.name }}</h3>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <span :class="getStatusBadge(project.status)" class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.25em]">
                                    {{ formatStatus(project.status) }}
                                </span>
                                <!-- Settings Gear (Admins Only) -->
                                <button 
                                    v-if="$page.props.auth.user.roles.some(r => r.name === 'Super Admin')"
                                    @click="openSettingsModal(project)" 
                                    class="h-8 w-8 flex items-center justify-center rounded-lg border border-slate-100 bg-white text-slate-400 transition hover:bg-slate-50 hover:text-indigo-600 shadow-sm"
                                    title="Protocol Settings"
                                >
                                    <Cog6ToothIcon class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        <p class="mt-4 text-sm leading-6 text-slate-600 line-clamp-3">{{ project.description || 'No project description available yet.' }}</p>

                        <div class="mt-6 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-3xl bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Client</p>
                                <p class="mt-2 font-semibold text-slate-800">{{ project.client?.name || 'Internal' }}</p>
                            </div>
                            <div class="rounded-3xl bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Tasks</p>
                                <p class="mt-2 font-semibold text-slate-800">{{ project.tasks_count || 0 }}</p>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <Link :href="route('projects.show', { project: project.id })" class="inline-flex items-center gap-2 rounded-3xl border border-indigo-100 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100">
                                    {{ project.status === 'archived' ? 'View' : 'Open' }}
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14" /><path d="M13 18l6-6-6-6" /></svg>
                                </Link>
                                <button @click="openEditModal(project)" class="rounded-3xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Edit</button>
                                
                                <!-- Admin Actions -->
                                <template v-if="$page.props.auth.user.roles.some(r => r.name === 'Super Admin')">
                                    <button 
                                        v-if="project.status !== 'archived'"
                                        @click="archiveProject(project)" 
                                        class="rounded-3xl border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 transition hover:bg-amber-100"
                                        title="Archive Project"
                                    >
                                        Archive
                                    </button>
                                    <button 
                                        v-else
                                        @click="unarchiveProject(project)" 
                                        class="rounded-3xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100"
                                        title="Restore Project"
                                    >
                                        Restore
                                    </button>
                                    <button 
                                        @click="deleteProject(project)" 
                                        class="rounded-3xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100"
                                        title="Delete Project"
                                    >
                                        Delete
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="rounded-3xl border border-dashed border-slate-200 bg-white p-10 text-center text-slate-500 shadow-sm">
                    <div class="mx-auto mb-5 inline-flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16" /><path d="M4 12h16" /><path d="M4 18h16" /></svg>
                    </div>
                    <h3 class="text-xl font-semibold text-slate-900">No projects found</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Try changing the filter or create a new project to begin tracking work.</p>
                    <div class="mt-6">
                        <Link :href="route('projects.create')" class="inline-flex items-center rounded-full bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/10 hover:bg-indigo-700 transition">Create first project</Link>
                    </div>
                </div>
            </section>

            <section v-if="props.projects?.links" class="flex justify-center">
                <Pagination :links="props.projects.links" />
            </section>
</section>
            <!-- Simple Edit Project Modal -->
            <Modal :show="showEditModal" @close="closeEditModal" title="Update Project Basics">
                <form @submit.prevent="submitEditForm" id="simpleEditProjectForm" class="space-y-6 p-1">
                    <BaseInput v-model="editForm.name" label="Project Name *" :error="editForm.errors.name" />
                    
                    <div class="space-y-1">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Current Status</label>
                        <select v-model="editForm.status" class="w-full rounded-2xl border border-slate-200 bg-white py-3 px-4 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                            <option value="planning">Planning</option>
                            <option value="active">Active</option>
                            <option value="on_hold">On Hold</option>
                            <option value="completed">Completed</option>
                        </select>
                        <p v-if="editForm.errors.status" class="text-xs text-red-600 mt-1">{{ editForm.errors.status }}</p>
                    </div>

                    <p class="text-[10px] text-slate-400 font-bold uppercase italic">Note: Advanced settings like membership, visibility, and cloning are available via the settings gear (Admin only).</p>
                </form>
                <template #footer>
                    <div class="flex gap-3 justify-end">
                        <SecondaryButton @click="closeEditModal">Discard</SecondaryButton>
                        <PrimaryButton @click="submitEditForm" :disabled="editForm.processing">Commit Changes</PrimaryButton>
                    </div>
                </template>
            </Modal>

            <!-- Advanced Settings Modal (Admin Only) -->
            <Modal :show="showSettingsModal" @close="closeSettingsModal" title="Project Protocol & Settings" maxWidth="3xl">
                <form @submit.prevent="submitSettingsForm" id="editProjectFormDashboard" class="space-y-8 p-1">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <BaseInput v-model="settingsForm.name" label="Project Name *" :error="settingsForm.errors.name" />
                        <BaseInput v-model="settingsForm.code" label="Project Code *" class="uppercase" :error="settingsForm.errors.code" />
                        
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Status</label>
                            <select v-model="settingsForm.status" class="w-full rounded-2xl border border-slate-200 bg-white py-3 px-4 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                                <option value="planning">Planning</option>
                                <option value="active">Active</option>
                                <option value="on_hold">On Hold</option>
                                <option value="completed">Completed</option>
                            </select>
                            <p v-if="settingsForm.errors.status" class="text-xs text-red-600 mt-1">{{ settingsForm.errors.status }}</p>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Visibility Protocol</label>
                            <select v-model="settingsForm.visibility" class="w-full rounded-2xl border border-slate-200 bg-white py-3 px-4 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                                <option value="public">Public (Global Access)</option>
                                <option value="team_locked">Team Locked (Members Only)</option>
                                <option value="stealth">Stealth (Hidden Registry)</option>
                                <option value="freelancer_mode">Freelancer (Task Isolated)</option>
                            </select>
                        </div>

                        <BaseInput v-model="settingsForm.start_date" type="date" label="Actual Start Date" :error="settingsForm.errors.start_date" />
                        <BaseInput v-model="settingsForm.deadline" type="date" label="Target Deadline" :error="settingsForm.errors.deadline" />
                    </div>

                    <!-- Teams/Freelancers based on visibility -->
                    <div v-if="settingsForm.visibility === 'team_locked'" class="bg-slate-50 p-6 rounded-[2rem] border border-slate-100 space-y-3">
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-tight">Assign Additional Teams</label>
                        <MultiUserSelect v-model="settingsForm.selected_teams" :items="teams" placeholder="Select teams..." />
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">All current and future team members will gain access.</p>
                    </div>

                    <div v-if="settingsForm.visibility === 'freelancer_mode'" class="bg-slate-50 p-6 rounded-[2rem] border border-slate-100 space-y-3">
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-tight">Assign Freelance Nodes</label>
                        <MultiUserSelect v-model="settingsForm.selected_freelancers" :items="allEmployees" placeholder="Select freelancers..." />
                    </div>

                    <!-- Project Owners -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Project Authority (Owners)</label>
                            <button @click.prevent="showAllOwners = !showAllOwners" class="text-[10px] font-black uppercase tracking-widest text-indigo-600 hover:text-indigo-800 transition-colors">
                                {{ showAllOwners ? 'Managers Only' : 'All Employees' }}
                            </button>
                        </div>
                        <MultiUserSelect v-model="settingsForm.owners" :items="showAllOwners ? allEmployees : managers" placeholder="Select owners..." />
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Operational Description</label>
                        <textarea v-model="settingsForm.description" rows="3" class="w-full rounded-2xl border border-slate-200 bg-white py-3 px-4 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"></textarea>
                        <p v-if="settingsForm.errors.description" class="text-xs text-red-600 mt-1">{{ settingsForm.errors.description }}</p>
                    </div>

                    <!-- Cloning Option -->
                    <div class="bg-amber-50 p-6 rounded-[2rem] border border-amber-100 space-y-3">
                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                            <label class="block text-xs font-black text-amber-800 uppercase tracking-tight">Import / Clone Blueprint</label>
                        </div>
                        <BaseSelect v-model="settingsForm.clone_from_id" label="Merge from existing project">
                            <option :value="null">No merge (keep existing structure)</option>
                            <option v-for="proj in projectItems" :key="proj.id" :value="proj.id" :disabled="proj.id === settingsForm.id">
                                {{ proj.name }} ({{ proj.code }})
                            </option>
                        </BaseSelect>
                        <p class="text-[10px] font-bold text-amber-600 uppercase tracking-widest">WARNING: This will append all modules from the source project into the current one.</p>
                    </div>
                </form>
                <template #footer>
                    <div class="flex flex-wrap gap-3 justify-end">
                        <SecondaryButton @click="closeSettingsModal">Cancel</SecondaryButton>
                        <PrimaryButton @click="submitSettingsForm" :disabled="settingsForm.processing">Save Full Protocol</PrimaryButton>
                    </div>
                </template>
            </Modal>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, Link, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import BaseInput from '@/Components/BaseInput.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import MultiUserSelect from '@/Components/MultiUserSelect.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import { Cog6ToothIcon } from '@heroicons/vue/24/outline';

defineOptions({ layout: MainLayout });

const props = defineProps({
    projects: [Array, Object],
    stats: Object,
    filters: Object,
    managers: Array,
    teams: Array,
    allEmployees: Array
});

const currentView = computed(() => {
    const params = new URLSearchParams(window.location.search);
    return params.get('view') || 'active';
});

const toggleView = (mode) => {
    if (currentView.value === mode) return;
    router.visit(route('projects.index'), {
        data: {
            view: mode,
            search: props.filters?.search
        },
        preserveState: true,
        replace: true
    });
};

const searchQuery = ref(props.filters?.search || '');
let searchTimeout = null;

const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('projects.index'), {
            search: searchQuery.value,
            view: currentView.value
        }, {
            preserveState: true,
            replace: true
        });
    }, 300);
};

const archiveProject = (project) => {
    if (confirm(`Are you sure you want to archive "${project.name}"?`)) {
        router.post(route('projects.archive', project.id));
    }
};

const unarchiveProject = (project) => {
    router.post(route('projects.unarchive', project.id));
};

const deleteProject = (project) => {
    if (confirm(`PERMANENT DELETE WARNING: Are you sure you want to delete "${project.name}"?\n\nThis will move the project to trash.`)) {
        router.delete(route('projects.destroy', project.id));
    }
};

const showEditModal = ref(false);
const showSettingsModal = ref(false);
const showAllOwners = ref(false);

const editForm = useForm({
    id: null,
    name: '',
    status: '',
    client_id: null,
    code: ''
});

const settingsForm = useForm({
    id: null,
    name: '',
    client_id: null,
    code: '',
    description: '',
    status: '',
    visibility: 'public',
    start_date: '',
    deadline: '',
    owners: [],
    selected_teams: [],
    selected_freelancers: [],
    clone_from_id: null
});

const projectItems = computed(() => props.projects?.data || props.projects || []);

const openEditModal = (project) => {
    editForm.id = project.id;
    editForm.name = project.name;
    editForm.status = project.status;
    editForm.client_id = project.client_id;
    editForm.code = project.code;
    editForm.clearErrors();
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    editForm.reset();
};

const submitEditForm = () => {
    editForm.put(route('projects.update', { project: editForm.id }), {
        onSuccess: () => closeEditModal(),
        preserveScroll: true
    });
};

const openSettingsModal = (project) => {
    settingsForm.id = project.id;
    settingsForm.name = project.name;
    settingsForm.client_id = project.client_id;
    settingsForm.code = project.code;
    settingsForm.description = project.description;
    settingsForm.status = project.status;
    settingsForm.visibility = project.visibility || 'public';
    settingsForm.start_date = project.start_date ? project.start_date.split('T')[0] : '';
    settingsForm.deadline = project.deadline ? project.deadline.split('T')[0] : '';
    settingsForm.owners = project.owners || [];
    settingsForm.selected_teams = project.assigned_teams || [];
    settingsForm.selected_freelancers = project.assigned_freelancers || [];
    settingsForm.clone_from_id = null;
    settingsForm.clearErrors();
    showSettingsModal.value = true;
};

const closeSettingsModal = () => {
    showSettingsModal.value = false;
    settingsForm.reset();
};

const submitSettingsForm = () => {
    settingsForm.put(route('projects.update', { project: settingsForm.id }), {
        onSuccess: () => closeSettingsModal(),
        preserveScroll: true
    });
};

const formatStatus = (status) => {
    if (!status) return 'Unknown';
    return status.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const getStatusBadge = (status) => {
    switch (status) {
        case 'active': return 'bg-emerald-100 text-emerald-700 border border-emerald-200';
        case 'planning': return 'bg-blue-100 text-blue-700 border border-blue-200';
        case 'on_hold': return 'bg-amber-100 text-amber-700 border border-amber-200';
        case 'completed': return 'bg-slate-100 text-slate-700 border border-slate-200';
        case 'archived': return 'bg-rose-100 text-rose-700 border border-rose-200';
        default: return 'bg-slate-100 text-slate-700 border border-slate-200';
    }
};
</script>
