<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import { usePage, router } from '@inertiajs/vue3'; // Switch to Inertia
import SmartTabLayout from '@/Layouts/SmartTabLayout.vue';
import UserOverview from './Components/UserOverview.vue';
import UserAccess from './Components/UserAccess.vue';
import UserSecurity from './Components/UserSecurity.vue';
import { useToastStore } from '@/stores/toast';
import { CheckCircleIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';

const page = usePage();
const toast = useToastStore();

// State
const loading = ref(true);
const saving = ref(false);
const roles = ref([]);
const departments = ref([]);
const locations = ref([]);
const userId = ref(null);

const form = ref({
    name: '',
    email: '',
    employee_id: '',
    status: 'active',
    department_id: '',
    location_id: '',
    role_ids: [],
    password: '',
    password_confirmation: '',
    force_mfa: false
});

// Tabs Configuration
const tabs = [
    { id: 'overview', label: 'Overview' },
    { id: 'access', label: 'Access Control' },
    { id: 'security', label: 'Security' },
    { id: 'personal', label: 'Personal (Coming Soon)' },
    { id: 'docs', label: 'Documents (Coming Soon)' },
];

// Active Tab from URL
const activeTab = computed(() => {
    const params = new URLSearchParams(window.location.search);
    return params.get('tab') || 'overview';
});

// Load Data
const loadData = async () => {
    // Assuming /users/:id/edit logic
    const path = window.location.pathname;
    const parts = path.split('/');
    // Handle specific route patterns if needed, simplified:
    const id = parts[parts.length - 1] === 'edit' ? parts[parts.length - 2] : parts[parts.length - 1];
    userId.value = id;

    try {
        const [uRes, rRes, dRes, lRes] = await Promise.all([
            axios.get(`/api/admin/users/${id}`),
            axios.get('/api/admin/roles'),
            axios.get('/api/admin/departments'), 
            axios.get('/api/admin/locations')
        ]);
        
        roles.value = rRes.data.data || rRes.data;
        departments.value = dRes.data.data || dRes.data || [];
        locations.value = lRes.data.data || lRes.data || [];

        const user = uRes.data.data;
        form.value = {
            ...form.value,
            name: user.name,
            email: user.email,
            employee_id: user.employee_id,
            status: user.status,
            department_id: user.department?.id || '',
            location_id: user.location?.id || '',
            role_ids: user.roles.map(r => r.id),
            force_mfa: user.force_mfa 
        };
        
    } catch (error) {
        toast.error('Failed to load user data.');
        console.error(error);
    } finally {
        loading.value = false;
    }
};

const updateUser = async () => {
    if (form.value.password && form.value.password !== form.value.password_confirmation) {
        toast.error("Passwords do not match");
        return;
    }
    
    saving.value = true;
    try {
        await axios.put(`/api/admin/users/${userId.value}`, form.value);
        toast.success("User updated successfully");
    } catch (error) {
        toast.error('Failed to update user.');
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    loadData();
});
</script>

<template>
    <SmartTabLayout 
        :title="form.name || 'Edit User'" 
        :tabs="tabs" 
        :activeTab="activeTab"
    >
        <template #meta>
             ID: {{ form.employee_id || 'N/A' }} • {{ form.email }}
        </template>

        <template #actions>
             <button 
                @click="router.visit('/users')"
                class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 shadow-sm"
             >
                <ArrowLeftIcon class="w-4 h-4" />
                Back
             </button>
             <button 
                @click="updateUser"
                :disabled="saving"
                class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm disabled:opacity-70"
             >
                <CheckCircleIcon v-if="!saving" class="w-4 h-4" />
                <svg v-else class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                {{ saving ? 'Saving...' : 'Save Changes' }}
             </button>
        </template>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center py-20">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>

        <!-- Content -->
        <div v-else>
            <UserOverview 
                v-if="activeTab === 'overview'" 
                v-model="form" 
                :departments="departments" 
                :locations="locations" 
            />

            <UserAccess 
                v-if="activeTab === 'access'" 
                v-model="form" 
                :roles="roles" 
            />

            <UserSecurity 
                v-if="activeTab === 'security'" 
                v-model="form" 
            />

            <div v-if="['personal', 'docs'].includes(activeTab)" class="text-center py-12 bg-white/50 rounded-xl border border-dashed border-gray-300">
                <p class="text-gray-500">This module is under development.</p>
            </div>
        </div>

    </SmartTabLayout>
</template>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.3s ease-out forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(5px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
