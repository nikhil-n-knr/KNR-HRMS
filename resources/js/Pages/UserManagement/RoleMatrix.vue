<template>
  <Head title="Permission Matrix" />
  <div class="bg-[#f4f5fa]">
    <GradientHeroHeader
      kicker="Administration"
      title="Permission Matrix"
      :subtitle="selectedRoleName ? `Granular access control for ${selectedRoleName}.` : 'Granular access control for the selected role.'"
      :allow-overflow="true"
    >
      <template #right>
        <div class="flex flex-wrap items-center gap-3">
          <div class="w-full sm:w-[380px]">
            <Combobox
              v-model="selectedRoleId"
              :items="roles"
              label-key="name"
              value-key="id"
              placeholder="Search role…"
            />
          </div>

          <button
            @click="savePermissions"
            :disabled="!selectedRoleId || saving"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-white text-indigo-700 text-sm font-extrabold
                   hover:bg-indigo-50 active:scale-[0.97] transition-all shadow-lg shadow-black/10 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg v-if="saving" class="animate-spin h-4 w-4" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
            </svg>
            <span class="hidden sm:inline">{{ saving ? 'Saving…' : 'Save' }}</span>
          </button>
        </div>
      </template>
    </GradientHeroHeader>

    <div class="mx-0 sm:mx-6 mt-5 pb-12">
      <div class="space-y-6 pb-20">

    <!-- Matrix Container -->
    <div v-if="selectedRoleId && modules.length" class="space-y-8 animate-fade-in-up">
        
    <!-- Global Actions Toolbar -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 px-2">
            <!-- Data Scope Selector (Global Default) -->
            <div class="flex items-center gap-2 bg-white/50 px-3 py-1.5 rounded-xl border border-emerald-100">
                <span class="text-xs font-bold text-emerald-800 uppercase tracking-wide">Default Scope To:</span>
                <select v-model="defaultScope" class="text-xs border-none bg-transparent font-medium text-gray-700 focus:ring-0 cursor-pointer">
                    <option value="self">Self Only</option>
                    <option value="team">My Team</option>
                    <option value="department">My Department</option>
                    <option value="tenant">Entire Organization</option>
                </select>
            </div>

            <div class="flex gap-3">
                <button @click="toggleGlobal(true)" class="text-xs font-bold text-emerald-700 hover:text-emerald-500 bg-white/50 hover:bg-white px-3 py-1.5 rounded-lg border border-emerald-100 transition shadow-sm">
                    Select Everything
                </button>
                 <button @click="toggleGlobal(false)" class="text-xs font-bold text-red-600 hover:text-red-500 bg-white/50 hover:bg-white px-3 py-1.5 rounded-lg border border-red-100 transition shadow-sm">
                    Clear All
                </button>
            </div>
        </div>

        <!-- Modules Loop -->
        <div v-for="module in modules" :key="module.id" class="bg-white/70 backdrop-blur-xl border border-white/60 rounded-3xl shadow-sm overflow-hidden transition-all hover:shadow-md">
            <!-- Module Header -->
            <div class="px-6 py-4 bg-gradient-to-r from-emerald-50/80 to-teal-50/80 border-b border-emerald-100 flex justify-between items-center group cursor-pointer" @click="toggleModule(module, !isModuleFullySelected(module))">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white rounded-lg shadow-sm text-emerald-600 ring-1 ring-emerald-100">
                        <!-- Icon Placeholder -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">{{ module.name }}</h3>
                        <p class="text-xs text-emerald-600/70 font-medium tracking-wide uppercase">Main Module</p>
                    </div>
                </div>
                
                <!-- Bulk Module Toggle -->
                <div class="flex items-center gap-2">
                     <span class="text-xs font-semibold text-gray-500 group-hover:text-emerald-600 transition">
                        {{ isModuleFullySelected(module) ? 'Deselect Module' : 'Select Module' }}
                     </span>
                     <div 
                        class="w-12 h-6 rounded-full transition-colors relative"
                        :class="isModuleFullySelected(module) ? 'bg-emerald-500' : 'bg-gray-300'"
                     >
                        <div 
                           class="absolute top-1 left-1 w-4 h-4 rounded-full bg-white shadow-sm transition-transform"
                           :class="isModuleFullySelected(module) ? 'translate-x-6' : 'translate-x-0'"
                        ></div>
                     </div>
                </div>
            </div>

            <!-- Sub-modules List -->
            <div class="divide-y divide-emerald-50">
                 <div 
                    v-for="sub in module.sub_modules" 
                    :key="sub.id" 
                    class="grid grid-cols-1 lg:grid-cols-12 gap-4 px-6 py-5 hover:bg-emerald-50/30 transition-colors items-center"
                 >
                    <!-- Sub-module Name & Row Toggle -->
                    <div class="lg:col-span-3">
                        <div class="flex items-center gap-3 cursor-pointer" @click="toggleRow(module.key, sub.key, !isRowFullySelected(module.key, sub.key))">
                            <div class="h-2 w-2 rounded-full bg-emerald-300"></div>
                            <span class="font-semibold text-gray-700">{{ sub.name }}</span>
                        </div>
                        <button 
                            @click.stop="toggleRow(module.key, sub.key, !isRowFullySelected(module.key, sub.key))"
                            class="text-sm uppercase tracking-wider font-bold mt-1 ml-5 text-gray-400 hover:text-emerald-600 transition"
                        >
                            {{ isRowFullySelected(module.key, sub.key) ? 'Clear All' : 'Select All' }}
                        </button>
                    </div>

                    <!-- Permissions Grid -->
                    <div class="lg:col-span-9 flex flex-wrap gap-2">
                        <label 
                            v-for="action in availableActions" 
                            :key="action" 
                            class="relative flex items-center group cursor-pointer"
                        >
                            <input 
                                type="checkbox"
                                class="peer sr-only"
                                :checked="hasPermission(module.key, sub.key, action)"
                                @change="togglePermission(module.key, sub.key, action)"
                            >
                            <div 
                                class="px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-gray-500 text-xs font-semibold transition-all peer-checked:border-emerald-500 peer-checked:shadow-sm group-hover:border-emerald-300 select-none flex items-center gap-2"
                                :class="{'bg-emerald-50 peer-checked:bg-emerald-50': hasPermission(module.key, sub.key, action)}"
                            >
                                <span 
                                    v-if="hasPermission(module.key, sub.key, action)"
                                    @click.prevent="cycleScope(`${module.key}.${sub.key}.${action}`)"
                                    class="h-5 w-5 rounded flex items-center justify-center text-sm font-bold border cursor-pointer hover:scale-110 transition-transform"
                                    :class="getScopeColorClass(`${module.key}.${sub.key}.${action}`)"
                                    title="Click to cycle Scope (Self -> Team -> Dept -> Org)"
                                >
                                    {{ getScopeLabel(`${module.key}.${sub.key}.${action}`) }}
                                </span>
                                <span :class="{'text-emerald-700': hasPermission(module.key, sub.key, action)}">{{ formatAction(action) }}</span>
                            </div>
                        </label>
                    </div>
                 </div>
            </div>
        </div>
    </div>

    <!-- Empty State -->
    <div v-else class="flex flex-col items-center justify-center py-24 bg-white/40 backdrop-blur-md border-2 border-dashed border-gray-300/50 rounded-3xl animate-pulse-slow">
        <div class="h-20 w-20 bg-gray-100 rounded-full flex items-center justify-center mb-4 text-gray-300">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
             </svg>
        </div>
        <h3 class="text-xl font-bold text-gray-600">No Role Selected</h3>
        <p class="text-gray-400 mt-2">Please select a role from the dropdown above to start configuring permissions.</p>
    </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import GradientHeroHeader from '@/Components/UI/GradientHeroHeader.vue';
