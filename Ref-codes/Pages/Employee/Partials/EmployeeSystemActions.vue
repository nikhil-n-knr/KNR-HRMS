<template>
    <div class="mt-6 pt-4 border-t border-gray-100 flex flex-wrap gap-4 items-center justify-between">
            <div class="flex items-center gap-4">
                <!-- 1. Enable Login (If not linked) -->
                <div v-if="!employee.user_id">
                <button 
                    @click="initiateCreateLogin" 
                    :disabled="creatingLogin"
                    class="px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg text-sm font-medium hover:bg-indigo-100 transition flex items-center gap-2"
                >
                    <svg v-if="creatingLogin" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                    Enable Login Access
                </button>
                </div>

                <!-- 2. System Controls (If linked) -->
                <div v-else class="flex items-center gap-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                    <svg class="mr-1.5 h-2 w-2 text-emerald-400" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg>
                    Login Enabled
                    </span>
                    
                    <div class="h-4 w-px bg-gray-300 mx-2"></div>

                    <!-- Reset Password -->
                    <button @click="showPasswordModal = true" class="text-sm text-gray-600 hover:text-indigo-600 font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                    Reset Password
                    </button>

                    <!-- Change Role -->
                    <button @click="showRoleModal = true" class="text-sm text-gray-600 hover:text-indigo-600 font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
                    Change Role
                    </button>
                    
                    <!-- Block Access -->
                    <button @click="initiateToggleStatus" class="text-sm text-gray-600 hover:text-red-600 font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                    {{ employee.user?.status === 'active' ? 'Block Access' : 'Unblock Access' }}
                    </button>
                </div>
            </div>

            <!-- Export -->
            <button @click="exportProfile" class="flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                Export Profile
            </button>


        <!-- Associated Modals (Owned by this component) -->
        <PasswordResetModal :show="showPasswordModal" :employee="employee" @close="showPasswordModal = false" />
        <RoleSelectionModal :show="showRoleModal" :employee="employee" @close="showRoleModal = false" @saved="$emit('refresh')" />
        
        <ConfirmationModal 
            :show="showConfirmModal" 
            v-bind="confirmModalConfig"
            @close="showConfirmModal = false"
            @confirm="confirmModalConfig.action"
        />

        <CredentialModal
            :show="showCredentialModal"
            :email="newCredentials.email"
            :password="newCredentials.password"
            @close="showCredentialModal = false"
        />
    </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';
import PasswordResetModal from '@/Components/Modals/PasswordResetModal.vue';
import RoleSelectionModal from '@/Components/Modals/RoleSelectionModal.vue';
import ConfirmationModal from '@/Components/Modals/ConfirmationModal.vue';
import CredentialModal from '@/Components/Modals/CredentialModal.vue';

const props = defineProps({
    employee: { type: Object, required: true }
});

const emit = defineEmits(['refresh']);
const toast = useToastStore();

const showPasswordModal = ref(false);
const showRoleModal = ref(false);
const showConfirmModal = ref(false);
const showCredentialModal = ref(false);

const confirmModalConfig = ref({ title: '', message: '' });
const newCredentials = ref({ email: '', password: '' });
const creatingLogin = ref(false);

const initiateCreateLogin = () => {
    confirmModalConfig.value = {
        title: 'Create System Login',
        message: `Create system login for ${props.employee.first_name}?`,
        confirmText: 'Create Login',
        type: 'primary',
        action: performCreateLogin
    };
    showConfirmModal.value = true;
};

const performCreateLogin = async () => {
    showConfirmModal.value = false;
    creatingLogin.value = true;
    try {
        const response = await axios.post(`/api/admin/employees/${props.employee.id}/create-login`, {
            email: props.employee.email
        });
        
        newCredentials.value = {
            email: response.data.user.email,
            password: response.data.password
        };
        showCredentialModal.value = true;
        emit('refresh');
    } catch (error) {
        toast.error(error.response?.data?.message || "Failed to create login");
    } finally {
        creatingLogin.value = false;
    }
};

const initiateToggleStatus = () => {
    const isBlocking = props.employee.user?.status === 'active';
    confirmModalConfig.value = {
        title: isBlocking ? 'Block Access' : 'Unblock Access',
        message: `Are you sure you want to ${isBlocking ? 'BLOCK' : 'UNBLOCK'} access?`,
        confirmText: isBlocking ? 'Block Access' : 'Unblock Access',
        type: isBlocking ? 'danger' : 'primary',
        action: performToggleStatus
    };
    showConfirmModal.value = true;
};

const performToggleStatus = async () => {
     showConfirmModal.value = false;
    try {
        await axios.put(`/api/admin/employees/${props.employee.id}/toggle-status`);
        toast.success("Access status updated");
        emit('refresh');
    } catch (e) {
        toast.error("Failed to update status");
    }
};

const exportProfile = () => {
    window.location.href = `/api/admin/employees/${props.employee.id}/export`;
};
</script>
