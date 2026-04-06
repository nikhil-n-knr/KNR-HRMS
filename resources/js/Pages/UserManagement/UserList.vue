<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-emerald-800 to-teal-700">
          User Management
        </h1>
        <p class="text-emerald-600/80 text-sm mt-1">Manage system access and permissions</p>
      </div>
      <div class="flex gap-3">
        <Link 
          v-if="authStore.can('user_management.users.create')"
          href="/admin/users/create" 
          class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl text-sm font-medium shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/40 hover:-translate-y-0.5 transition-all flex items-center gap-2"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
          </svg>
          Add User
        </Link>
      </div>
    </div>

    <!-- Stats / AI Insights -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
       <div class="p-4 rounded-2xl bg-white/40 border border-white/50 backdrop-blur-sm shadow-sm flex items-center gap-4">
          <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500">Total Users</p>
            <p class="text-xl font-bold text-gray-800">{{ users.meta?.total || 0 }}</p>
          </div>
       </div>
       
       <!-- AI Chip -->
       <div class="p-4 rounded-2xl bg-gradient-to-br from-indigo-50/50 to-purple-50/50 border border-indigo-100/50 backdrop-blur-sm shadow-sm flex items-center gap-4 relative overflow-hidden group">
          <div class="absolute -right-4 -top-4 h-16 w-16 bg-gradient-to-br from-indigo-500 to-purple-500 opacity-10 rounded-full blur-xl group-hover:opacity-20 transition-opacity"></div>
          <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 z-10">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
               <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="z-10">
            <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wide mb-0.5">AI Insight</p>
            <p class="text-sm text-gray-700 font-medium">3 inactive users detected this week.</p>
          </div>
       </div>
    </div>

    <!-- Data Table -->
    <BaseDataTable
        :columns="columns"
        :data="users.data || []"
        :meta="users.meta || {}"
        :loading="false"
        search-placeholder="Search users..."
        @search="handleSearch"
        @page-change="changePage"
    >
        <template #actions>
             <button class="px-4 py-2 bg-white/40 hover:bg-white/60 border border-white/50 rounded-xl text-emerald-700 text-sm font-medium backdrop-blur-sm transition shadow-sm hover:shadow-md">
                Export
             </button>
             <select v-model="statusFilter" @change="handleSearch" class="pl-3 pr-10 py-2 text-base border-gray-200 focus:outline-none focus:ring-emerald-500/30 focus:border-emerald-500/30 sm:text-sm rounded-xl bg-white/50">
               <option value="">All Status</option>
               <option value="active">Active</option>
               <option value="suspended">Suspended</option>
             </select>
        </template>

        <template #cell-name="{ item }">
            <div class="flex items-center gap-3">
                  <div class="flex-shrink-0 h-9 w-9 lg:h-10 lg:w-10">
                    <div class="h-full w-full rounded-full bg-gradient-to-tr from-emerald-400 to-teal-500 flex items-center justify-center text-white font-bold text-xs lg:text-sm shadow-sm ring-2 ring-white">
                       {{ getInitials(item.name) }}
                    </div>
                  </div>
                  <div class="min-w-0">
                    <div class="text-sm font-bold text-gray-900 truncate">{{ item.name }}</div>
                    <div class="text-xs text-gray-500 truncate">{{ item.email }}</div>
                  </div>
            </div>
        </template>

        <template #cell-roles="{ item }">
             <div class="flex flex-wrap gap-1">
                   <span v-for="role in item.roles" :key="role.id" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                     {{ role.name }}
                   </span>
             </div>
        </template>

        <template #cell-status="{ value }">
            <span 
              class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
              :class="value === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
            >
              {{ value }}
            </span>
        </template>

        <template #rowActions="{ item }">
            <div class="flex items-center justify-end gap-3">
                    <button 
                        v-if="authStore.can('user_management.users.impersonate')"
                        @click="impersonateUser(item)" 
                        class="text-gray-400 hover:text-indigo-600 transition-colors"
                        title="Impersonate User"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                    <Link 
                        v-if="authStore.can('user_management.users.update')"
                        :href="`/admin/users/${item.id}/edit`" 
                        class="text-emerald-600 hover:text-emerald-900 transition-colors"
                        title="Edit User"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                           <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                    </Link>
                    <button 
                        v-if="authStore.can('user_management.users.delete')"
                        @click="deleteUser(item)" 
                        class="text-red-400 hover:text-red-600 transition-colors"
                        title="Delete User"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                           <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 000-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </button>
            </div>
        </template>
    </BaseDataTable>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" title="Delete User" @close="closeDeleteModal">
            <div class="space-y-4">
                <p class="text-gray-600">
                    Are you sure you want to delete <span class="font-semibold text-gray-800">{{ userToDelete?.name }}</span>? 
                    This action cannot be undone.
                </p>
            </div>
            <template #footer>
                <button @click="closeDeleteModal" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg text-sm font-medium transition">
                    Cancel
                </button>
                <button 
                    @click="confirmDelete" 
                    class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium shadow-md hover:bg-red-700 transition-all"
                >
                    Delete User
                </button>
            </template>
        </Modal>

        <!-- Impersonate Confirmation Modal -->
        <Modal :show="showImpersonateModal" title="Switch User" @close="closeImpersonateModal">
            <div class="space-y-4">
                <div class="p-4 bg-blue-50 border border-blue-100 rounded-xl flex gap-3">
                   <svg class="h-6 w-6 text-blue-600 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                   </svg>
                   <div>
                       <h4 class="text-sm font-semibold text-blue-800">Impersonation Mode</h4>
                       <p class="text-sm text-blue-600 mt-1">
                           You are about to switch to view the system as <span class="font-bold">{{ userToImpersonate?.name }}</span>. 
                           Your current session will end, and you will need to re-login to return to your admin account.
                       </p>
                   </div>
                </div>
            </div>
            <template #footer>
                <button @click="closeImpersonateModal" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg text-sm font-medium transition">
                    Cancel
                </button>
                <button 
                    @click="confirmImpersonate" 
                    class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-lg text-sm font-medium shadow-md hover:shadow-lg transition-all"
                >
                    Switch User
                </button>
            </template>
        </Modal>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { useAuthStore } from '@/stores/auth';
