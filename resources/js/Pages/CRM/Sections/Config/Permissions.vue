<template>
    <div class="flex flex-col h-[calc(100vh-120px)] overflow-hidden bg-gray-50/50 relative font-sans text-left">
        <!-- Floating Info Button -->
        <div class="absolute top-4 right-8 z-50 group no-print">
            <button class="w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center text-gray-400 hover:text-indigo-500 hover:border-indigo-200 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <i class="fas fa-info text-xs"></i>
            </button>
            <div class="absolute right-0 top-10 w-80 bg-gray-900 text-white text-sm p-6 rounded-[32px] shadow-2xl opacity-0 group-hover:opacity-100 transition-all pointer-events-none z-50 transform origin-top-right scale-95 group-hover:scale-100 border border-white/10 font-medium font-sans">
                <div class="font-black tracking-[0.2em] uppercase mb-3 text-indigo-400 border-b border-indigo-500/20 pb-3 text-left">Governance & Role Architecture</div>
                <p class="text-gray-300 leading-relaxed mb-4 text-left font-sans">The Governance Module orchestrates role-based access control (RBAC) across your global ecosystem. Define security tiers and permissions to maintain data integrity and operational oversight.</p>
                <div class="space-y-2 text-left">
                    <div class="flex items-center gap-3 text-left font-sans font-sans"><div class="w-2 h-2 rounded-full bg-indigo-500 shadow-lg shadow-indigo-500/50 text-left font-sans"></div> <span>Role-Based Access Mapping</span></div>
                    <div class="flex items-center gap-3 text-left font-sans font-sans"><div class="w-2 h-2 rounded-full bg-emerald-500 shadow-lg shadow-emerald-500/50 text-left font-sans"></div> <span>Granular Permission Scaffolding</span></div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex flex-col min-w-0">
            <!-- Global Header -->
            <div class="px-8 py-8 border-b border-gray-100 bg-white relative z-40 no-print text-left font-sans">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 text-left font-sans text-left font-sans">
                    <div class="text-left font-sans text-left font-sans text-left font-sans">
                        <h2 class="text-3xl font-black text-gray-900 tracking-tight text-left font-sans text-left font-sans">Governance Core</h2>
                        <div class="flex items-center mt-3 text-left font-sans text-left font-sans text-left font-sans">
                            <i class="fas fa-shield-halved text-indigo-500 mr-3 text-left font-sans text-left font-sans text-left font-sans"></i>
                            <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans text-left font-sans">Security Protocol: Locked</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-left font-sans text-left font-sans">
                         <div class="flex p-2 bg-indigo-50/50 rounded-2xl text-left font-sans text-left font-sans">
                            <button @click="view = 'roles'" 
                                    :class="['px-7 py-3 rounded-xl text-sm font-black uppercase tracking-widest transition-all text-left font-sans', view === 'roles' ? 'bg-white text-indigo-600 shadow-xl shadow-indigo-500/10' : 'text-gray-400 hover:text-indigo-400']">
                                Role Definitions
                            </button>
                            <button @click="view = 'users'" 
                                    :class="['px-7 py-3 rounded-xl text-sm font-black uppercase tracking-widest transition-all text-left font-sans', view === 'users' ? 'bg-white text-indigo-600 shadow-xl shadow-indigo-500/10' : 'text-gray-400 hover:text-indigo-400']">
                                User Access Mapping
                            </button>
                        </div>
                        <button @click="printView" class="w-12 h-12 bg-white border border-gray-200 text-gray-400 rounded-2xl flex items-center justify-center hover:bg-gray-50 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm group relative text-left font-sans">
                            <i class="fas fa-print text-sm text-left font-sans"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Management Surface -->
            <div class="flex-1 overflow-auto p-8 text-left font-sans text-left font-sans" id="print-area">
                <div v-if="view === 'roles'" class="space-y-10 animate-in fade-in slide-in-from-bottom-5 duration-700 text-left font-sans text-left font-sans">
                    <div class="flex gap-4 p-2 bg-white rounded-3xl border border-gray-100 w-fit text-left font-sans text-left font-sans shadow-inner">
                        <button v-for="role in roles" :key="role.id"
                                @click="activeRole = role"
                                :class="['px-7 py-3 rounded-xl text-sm font-black uppercase tracking-widest transition-all text-left font-sans text-left font-sans', activeRole?.id === role.id ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-100' : 'text-gray-400 hover:text-indigo-400']">
                            {{ role.name }}
                        </button>
                    </div>

                    <div v-if="activeRole" class="grid grid-cols-1 lg:grid-cols-4 gap-10 text-left font-sans text-left font-sans">
                        <!-- Role Strategic Info -->
                        <div class="lg:col-span-1 text-left font-sans text-left font-sans">
                            <div class="bg-indigo-900 p-10 rounded-[50px] shadow-2xl relative overflow-hidden group border-l-8 border-l-indigo-400 text-left font-sans text-left font-sans">
                                <div class="absolute -right-20 -top-20 w-80 h-80 bg-white/10 rounded-full blur-3xl group-hover:bg-white/20 transition-all duration-700 text-left font-sans text-left font-sans"></div>
                                <div class="relative z-10 text-left font-sans text-left font-sans">
                                    <div class="w-16 h-16 bg-white/10 text-indigo-400 rounded-3xl flex items-center justify-center mb-8 shadow-inner text-left font-sans text-left font-sans">
                                        <i class="fas fa-fingerprint text-2xl text-left font-sans text-left font-sans"></i>
                                    </div>
                                    <h3 class="text-2xl font-black text-white mb-3 tracking-tight text-left font-sans text-left font-sans">{{ activeRole.name }}</h3>
                                    <p class="text-xs text-indigo-300 font-medium leading-relaxed mb-10 text-left font-sans text-left font-sans">
                                        {{ activeRole.description || 'Enterprise hierarchy definition for specialized data access and operational persistence.' }}
                                    </p>
                                    
                                    <div class="space-y-6 text-left font-sans text-left font-sans">
                                        <div class="flex justify-between items-center text-sm font-black uppercase tracking-widest text-indigo-200 text-left font-sans">
                                            <span>Security Clearance Rank</span>
                                            <span class="text-white text-left font-sans">Tier 4-Alpha</span>
                                        </div>
                                        <div class="h-2 bg-white/10 rounded-full overflow-hidden shadow-inner text-left font-sans">
                                            <div class="h-full bg-indigo-400 rounded-full w-4/5 shadow-lg shadow-indigo-500/50 text-left font-sans"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 bg-white p-8 rounded-[40px] border border-gray-100 shadow-sm text-left font-sans text-left font-sans">
                                <h4 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-6 flex items-center items-center text-left font-sans">
                                    <i class="fas fa-info-circle mr-2 text-indigo-500 text-left font-sans"></i>
                                    Operational Guidance
                                </h4>
                                <p class="text-base text-gray-500 leading-relaxed font-medium italic text-left font-sans text-left font-sans">
                                    Permission nodes are synchronized in real-time. Changes to "{{ activeRole.name }}" settings will propagate through the global identity cluster immediately.
                                </p>
                            </div>
                        </div>

                        <!-- Permissions Matrix Grid -->
                        <div class="lg:col-span-3 space-y-10 text-left font-sans text-left font-sans">
                             <div class="grid grid-cols-1 md:grid-cols-2 gap-10 text-left font-sans text-left font-sans">
                                 <div v-for="(perms, module) in permissionsByModule" :key="module" 
                                      class="bg-white p-10 rounded-[50px] shadow-sm border border-gray-100 hover:shadow-2xl transition-all border-t-8 border-t-indigo-100 text-left font-sans text-left font-sans">
                                     <div class="flex justify-between items-center mb-8 text-left font-sans text-left font-sans">
                                         <div class="flex items-center gap-4 text-left font-sans text-left font-sans">
                                             <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-left font-sans">
                                                 <i :class="[getModuleIcon(module), 'fas text-xs text-left font-sans']"></i>
                                             </div>
                                             <h4 class="text-lg font-black text-gray-900 tracking-tight text-left font-sans">{{ module.toUpperCase() }}</h4>
                                         </div>
                                         <div class="px-3 py-1.5 bg-gray-50 text-sm font-black text-gray-400 uppercase tracking-widest rounded-xl border border-gray-100 shadow-inner text-left font-sans">
                                             {{ perms.length }} NODES
                                         </div>
                                     </div>

                                     <div class="space-y-4 text-left font-sans text-left font-sans">
                                         <div v-for="permission in perms" :key="permission.id" 
                                              @click="togglePermission(activeRole, permission)"
                                              class="flex items-center justify-between p-5 rounded-[28px] border transition-all cursor-pointer group hover:bg-indigo-50/50 text-left font-sans"
                                              :class="hasPermission(activeRole, permission) ? 'bg-indigo-50/50 border-indigo-200' : 'bg-gray-50/50 border-gray-100'">
                                             <div class="text-left font-sans text-left font-sans">
                                                 <span class="text-sm font-black uppercase tracking-widest block text-left font-sans" :class="hasPermission(activeRole, permission) ? 'text-indigo-900' : 'text-gray-400'">{{ permission.name.replace(/_/g, ' ') }}</span>
                                             </div>
                                             <div :class="['w-14 h-7 rounded-full transition-all relative p-1 shadow-inner text-left font-sans', hasPermission(activeRole, permission) ? 'bg-indigo-600' : 'bg-gray-200']">
                                                <div :class="['w-5 h-5 bg-white rounded-full shadow-md transition-all', hasPermission(activeRole, permission) ? 'translate-x-[28px]' : 'translate-x-0']"></div>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                        </div>
                    </div>
                </div>

                <!-- User Mapping View -->
                <div v-if="view === 'users'" class="animate-in fade-in slide-in-from-bottom-5 duration-700 text-left font-sans text-left font-sans">
                     <div class="bg-white rounded-[50px] shadow-sm border border-gray-100 overflow-hidden text-left font-sans text-left font-sans">
                         <div class="px-12 py-10 border-b border-gray-50 bg-gray-50/20 flex justify-between items-center text-left font-sans text-left font-sans">
                             <div class="text-left font-sans text-left font-sans">
                                 <h3 class="text-2xl font-black text-gray-900 tracking-tight text-left font-sans text-left font-sans">Identity Ledger</h3>
                                 <p class="text-sm font-black text-gray-400 uppercase tracking-widest mt-2 text-left font-sans text-left font-sans">Global Stakeholders & Access Levels</p>
                             </div>
                             <div class="relative w-80 group text-left font-sans text-left font-sans">
                                <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-indigo-500 transition-colors text-left font-sans text-left font-sans"></i>
                                <input v-model="userSearch" type="text" placeholder="FILTER IDENTITIES..." class="w-full pl-12 pr-6 py-4 bg-white border-none rounded-[24px] text-sm font-black uppercase focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder-gray-400 shadow-inner text-left font-sans text-left font-sans">
                            </div>
                         </div>

                         <table class="min-w-full divide-y divide-gray-100 text-left font-sans text-left font-sans">
                            <thead class="bg-white text-left font-sans text-left font-sans">
                                <tr>
                                    <th class="px-12 py-8 text-left text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans">Subject Persona</th>
                                    <th class="px-12 py-8 text-left text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans">Assigned Security Tiers</th>
                                    <th class="px-12 py-8 text-right text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-50 text-left font-sans text-left font-sans">
                                <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-indigo-50/10 transition-all group border-l-4 border-l-transparent hover:border-l-indigo-600 text-left font-sans text-left font-sans">
                                    <td class="px-12 py-10 text-left font-sans text-left font-sans">
                                        <div class="flex items-center text-left font-sans text-left font-sans">
                                            <div class="w-14 h-14 bg-indigo-900 rounded-[24px] flex items-center justify-center text-white shadow-xl text-lg font-black mr-6 text-left font-sans text-left font-sans">
                                                {{ user.name.charAt(0) }}
                                            </div>
                                            <div class="text-left font-sans text-left font-sans">
                                                <div class="text-base font-black text-gray-900 text-left font-sans text-left font-sans">{{ user.name }}</div>
                                                <div class="text-sm font-bold text-gray-400 uppercase tracking-widest mt-1 text-left font-sans text-left font-sans">{{ user.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-12 py-10 text-left font-sans text-left font-sans">
                                        <div class="flex flex-wrap gap-2 text-left font-sans text-left font-sans">
                                            <span v-for="role in user.roles" :key="role.id" 
                                                  class="px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl text-sm font-black uppercase tracking-widest shadow-inner border border-indigo-100 text-left font-sans text-left font-sans">
                                                {{ role.name.toUpperCase() }}
                                            </span>
                                            <span v-if="!user.roles?.length" class="text-sm font-black text-gray-300 uppercase tracking-widest text-left font-sans text-left font-sans italic opacity-40">Unclassified Identity</span>
                                        </div>
                                    </td>
                                    <td class="px-12 py-10 text-right text-left font-sans text-left font-sans">
                                        <button @click="manageUserRoles(user)" class="w-12 h-12 rounded-2xl bg-white border border-gray-100 text-gray-400 hover:text-indigo-600 hover:border-indigo-100 flex items-center justify-center transition-all shadow-sm active:scale-95 mx-auto text-left font-sans text-left font-sans text-left font-sans text-left font-sans">
                                            <i class="fas fa-fingerprint text-sm text-left font-sans text-left font-sans text-left font-sans"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                         </table>
                         <div v-if="filteredUsers.length === 0" class="flex flex-col items-center justify-center py-40 text-gray-300 text-left font-sans text-left font-sans">
                             <div class="w-24 h-24 bg-white rounded-[40px] shadow-sm flex items-center justify-center mb-8 border border-gray-100 text-left font-sans text-left font-sans">
                                 <i class="fas fa-users-slash text-3xl opacity-20 text-left font-sans text-left font-sans"></i>
                             </div>
                             <p class="text-sm font-black text-gray-400 uppercase tracking-widest text-left font-sans text-left font-sans">No persona definitions match current indexing criteria.</p>
                         </div>
                     </div>
                </div>
            </div>
        </div>

        <!-- IDENTITY MAPPING MODAL -->
        <div v-if="showUserModal" @click.self="showUserModal = false" class="fixed inset-0 z-[100] bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4 text-left font-sans text-left font-sans">
            <div class="bg-white rounded-[50px] shadow-2xl max-w-lg w-full overflow-hidden border border-white text-left font-sans animate-in zoom-in-95 duration-300 text-left font-sans">
                <div class="bg-gray-50/50 px-12 py-10 border-b border-gray-100 flex justify-between items-center text-left font-sans text-left font-sans">
                    <div class="text-left font-sans text-left font-sans text-left font-sans">
                        <h3 class="text-2xl font-black text-gray-900 tracking-tight text-left font-sans text-left font-sans">Security Tier Mapping</h3>
                        <p class="text-sm font-black text-indigo-600 uppercase tracking-[0.2em] mt-2 text-left font-sans text-left font-sans">MAPPING: {{ activeUser?.name.toUpperCase() }}</p>
                    </div>
                     <button @click="showUserModal = false" class="w-12 h-12 rounded-2xl bg-white border border-gray-100 text-gray-400 hover:text-gray-900 flex items-center justify-center shadow-sm transition-all hover:rotate-90 text-left font-sans text-left font-sans">
                        <i class="fas fa-times text-left font-sans text-left font-sans"></i>
                    </button>
                </div>
                <div class="p-12 space-y-8 text-left font-sans text-left font-sans">
                    <div class="space-y-4 text-left font-sans text-left font-sans">
                         <label class="block text-sm font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans text-left font-sans">Select Active Security Tiers</label>
                         <div class="grid grid-cols-1 gap-4 text-left font-sans text-left font-sans">
                            <div v-for="role in roles" :key="role.id" 
                                 @click="toggleUserRole(role)"
                                 class="flex items-center justify-between p-6 rounded-[32px] border transition-all cursor-pointer group hover:shadow-xl text-left font-sans"
                                 :class="userHasRole(role) ? 'bg-indigo-600 text-white border-indigo-500 shadow-2xl shadow-indigo-500/20' : 'bg-gray-50 border-gray-100 text-gray-600'">
                                <span class="text-base font-black uppercase tracking-widest text-left font-sans">{{ role.name }}</span>
                                <i :class="['fas text-sm text-left font-sans text-left font-sans', userHasRole(role) ? 'fa-check-circle text-white' : 'fa-circle-notch text-gray-200 opacity-0 group-hover:opacity-100']"></i>
                            </div>
                         </div>
                    </div>
                </div>
                <div class="p-12 border-t border-gray-100 text-left font-sans text-left font-sans">
                     <button @click="updateUserRoles" class="w-full bg-gray-900 text-white py-5 rounded-[28px] font-black text-base tracking-widest uppercase hover:bg-indigo-600 transition-all shadow-xl active:scale-95 text-left font-sans text-left font-sans">
                        COMMIT IDENTITY SYNC
                     </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    roles: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    permissions: { type: Array, default: () => [] },
});