import Combobox from '@/Components/Combobox.vue';
import { useAuthStore } from '@/stores/auth';
import { useToastStore } from '@/stores/toast';

defineOptions({ layout: MainLayout });

const authStore = useAuthStore();
const toast = useToastStore();

// Data
const roles = ref([]);
const modules = ref([]);
const selectedRoleId = ref('');
const currentPermissions = ref([]); // Stores selected permission keys ['user.view', 'user.edit']
const permissionScopes = ref({});   // Stores scope for each key: {'user.view': 'tenant'}
const defaultScope = ref('tenant'); // Default for new selections
const saving = ref(false);

// Configuration
// We use a superset of actions. In a real dynamic system, these would come from the sub-module definition.
const availableActions = ['view', 'view_detail', 'create', 'update', 'delete', 'approve', 'export'];

// Derived State
const selectedRoleName = computed(() => {
    const role = roles.value.find(r => r.id === selectedRoleId.value);
    return role ? role.name : '';
});

// -- Logic --

const loadData = async () => {
    try {
        const [rData, mData] = await Promise.all([
            axios.get('/api/admin/roles'),
            axios.get('/api/admin/roles/matrix')
        ]);
        roles.value = rData.data.data;
        modules.value = mData.data.modules;
    } catch (e) {
        console.error("Failed to load matrix data", e);
    }
};

const hasPermission = (modKey, subKey, action) => {
    return currentPermissions.value.includes(`${modKey}.${subKey}.${action}`);
};

const togglePermission = (modKey, subKey, action) => {
    const key = `${modKey}.${subKey}.${action}`;
    if (currentPermissions.value.includes(key)) {
        currentPermissions.value = currentPermissions.value.filter(k => k !== key);
        delete permissionScopes.value[key];
    } else {
        currentPermissions.value.push(key);
        permissionScopes.value[key] = defaultScope.value;
    }
};

