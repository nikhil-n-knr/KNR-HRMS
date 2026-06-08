<template>
  <Head title="Roles" />
  <div class="bg-[#f4f5fa]">
    <GradientHeroHeader
      kicker="Administration"
      title="Roles"
      subtitle="Define access levels, dashboards, and responsibility boundaries."
    >
      <template #right>
        <div class="flex flex-wrap items-center gap-3">
          <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[140px]">
            <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Total Roles</p>
            <p class="text-4xl font-extrabold text-white leading-none">{{ totalRoles }}</p>
          </div>
          <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[140px]">
            <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">System Roles</p>
            <p class="text-4xl font-extrabold text-white leading-none">{{ systemRoles }}</p>
          </div>
          <button
            v-if="authStore.can('user_management.role.create')"
            @click="createRole"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-white text-indigo-700 text-sm font-extrabold hover:bg-indigo-50 active:scale-[0.97] transition-all shadow-lg shadow-black/10"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span class="hidden sm:inline">Create Role</span>
          </button>
          <Link
            href="/admin/roles/matrix"
            class="w-10 h-10 flex items-center justify-center rounded-2xl bg-white/10 border border-white/20 text-white/70 hover:bg-white/20 hover:text-white transition-all"
            title="Permission Matrix"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
            </svg>
          </Link>
        </div>
      </template>
    </GradientHeroHeader>

    <div class="mx-0 sm:mx-6 mt-5 pb-12">
      <div class="space-y-6">

    <!-- Role Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- Create New Card -->
      <div 
        v-if="authStore.can('user_management.role.create')"
        @click="createRole"
        class="group relative bg-white/30 hover:bg-white/50 border-2 border-dashed border-emerald-300 rounded-2xl flex flex-col items-center justify-center p-8 transition-all cursor-pointer"
      >
         <div class="h-12 w-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
         </div>
         <h3 class="text-lg font-semibold text-emerald-800">Create New Role</h3>
         <p class="text-sm text-emerald-600 text-center mt-2">Custom access control</p>
         <button class="mt-4 text-emerald-700 text-sm font-medium hover:underline">Quick Create</button>
      </div>

      <!-- Role Card Loop -->
      <div v-for="role in roles.data" :key="role.id" class="bg-white/60 backdrop-blur-sm border border-white/50 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all flex flex-col">
          <div class="flex justify-between items-start mb-4">
             <div class="h-10 w-10 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
             </div>
             <span v-if="role.is_system" class="bg-gray-100 text-gray-600 text-xs font-semibold px-2 py-1 rounded">System</span>
          </div>
          
          <h3 class="text-lg font-bold text-gray-800">{{ role.name }}</h3>
          <p class="text-sm text-gray-500 mt-1 line-clamp-2 min-h-[40px]">{{ role.description || 'No description provided.' }}</p>
          
          <div class="mt-2 flex items-center gap-1.5">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
              <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">{{ role.dashboard || '/dashboard' }}</span>
          </div>
          
          <div class="mt-auto pt-6 border-t border-gray-100 flex items-center justify-between">
             <div class="flex items-center -space-x-2">
                <!-- Mock Avatars (would come from role.users relation ideally) -->
                <div class="h-8 w-8 rounded-full ring-2 ring-white bg-gray-200"></div>
                <div class="h-8 w-8 rounded-full ring-2 ring-white bg-gray-300"></div>
                <div class="h-8 w-8 rounded-full ring-2 ring-white bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-500">
                  +{{ role.users_count }}
                </div>
             </div>
             <div class="flex items-center gap-4">
                 <Link v-if="authStore.can('user_management.role.update')" :href="`/admin/roles/matrix?role=${role.id}`" class="text-emerald-600 hover:text-emerald-800 text-sm font-medium">Manage</Link>
                 
                 <div class="flex items-center gap-2">
                     <button v-if="authStore.can('user_management.role.update') && role.name !== 'Super Admin'" @click.prevent="openEditModal(role)" class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit Role">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                     </button>
                     <button v-if="authStore.can('user_management.role.delete') && !role.is_system && role.name !== 'Super Admin'" @click.prevent="confirmDelete(role)" class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Delete Role">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                         </svg>
                     </button>
                 </div>
             </div>
          </div>
      </div>
    </div>
    
    <!-- Create Role Modal -->
    <Modal :show="showCreateModal" title="Create New Role" @close="showCreateModal = false">
        <div class="space-y-4">
            <BaseInput
                v-model="form.name"
                label="Role Name"
                placeholder="e.g. HR Manager"
                :error="form.errors.name"
            />
             <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea
                    v-model="form.description" 
                    placeholder="Briefly describe this role's access level..."
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-all text-sm py-2.5 px-4 bg-white/50 backdrop-blur-sm hover:bg-white"
                    rows="3"
                ></textarea>
                <p v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</p>
            </div>
            
            <!-- Dashboard Assignment -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assign Dashboard</label>
                <select 
                    v-model="form.dashboard"
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-all text-sm py-2.5 px-4 bg-white/50 backdrop-blur-sm hover:bg-white"
                >
                    <option value="/dashboard">Employee Dashboard (Default)</option>
                    <option value="/admin/dashboard">Super Admin Dashboard</option>
                    <option value="/hr/dashboard">HR Dashboard</option>
                    <option value="/manager/dashboard">Manager Dashboard</option>
                    <option value="/projects/management-dashboard">Project Management Dashboard</option>
                </select>
                <p v-if="form.errors.dashboard" class="text-red-500 text-xs mt-1">{{ form.errors.dashboard }}</p>
            </div>
        </div>
        <template #footer>
            <button @click="showCreateModal = false" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg text-sm font-medium transition">Cancel</button>
            <button 
                @click="saveRole" 
                :disabled="form.processing"
                class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-lg text-sm font-medium shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all"
            >
                {{ form.processing ? 'Creating...' : 'Create Role' }}
            </button>
        </template>
    </Modal>

    <!-- Edit Role Modal -->
    <Modal :show="showEditModal" title="Edit Role" @close="showEditModal = false">
        <div class="space-y-4">
            <BaseInput
                v-model="editForm.name"
                label="Role Name"
                placeholder="e.g. HR Manager"
                :error="editForm.errors.name"
            />
             <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea
                    v-model="editForm.description" 
                    placeholder="Briefly describe this role's access level..."
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-all text-sm py-2.5 px-4 bg-white/50 backdrop-blur-sm hover:bg-white"
                    rows="3"
                ></textarea>
                <p v-if="editForm.errors.description" class="text-red-500 text-xs mt-1">{{ editForm.errors.description }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assign Dashboard</label>
                <select 
                    v-model="editForm.dashboard"
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-all text-sm py-2.5 px-4 bg-white/50 backdrop-blur-sm hover:bg-white"
                >
                    <option value="/dashboard">Employee Dashboard (Default)</option>
                    <option value="/admin/dashboard">Super Admin Dashboard</option>
                    <option value="/hr/dashboard">HR Dashboard</option>
                    <option value="/manager/dashboard">Manager Dashboard</option>
                    <option value="/projects/management-dashboard">Project Management Dashboard</option>
                </select>
                <p v-if="editForm.errors.dashboard" class="text-red-500 text-xs mt-1">{{ editForm.errors.dashboard }}</p>
            </div>
        </div>
        <template #footer>
            <button @click="showEditModal = false" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg text-sm font-medium transition">Cancel</button>
            <button 
                @click="updateRole" 
                :disabled="editForm.processing"
                class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-lg text-sm font-medium shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all"
            >
                {{ editForm.processing ? 'Saving...' : 'Save Changes' }}
            </button>
        </template>
    </Modal>

    <!-- Delete Role Modal -->
    <Modal :show="showDeleteModal" title="Delete Role" @close="showDeleteModal = false">
        <div class="p-4 text-center">
            <div class="w-16 h-16 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Are you sure?</h3>
            <p class="text-sm text-gray-500">This action cannot be undone. This will permanently delete the <span class="font-bold text-gray-800">{{ roleToDelete?.name }}</span> role and remove its associated permissions.</p>
        </div>
        <template #footer>
            <button @click="showDeleteModal = false" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg text-sm font-medium transition">Cancel</button>
            <button 
                @click="deleteRole" 
                :disabled="deleteForm.processing"
                class="px-4 py-2 bg-rose-600 text-white rounded-lg text-sm font-medium shadow-md hover:bg-rose-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
            >
                {{ deleteForm.processing ? 'Deleting...' : 'Delete Role' }}
            </button>
        </template>
    </Modal>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router, Link, Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import { useToastStore } from '@/stores/toast';
import { useAuthStore } from '@/stores/auth';
import BaseInput from '@/Components/BaseInput.vue';
import GradientHeroHeader from '@/Components/UI/GradientHeroHeader.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    roles: Object // Changed from Array to Object to handle API resource wrapper { data: [...] }
});