const view = ref('roles'); // roles, users
const activeRole = ref(props.roles[0] || null);
const activeUser = ref(null);
const showUserModal = ref(false);
const userSearch = ref('');
const userRoles = ref([]);

const permissionsByModule = computed(() => {
    const grouped = {};
    (props.permissions || []).forEach(p => {
        const module = p.module || 'standard';
        if (!grouped[module]) grouped[module] = [];
        grouped[module].push(p);
    });
    return grouped;
});

const filteredUsers = computed(() => {
    let list = props.users || [];
    if (userSearch.value) {
        const term = userSearch.value.toLowerCase();
        list = list.filter(u => u.name?.toLowerCase().includes(term) || u.email?.toLowerCase().includes(term));
    }
    return list;
});

const getModuleIcon = (module) => {
    const icons = {
        crm: 'fa-briefcase',
        marketing: 'fa-bullhorn',
        hr: 'fa-users',
        finance: 'fa-chart-pie',
        system: 'fa-microchip',
        reports: 'fa-chart-line'
    };
    return icons[module.toLowerCase()] || 'fa-box';
};

const hasPermission = (role, permission) => {
    return role?.permissions?.some(p => p.id === permission.id);
};

const togglePermission = async (role, permission) => {
    try {
        await axios.post(route('crm.permissions.toggle', role.id), {
            permission_id: permission.id
        });
        // Optimistic / Simple refresh
        router.reload({ preserveScroll: true });
    } catch (e) {
        alert('Security Fault: Failed to synchronize protocol.');
    }
};

const manageUserRoles = (user) => {
    activeUser.value = user;
    userRoles.value = (user.roles || []).map(r => r.id);
    showUserModal.value = true;
};

const userHasRole = (role) => userRoles.value.includes(role.id);

const toggleUserRole = (role) => {
    const idx = userRoles.value.indexOf(role.id);
    if (idx > -1) userRoles.value.splice(idx, 1);
    else userRoles.value.push(role.id);
};

const updateUserRoles = async () => {
    try {
        await axios.post(route('crm.users.roles.update', activeUser.value.id), {
            role_ids: userRoles.value
        });
        showUserModal.value = false;
        router.reload({ preserveScroll: true });
    } catch (e) {
        alert('Security Fault: Failed to synchronize user persona.');
    }
};

const printView = () => window.print();
</script>

<style scoped>
/* Custom Hide Scrollbar */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.05); border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.1); }

@media print {
    #print-area { padding: 0 !important; }
    .no-print { display: none !important; }
}
</style>
