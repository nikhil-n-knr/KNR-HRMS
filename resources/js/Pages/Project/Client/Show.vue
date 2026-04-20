<template>
    <div class="space-y-6 animate-fade-in-up">
        <!-- New Password Banner -->
        <div v-if="$page.props.flash.flash_password" class="p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-center justify-between shadow-sm animate-pulse">
            <div>
                <div class="text-[10px] font-black text-amber-500 uppercase tracking-widest leading-none mb-1">Security Artifact Generated</div>
                <div class="text-sm font-black text-slate-800">
                    Temporal Key: <code class="bg-white px-2 py-1 rounded border border-amber-300 select-all">{{ $page.props.flash.flash_password }}</code>
                </div>
            </div>
            <button @click="$page.props.flash.flash_password = null" class="text-amber-400 hover:text-amber-600 transition-colors">
                <i class="fas fa-times-circle"></i>
            </button>
        </div>
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Link :href="route('projects.clients.index')" class="p-2 hover:bg-white rounded-full transition text-gray-500 hover:text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">{{ client.name }}</h1>
                    <div class="text-sm text-gray-500 font-mono">{{ client.code }}</div>
                </div>
            </div>
            <div class="flex gap-2">
                 <Link :href="route('projects.clients.index')" class="px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-700 hover:bg-gray-50 transition">
                    Back to List
                </Link>
                <!-- Add Edit Button if needed using v-can -->
            </div>
        </div>

        <!-- Details Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Info -->
            <div class="md:col-span-2 bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl border border-white/50 p-6 space-y-6">
                <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-100 pb-3 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Client Details
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="p-4 bg-gray-50/50 rounded-xl border border-gray-100">
                        <label class="block text-[10px] uppercase tracking-widest text-gray-500 font-black mb-1">Contact Person</label>
                        <div class="text-gray-900 font-medium">{{ client.contact_person || 'N/A' }}</div>
                    </div>
                     <div class="p-4 bg-gray-50/50 rounded-xl border border-gray-100">
                        <label class="block text-[10px] uppercase tracking-widest text-gray-500 font-black mb-1">Email</label>
                        <div class="text-gray-900 font-medium">{{ client.email || 'N/A' }}</div>
                    </div>
                     <div class="p-4 bg-gray-50/50 rounded-xl border border-gray-100 md:col-span-2 flex justify-between items-center">
                        <label class="block text-[10px] uppercase tracking-widest text-gray-500 font-black">Portal Status</label>
                         <span v-if="client.portal_access" class="px-3 py-1 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-700 tracking-wider uppercase border border-emerald-200 shadow-sm">
                            Active
                         </span>
                         <span v-else class="px-3 py-1 rounded-full text-[10px] font-black bg-gray-100 text-gray-500 tracking-wider uppercase border border-gray-200">
                            Offline
                         </span>
                    </div>
                </div>
                
                <div class="border-t border-gray-100 pt-5">
                    <h4 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Service Contract
                    </h4>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="p-4 bg-gray-50/50 rounded-xl border border-gray-100">
                             <label class="block text-[10px] uppercase tracking-widest text-gray-500 font-black mb-1">Start Date</label>
                            <div class="text-gray-800 font-bold">{{ client.contract_start ? new Date(client.contract_start).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric'}) : 'Not set' }}</div>
                        </div>
                         <div class="p-4 bg-gray-50/50 rounded-xl border border-gray-100">
                             <label class="block text-[10px] uppercase tracking-widest text-gray-500 font-black mb-1">End Date</label>
                            <div class="text-gray-800 font-bold">{{ client.contract_end ? new Date(client.contract_end).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric'}) : 'Not set' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats / Projects -->
            <div class="space-y-6">
                <div class="bg-white/80 backdrop-blur-xl shadow-xl border border-white/50 rounded-2xl p-6 transition-all duration-300">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            Linked Projects
                        </h3>
                        <button @click="showAssignModal = true" class="text-xs px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-lg hover:bg-emerald-600 hover:text-white font-black tracking-wider uppercase transition-all duration-200 shadow-sm">
                            Map Project
                        </button>
                    </div>
                    
                    <div v-if="client.projects && client.projects.length" class="space-y-4">
                        <div v-for="project in client.projects" :key="project.id" class="p-4 bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-lg hover:-translate-y-0.5 hover:border-emerald-200 transition-all duration-300 group relative">
                            <!-- Unlink Action -->
                            <button @click.prevent="unassignProject(project)" class="absolute top-4 right-4 p-1.5 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-lg opacity-0 group-hover:opacity-100 transition-all duration-200" title="Unlink Project">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>

                            <div class="pr-8">
                                <h4 class="font-bold text-gray-900 group-hover:text-emerald-700 transition-colors text-sm">
                                    <Link :href="route('projects.index', { project: project.id })">{{ project.name }}</Link>
                                </h4>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="font-mono text-[10px] uppercase text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full font-bold tracking-widest">{{ project.code }}</span>
                                    <span class="capitalize px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 text-[10px] font-black tracking-widest">{{ project.status }}</span>
                                </div>
                            </div>
                            
                            <div class="mt-4 grid grid-cols-3 gap-2 border-t border-gray-50 pt-4">
                                <div>
                                    <div class="text-[9px] uppercase font-black tracking-widest text-gray-400">Tasks</div>
                                    <div class="text-sm font-bold text-gray-800">{{ project.tasks_count || 0 }}</div>
                                </div>
                                <div>
                                    <div class="text-[9px] uppercase font-black tracking-widest text-gray-400">Sprints</div>
                                    <div class="text-sm font-bold text-gray-800">{{ project.sprints_count || 0 }}</div>
                                </div>
                                 <div class="text-right">
                                    <div class="text-[9px] uppercase font-black tracking-widest text-gray-400">Progress</div>
                                    <div class="text-sm font-black text-emerald-600">{{ project.manual_progress_percentage || 0 }}%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-400 text-sm">
                        No active projects linked.
                    </div>
                </div>

                <!-- Client Users -->
                <div class="bg-white/80 backdrop-blur-xl shadow-xl border border-white/50 rounded-2xl p-6 transition-all duration-300">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Client Users
                        </h3>
                        <button @click="showInviteModal = true" class="text-xs px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-lg hover:bg-emerald-600 hover:text-white font-black tracking-wider uppercase transition-all duration-200 shadow-sm">
                            Invite User
                        </button>
                    </div>
                    
                    <div v-if="client.client_users && client.client_users.length" class="space-y-3">
                        <div v-for="user in client.client_users" :key="user.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200 group">
                            <div>
                                <div class="font-medium text-gray-900 flex items-center gap-2">
                                    {{ user.name }}
                                    <span v-if="!user.is_active" class="px-1.5 py-0.5 bg-red-100 text-red-600 text-sm rounded uppercase font-bold">Suspended</span>
                                </div>
                                <div class="text-xs text-gray-500">{{ user.email }}</div>
                            </div>
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click="openPasswordReset(user)" class="p-1.5 rounded-lg border border-gray-200 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 hover:border-emerald-200 transition-colors" title="Reset Password">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                    </svg>
                                </button>
                                <button @click="killSwitch(user)" :class="['p-1.5 rounded-lg border transition-colors', user.is_active ? 'border-gray-200 text-gray-400 hover:text-red-600 hover:bg-red-50 hover:border-red-200' : 'bg-green-600 text-white border-green-600 shadow-sm']" :title="user.is_active ? 'Revoke Access' : 'Restore Access'">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-6 text-gray-400 text-sm">
                        No portal users created yet.
                    </div>
                </div>
            </div>
        </div>

        <!-- Password Reset Modal -->
        <Modal :show="showPasswordModal" @close="showPasswordModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Set/Reset Password: {{ selectedUser?.name }}</h2>
                <form @submit.prevent="submitPasswordReset" class="space-y-4">
                    <div>
                        <InputLabel value="New Password" />
                        <TextInput v-model="passwordForm.password" type="password" class="mt-1 block w-full" placeholder="Enter new password" required />
                        <InputError :message="passwordForm.errors.password" />
                    </div>
                    <div>
                        <InputLabel value="Confirm New Password" />
                        <TextInput v-model="passwordForm.password_confirmation" type="password" class="mt-1 block w-full" placeholder="Confirm new password" required />
                        <InputError :message="passwordForm.errors.password_confirmation" />
                    </div>
                    
                    <div class="flex justify-end gap-2 mt-6">
                        <SecondaryButton @click="showPasswordModal = false">Cancel</SecondaryButton>
                        <PrimaryButton :disabled="passwordForm.processing" class="!bg-emerald-600 hover:!bg-emerald-700">Update Password</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Assign Project Modal -->
        <Modal :show="showAssignModal" @close="showAssignModal = false" max-width="md">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Map Project to Client</h2>
                <form @submit.prevent="submitAssign" class="space-y-4">
                    <div>
                        <InputLabel value="Select Project" />
                        <select v-model="assignForm.project_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                            <option value="">-- Choose Project --</option>
                            <option v-for="proj in unassignedProjects" :key="proj.id" :value="proj.id">
                                {{ proj.name }} ({{ proj.code }})
                            </option>
                        </select>
                        <InputError :message="assignForm.errors.project_id" />
                    </div>
                    
                    <div class="flex justify-end gap-2 mt-6">
                        <SecondaryButton @click="showAssignModal = false">Cancel</SecondaryButton>
                        <PrimaryButton :disabled="assignForm.processing" class="!bg-emerald-600 hover:!bg-emerald-700">Map Project</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Invite Modal -->
        <Modal :show="showInviteModal" @close="showInviteModal = false">
...
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Invite Client User</h2>
                <form @submit.prevent="submitInvite" class="space-y-4">
                    <div>
                        <InputLabel value="Name" />
                        <TextInput v-model="inviteForm.name" class="mt-1 block w-full" required />
                        <InputError :message="inviteForm.errors.name" />
                    </div>
                    <div>
                        <InputLabel value="Email" />
                        <TextInput v-model="inviteForm.email" type="email" class="mt-1 block w-full" required />
                        <InputError :message="inviteForm.errors.email" />
                    </div>
                    <div>
                        <InputLabel value="Password" />
                        <TextInput v-model="inviteForm.password" type="password" class="mt-1 block w-full" required />
                         <InputError :message="inviteForm.errors.password" />
                    </div>
                    <div>
                        <InputLabel value="Confirm Password" />
                        <TextInput v-model="inviteForm.password_confirmation" type="password" class="mt-1 block w-full" required />
                    </div>
                    
                    <div class="flex justify-end gap-2 mt-6">
                        <SecondaryButton @click="showInviteModal = false">Cancel</SecondaryButton>
                        <PrimaryButton :disabled="inviteForm.processing">Send Invite</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { Link, useForm, router, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputError from '@/Components/InputError.vue';
import { ref } from 'vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    client: Object,
    unassignedProjects: Array
});

// Assign Project Logic
const showAssignModal = ref(false);
const assignForm = useForm({
    project_id: ''
});

const submitAssign = () => {
    assignForm.post(route('projects.clients.assign-project', props.client.id), {
        onSuccess: () => {
            showAssignModal.value = false;
            assignForm.reset();
        }
    });
};

const unassignProject = (project) => {
    if (confirm(`Are you sure you want to unlink ${project.name} from this client? Users will immediately lose access.`)) {
        router.post(route('projects.clients.unassign-project', props.client.id), { project_id: project.id }, {
            preserveScroll: true
        });
    }
};

const showInviteModal = ref(false);
const inviteForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
});

// Password Reset Logic
const showPasswordModal = ref(false);
const selectedUser = ref(null);
const passwordForm = useForm({
    password: '',
    password_confirmation: ''
});

const openPasswordReset = (user) => {
    selectedUser.value = user;
    passwordForm.reset();
    showPasswordModal.value = true;
};

const submitPasswordReset = () => {
    passwordForm.post(route('clients.users.reset-password', selectedUser.value.id), {
        onSuccess: () => {
            showPasswordModal.value = false;
            passwordForm.reset();
            alert("Password updated successfully.");
        }
    });
};

const submitInvite = () => {
    inviteForm.post(route('projects.clients.invite', props.client.id), {
        onSuccess: () => {
            showInviteModal.value = false;
            inviteForm.reset();
        }
    });
};

const killSwitch = (user) => {
    if (user.is_active && !confirm(`Revoke portal access for ${user.name}?`)) return;
    
    router.post(route('clients.users.kill', user.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Updated automatically via Inertia
        }
    });
};
</script>