const toast = useToastStore();
const authStore = useAuthStore();

const totalRoles = computed(() => (props.roles?.data?.length ?? 0));
const systemRoles = computed(() => (props.roles?.data ?? []).filter((r) => r?.is_system).length);

const showCreateModal = ref(false);

const form = useForm({
    name: '',
    description: '',
    dashboard: '/dashboard'
});

const createRole = () => {
    form.reset();
    form.clearErrors();
    showCreateModal.value = true;
};

const saveRole = () => {
    if (!form.name) return;

    form.post('/admin/roles', {
        onSuccess: () => {
            showCreateModal.value = false;
            toast.success('Role created successfully.');
        },
        onError: () => {
            toast.error('Failed to create role.');
        }
    });
};

const showEditModal = ref(false);
const showDeleteModal = ref(false);
const roleToDelete = ref(null);
const editingRoleId = ref(null);

const editForm = useForm({
    name: '',
    description: '',
    dashboard: '/dashboard'
});

const deleteForm = useForm({});

const openEditModal = (role) => {
    editingRoleId.value = role.id;
    editForm.name = role.name;
    editForm.description = role.description || '';
    editForm.dashboard = role.dashboard || '/dashboard';
    showEditModal.value = true;
};

const updateRole = () => {
    editForm.put(`/admin/roles/${editingRoleId.value}`, {
        onSuccess: () => {
            showEditModal.value = false;
            toast.success('Role updated successfully.');
        },
        onError: () => {
             toast.error('Failed to update role.');
        }
    });
};

const confirmDelete = (role) => {
    roleToDelete.value = role;
    showDeleteModal.value = true;
};

const deleteRole = () => {
    deleteForm.delete(`/admin/roles/${roleToDelete.value.id}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
            toast.success('Role deleted successfully.');
        },
        onError: () => {
             toast.error('Failed to delete role.');
        }
    });
};
</script>