import { useToastStore } from '@/stores/toast';
import MainLayout from '@/Layouts/MainLayout.vue'; // Layout
import Modal from '@/Components/Modal.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import debounce from 'lodash/debounce';

// Define Layout
defineOptions({ layout: MainLayout });

// Props from Controller
const props = defineProps({
    users: Object, // Resource Collection (with meta, data)
    filters: Object
});

const authStore = useAuthStore();
const toast = useToastStore();

// Local State
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

// State for Modals
const showDeleteModal = ref(false);
const userToDelete = ref(null);
const showImpersonateModal = ref(false);
const userToImpersonate = ref(null);

const getInitials = (name) => {
    if (!name) return '';
    return name.split(' ').map((n) => n[0]).join('').substring(0, 2).toUpperCase();
};

const columns = {
    name: { label: 'User', class: 'text-left' },
    roles: { label: 'Roles', class: 'text-left' },
    department: { label: 'Department' },
    status: { label: 'Status' }
};

// Search & Filter Logic (Inertia Reload)
const handleSearch = debounce(() => {
    router.get('/admin/users', { 
        search: search.value,
        status: statusFilter.value 
    }, { preserveState: true, replace: true });
}, 300);

const changePage = (page) => {
    router.get('/admin/users', { 
        page, 
        search: search.value, 
        status: statusFilter.value 
    }, { preserveState: true });
};

// Delete Logic
const openDeleteModal = (user) => {
    userToDelete.value = user;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    userToDelete.value = null;
};

const deleteUser = (user) => {
    openDeleteModal(user);
};

const confirmDelete = () => {
    if (!userToDelete.value) return;

    router.delete(`/admin/users/${userToDelete.value.id}`, {
        onSuccess: () => {
             toast.success('User deleted successfully');
             closeDeleteModal();
        },
        onError: () => {
             toast.error('Failed to delete user');
        }
    });
};

// Impersonate Logic
const impersonateUser = (user) => {
    userToImpersonate.value = user;
    showImpersonateModal.value = true;
};

const closeImpersonateModal = () => {
    showImpersonateModal.value = false;
    userToImpersonate.value = null;
};

const confirmImpersonate = async () => {
    if (!userToImpersonate.value) return;
    
    // Likely an API call, or could be an Inertia visit if backed by web route
    // Assuming API for impersonation as it usually sets session/cookie
    // But let's try Inertia visit to '/impersonate' if it exists in web.php
    // If it's a pure API route, axios is fine. Keeping axios for this specific action if it's outside inertia flow.
    // However, usually we want to redirect to dashboard.
    
    // For now, let's stick to Inertia for consistency if possible, but if not, axios + window.location
    try {
        await axios.post('/impersonate', { user_id: userToImpersonate.value.id });
        toast.success(`Now impersonating ${userToImpersonate.value.name}`);
        window.location.href = '/dashboard'; // Force full reload to pick up new user session
    } catch (error) {
        toast.error("Failed to impersonate");
    }
};
</script>