const SCOPES = ['self', 'team', 'department', 'tenant'];
const SCOPE_LABELS = { self: 'S', team: 'T', department: 'D', tenant: 'O' };
const SCOPE_COLORS = { self: 'gray', team: 'blue', department: 'orange', tenant: 'emerald' };

const cycleScope = (key) => {
    if (!permissionScopes.value[key]) return;
    const currentIdx = SCOPES.indexOf(permissionScopes.value[key]);
    const nextIdx = (currentIdx + 1) % SCOPES.length;
    permissionScopes.value[key] = SCOPES[nextIdx];
};

const getScopeLabel = (key) => SCOPE_LABELS[permissionScopes.value[key] || 'tenant'];
const getScopeColorClass = (key) => {
    const map = {
        self: 'bg-gray-100 text-gray-700 border-gray-200',
        team: 'bg-blue-100 text-blue-700 border-blue-200',
        department: 'bg-orange-100 text-orange-700 border-orange-200',
        tenant: 'bg-emerald-100 text-emerald-700 border-emerald-200'
    };
    return map[permissionScopes.value[key] || 'tenant'];
};

const formatAction = (slug) => {
    return slug.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase());
};

// -- Bulk Actions --

// 1. Toggle entire row (Su-module)
const toggleRow = (modKey, subKey, shouldSelect) => {
     availableActions.forEach(action => {
         const key = `${modKey}.${subKey}.${action}`;
         if (shouldSelect) {
             if (!currentPermissions.value.includes(key)) {
                 currentPermissions.value.push(key);
                 permissionScopes.value[key] = defaultScope.value;
             }
         } else {
             currentPermissions.value = currentPermissions.value.filter(k => k !== key);
             delete permissionScopes.value[key];
         }
     });
};

const isRowFullySelected = (modKey, subKey) => {
    // Check if ALL available actions are selected
    return availableActions.every(action => currentPermissions.value.includes(`${modKey}.${subKey}.${action}`));
};

// 2. Toggle entire Module
const toggleModule = (module, shouldSelect) => {
    module.sub_modules.forEach(sub => {
        toggleRow(module.key, sub.key, shouldSelect);
    });
};

const isModuleFullySelected = (module) => {
    return module.sub_modules.every(sub => isRowFullySelected(module.key, sub.key));
};

// 3. Global Toggle
const toggleGlobal = (shouldSelect) => {
    if (!shouldSelect) {
        currentPermissions.value = [];
        permissionScopes.value = {};
        return;
    }
    // Select everything
    const all = [];
    modules.value.forEach(mod => {
        mod.sub_modules.forEach(sub => {
            availableActions.forEach(action => {
                const key = `${mod.key}.${sub.key}.${action}`;
                all.push(key);
                permissionScopes.value[key] = defaultScope.value;
            });
        });
    });
    // Dedupe just in case, though pushing fresh is cleaner
    currentPermissions.value = [...new Set([...currentPermissions.value, ...all])];
};


// -- Loading & Saving --

watch(selectedRoleId, async (newId) => {
    if (!newId) return;
    try {
        const response = await axios.get(`/api/admin/roles/${newId}`);
        const roleData = response.data.data;
        
        currentPermissions.value = [];
        permissionScopes.value = {};
        
        if (roleData.permissions && Array.isArray(roleData.permissions)) {
             // Map objects to keys and scopes
             roleData.permissions.forEach(p => {
                 const key = `${p.module}.${p.submodule}.${p.action}`;
                 currentPermissions.value.push(key);
                 // Assuming the API returns the pivot data 'data_scope'
                 permissionScopes.value[key] = p.pivot?.data_scope || 'tenant';
             });
        }
    } catch (e) {
        console.error("Failed to fetch role permissions", e);
        currentPermissions.value = [];
    }
});

const savePermissions = async () => {
    if (!selectedRoleId.value) return;
    saving.value = true;
    try {
        // Transform to backend expected format
        // We'll send an array of { name: 'module.sub.action', scope: 'tenant' }
        const payload = currentPermissions.value.map(key => ({
            name: key,
            data_scope: permissionScopes.value[key] || 'tenant'
        }));

        await axios.put(`/api/admin/roles/${selectedRoleId.value}`, {
            permissions_matrix: payload
        });
        
        toast.success('Configuration saved successfully!');
    } catch (e) {
        toast.error("Failed to save.");
        console.error(e);
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    loadData();
});
</script>

<style scoped>
/* Custom Scrollbar for matrix if needed, though mostly relying on body scroll */
.animate-fade-in-up {
  animation: fadeInUp 0.5s ease-out forwards;
  opacity: 0;
  transform: translateY(20px);
}

@keyframes fadeInUp {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-pulse-slow {
    animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
