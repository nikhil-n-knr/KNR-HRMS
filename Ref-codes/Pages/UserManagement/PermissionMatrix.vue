<template>
  <div class="space-y-6 pb-20">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white/40 backdrop-blur-md p-6 rounded-3xl border border-white/50 shadow-sm">
      <div>
        <h1 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-emerald-800 to-teal-700">
          Permission Matrix
        </h1>
        <p class="text-emerald-900/70 text-sm mt-1 font-medium">
            Granular access control for <span class="text-emerald-700 font-bold">{{ selectedRoleName || 'Selected Role' }}</span>
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
         <!-- Role Selector -->
         <div class="relative group">
             <div class="absolute -inset-0.5 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-xl opacity-30 group-hover:opacity-100 transition duration-1000 group-hover:duration-200 blur"></div>
             <select 
                v-model="selectedRoleId" 
                class="relative w-full md:w-64 rounded-xl border-none ring-1 ring-emerald-900/10 bg-white shadow-xl focus:ring-2 focus:ring-emerald-500 py-2.5 pl-4 pr-10 text-sm font-semibold text-gray-700 transition"
             >
                 <option :value="null" disabled>Select Role to Configure</option>
                 <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
             </select>
         </div>

         <!-- Dashboard Hub Selector -->
         <div class="relative group" v-if="selectedRoleId">
             <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-xl opacity-30 group-hover:opacity-100 transition duration-1000 group-hover:duration-200 blur"></div>
             <div class="relative flex items-center bg-white rounded-xl shadow-xl ring-1 ring-slate-900/10 h-full overflow-hidden">
                <div class="pl-4 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </div>
                <select 
                    v-model="selectedDashboard" 
                    class="border-none focus:ring-0 py-2.5 pl-2 pr-10 text-sm font-black text-slate-700 transition w-full"
                >
                    <option :value="null">AUTO-DETECT (SYSTEM BEST)</option>
                    <option value="/dashboard">NATIVE: EMPLOYEE HUB</option>
                    <option value="/admin/dashboard">NATIVE: ADMIN COMMAND</option>
                    <option value="/hr/dashboard">NATIVE: TALENT HUB</option>
                    <option value="/manager/dashboard">NATIVE: SQUAD HUB</option>
                    <option value="/projects/dashboard">COMPLEX: PROJECT PULSE</option>
                    <option value="/projects/management-dashboard">NATIVE: PROJECT MANAGEMENT HUB</option>
                </select>
             </div>
         </div>

         <!-- Save Button -->
         <button 
           @click="savePermissions"
           :disabled="!selectedRoleId || saving"
           class="relative overflow-hidden px-8 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl font-bold shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-50 disabled:cursor-not-allowed group"
         >
           <span class="relative z-10 flex items-center gap-2">
               <svg v-if="saving" class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                   <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                   <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
               </svg>
               <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
               </svg>
               {{ saving ? 'Saving Access...' : 'Save Configuration' }}
           </span>
         </button>
      </div>
    </div>

    <!-- Matrix Container -->
    <div v-if="selectedRoleId && structure.length" class="space-y-8 animate-fade-in-up">
        
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
        <div v-for="module in structure" :key="module.key" class="bg-white/70 backdrop-blur-xl border border-white/60 rounded-3xl shadow-sm overflow-hidden transition-all hover:shadow-md">
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
                    v-for="sub in module.submodules" 
                    :key="sub.key" 
                    class="grid grid-cols-1 lg:grid-cols-12 gap-4 px-6 py-5 hover:bg-emerald-50/30 transition-colors items-center"
                 >
                    <!-- Sub-module Name & Row Toggle -->
                    <div class="lg:col-span-3">
                        <div class="flex items-center gap-3 cursor-pointer" @click="toggleRow(sub.db_module, sub.key, sub.actions, !isRowFullySelected(sub.db_module, sub.key, sub.actions))">
                            <div class="h-2 w-2 rounded-full bg-emerald-300"></div>
                            <span class="font-semibold text-gray-700">{{ sub.name }}</span>
                        </div>
                        <button 
                            @click.stop="toggleRow(sub.db_module, sub.key, sub.actions, !isRowFullySelected(sub.db_module, sub.key, sub.actions))"
                            class="text-sm uppercase tracking-wider font-bold mt-1 ml-5 text-gray-400 hover:text-emerald-600 transition"
                        >
                            {{ isRowFullySelected(sub.db_module, sub.key, sub.actions) ? 'Clear All' : 'Select All' }}
                        </button>
                    </div>

                    <!-- Permissions Grid -->
                    <div class="lg:col-span-9">
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:flex lg:flex-wrap gap-2">
                            <label 
                                v-for="action in sub.actions" 
                                :key="action" 
                                class="relative flex items-center group cursor-pointer"
                            >
                                <input 
                                    type="checkbox"
                                    class="peer sr-only"
                                    :checked="hasPermission(sub.db_module, sub.key, action)"
                                    @change="togglePermission(sub.db_module, sub.key, action)"
                                >
                                <div class="w-full text-center lg:w-auto px-3 py-2 rounded-xl border border-gray-200 bg-white text-gray-500 text-base font-bold transition-all peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:border-emerald-500 peer-checked:shadow-md group-hover:border-emerald-300 select-none min-h-[40px] flex items-center justify-center">
                                    {{ formatAction(action) }}
                                </div>
                            </label>
                        </div>
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
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { useToastStore } from '@/stores/toast';

