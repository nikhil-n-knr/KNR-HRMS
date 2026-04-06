<template>
    <div class="space-y-6 animate-fade-in-up">
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
            <div class="md:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Client Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs uppercase tracking-wide text-gray-400 font-bold mb-1">Contact Person</label>
                        <div class="text-gray-900 font-medium">{{ client.contact_person || 'N/A' }}</div>
                    </div>
                     <div>
                        <label class="block text-xs uppercase tracking-wide text-gray-400 font-bold mb-1">Email</label>
                        <div class="text-gray-900 font-medium">{{ client.email || 'N/A' }}</div>
                    </div>
                     <div>
                        <label class="block text-xs uppercase tracking-wide text-gray-400 font-bold mb-1">Status</label>
                         <span v-if="client.portal_access" class="px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                            Portal Active
                         </span>
                         <span v-else class="px-2 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600">
                            Offline
                         </span>
                    </div>
                </div>
                
                <div class="border-t pt-4">
                    <h4 class="text-sm font-bold text-gray-900 mb-3">Service Contract</h4>
                    <div class="flex gap-8">
                        <div>
                             <label class="block text-xs uppercase tracking-wide text-gray-400 font-bold mb-1">Start Date</label>
                            <div class="text-gray-600 font-mono">{{ client.contract_start || 'Not set' }}</div>
                        </div>
                         <div>
                             <label class="block text-xs uppercase tracking-wide text-gray-400 font-bold mb-1">End Date</label>
                            <div class="text-gray-600 font-mono">{{ client.contract_end || 'Not set' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats / Projects -->
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Active Projects</h3>
                    <div v-if="client.projects && client.projects.length" class="space-y-3">
                        <div v-for="project in client.projects" :key="project.id" class="p-3 bg-gray-50 rounded-lg hover:bg-emerald-50 transition border border-gray-200">
                            <div class="font-medium text-emerald-700">{{ project.name }}</div>
                            <div class="flex justify-between items-center text-xs mt-1">
                                <span class="font-mono text-gray-500">{{ project.code }}</span>
                                <span class="capitalize px-1.5 py-0.5 rounded bg-white border text-gray-600">{{ project.status }}</span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-400 text-sm">
                        No active projects linked.
                    </div>
                </div>

                <!-- Client Users -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Client Users</h3>
                        <button @click="showInviteModal = true" class="text-sm text-emerald-600 hover:text-emerald-900 font-medium">
                            + Invite User
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

        <!-- Invite Modal -->
        <Modal :show="showInviteModal" @close="showInviteModal = false">
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
import { Link, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputError from '@/Components/InputError.vue';
import { ref } from 'vue';
import axios from 'axios';

defineOptions({ layout: MainLayout });

const props = defineProps({
    client: Object
});

const showInviteModal = ref(false);
const inviteForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
});

const submitInvite = () => {
    inviteForm.post(route('projects.clients.invite', props.client.id), {
        onSuccess: () => {
            showInviteModal.value = false;
            inviteForm.reset();
        }
    });
};

const killSwitch = async (user) => {
    if (user.is_active && !confirm(`Revoke portal access for ${user.name}?`)) return;
    
    try {
        await axios.post(route('clients.users.kill', user.id));
        // Refresh page data to reflect change
        window.location.reload();
    } catch (e) {
        alert("Failed to update access status.");
    }
};
</script>
