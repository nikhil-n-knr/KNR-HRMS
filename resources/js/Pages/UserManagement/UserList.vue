<template>
  <Head title="User Management" />
  <div class="bg-[#f4f5fa]">
    <GradientHeroHeader
      kicker="Administration"
      title="User Management"
      subtitle="Manage system access, security, and permissions."
    >
      <template #right>
        <div class="flex flex-wrap items-center gap-3 shrink-0">
          <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[140px]">
            <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Total Users</p>
            <p class="text-4xl font-extrabold text-white leading-none">{{ totalUsers }}</p>
          </div>
          <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[140px]">
            <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Current Filter</p>
            <p class="text-3xl font-extrabold text-white leading-none capitalize">{{ statusFilter || 'All' }}</p>
          </div>
          <Link
            v-if="authStore.can('user_management.users.create')"
            href="/admin/users/create"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-white text-indigo-700 text-sm font-extrabold hover:bg-indigo-50 active:scale-[0.97] transition-all shadow-lg shadow-black/10"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span class="hidden sm:inline">Add User</span>
          </Link>
        </div>
      </template>
    </GradientHeroHeader>

    <div class="mx-0 sm:mx-6 mt-5 pb-12">
      <div class="space-y-6">

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
          <button class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-bold shadow-sm hover:bg-slate-50 transition">
            Export
          </button>
          <select
            v-model="statusFilter"
            @change="handleSearch"
            class="pl-3 pr-10 py-2 text-sm font-semibold border-slate-200 focus:outline-none focus:ring-indigo-500/20 focus:border-indigo-400 rounded-xl bg-white"
          >
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
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { router, Link, Head } from '@inertiajs/vue3';
import { useAuthStore } from '@/stores/auth';
import { useToastStore } from '@/stores/toast';
import MainLayout from '@/Layouts/MainLayout.vue'; // Layout
import Modal from '@/Components/Modal.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import GradientHeroHeader from '@/Components/UI/GradientHeroHeader.vue';
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

const totalUsers = computed(() => (props.users?.meta?.total ?? 0));

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