defineOptions({ layout: MainLayout });

const props = defineProps({
    roles: Array,
    structure: Array, // [{ name, key, submodules: [{ name, key, actions: [...] }] }]
});

const toast = useToastStore();
const selectedRoleId = ref(null);
const selectedDashboard = ref(null);
const currentPermissions = ref([]); // Stores selected permission keys ['modules.users.create', ...]
const permissionScopes = ref({});   // Stores scope for each key
const defaultScope = ref('tenant');
const saving = ref(false);

const selectedRoleName = computed(() => {
    const role = props.roles.find(r => r.id === selectedRoleId.value);
    return role ? role.name : '';
});

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    const roleId = params.get('role');
    if (roleId) {
        selectedRoleId.value = parseInt(roleId);
    }
});

// -- Logic --

// Watch for role change -> Load initial permissions
watch(selectedRoleId, (newId) => {
    if (!newId) return;
    const role = props.roles.find(r => r.id === newId);
    if (role && role.permissions) {
        // Role permissions is a Map { "key": ID } from Controller
        // We need keys.
        currentPermissions.value = Object.keys(role.permissions);
        selectedDashboard.value = role.dashboard; // Might be null (Auto-detect)
        
        // Scope not passed in simple map currently. 
        // If we need scope, we might need to adjust controller to send { id, pivot_scope }.
        // For now default to tenant or use what we have (preserving if we don't have it is tricky).
        // Let's assume default for this version.
        // Or if we want to be persistent, we need richer data.
    } else {
        currentPermissions.value = [];
        selectedDashboard.value = null;
    }
});

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

const formatAction = (slug) => {
    return slug.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase());
};

// -- Bulk Actions --

const toggleRow = (modKey, subKey, actions, shouldSelect) => {
     actions.forEach(action => {
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

const isRowFullySelected = (modKey, subKey, actions) => {
    return actions.every(action => currentPermissions.value.includes(`${modKey}.${subKey}.${action}`));
};

const toggleModule = (module, shouldSelect) => {
    module.submodules.forEach(sub => {
        toggleRow(sub.db_module, sub.key, sub.actions, shouldSelect);
    });
};

const isModuleFullySelected = (module) => {
    return module.submodules.every(sub => isRowFullySelected(sub.db_module, sub.key, sub.actions));
};

const toggleGlobal = (shouldSelect) => {
    if (!shouldSelect) {
        currentPermissions.value = [];
        permissionScopes.value = {};
        return;
    }
    const all = [];
    props.structure.forEach(mod => {
        mod.submodules.forEach(sub => {
            sub.actions.forEach(action => {
                const key = `${sub.db_module}.${sub.key}.${action}`;
                all.push(key);
                permissionScopes.value[key] = defaultScope.value;
            });
        });
    });
    currentPermissions.value = [...new Set([...currentPermissions.value, ...all])];
};

const savePermissions = () => {
    if (!selectedRoleId.value) {
        toast.error('Please select a role first.');
        return;
    }
    
    // Transform to backend expected format
    const payload = currentPermissions.value.map(key => ({
        name: key, // 'module.submodule.action'
        data_scope: permissionScopes.value[key] || 'tenant'
    }));

    router.put(route('admin.roles.update', selectedRoleId.value), {
        permissions_matrix: payload,
        dashboard: selectedDashboard.value
    }, {
        onStart: () => saving.value = true,
        onFinish: () => saving.value = false,
        onSuccess: () => toast.success('Permissions updated successfully!'),
        onError: () => toast.error('Failed to update permissions.')
    });
};
</script>

<style scoped>
.animate-fade-in-up {
  animation: fadeInUp 0.5s ease-out forwards;
}

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-pulse-slow {
    animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
