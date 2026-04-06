<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import UserSearchInput from '@/Components/Common/UserSearchInput.vue';
import Modal from '@/Components/Modal.vue';
import { useToastStore } from '@/stores/toast';

const toast = useToastStore();

const props = defineProps({
    teams: Array, 
    users: Array
});

const isModalOpen = ref(false);
const editingTeam = ref(null);

// Member Management
const showMembersModal = ref(false);
const activeTeamId = ref(null);
const usersToAdd = ref([]);
const dummySearch = ref(null);

const activeTeam = computed(() => {
    return props.teams.find(t => t.id === activeTeamId.value);
});

const form = useForm({
    id: null,
    name: '',
    manager_id: null,
    manager_label: '', // Added for UserSearchInput
    parent_team_id: null
});

const openCreate = () => {
    editingTeam.value = null;
    form.reset();
    isModalOpen.value = true;
};

const openEdit = (team) => {
    editingTeam.value = team;
    form.id = team.id;
    form.name = team.name;
    form.manager_id = team.manager_id;
    form.manager_label = team.manager ? team.manager.name : '';
    form.parent_team_id = team.parent_team_id;
    isModalOpen.value = true;
};

const openMembers = (team) => {
    activeTeamId.value = team.id;
    usersToAdd.value = [];
    showMembersModal.value = true;
};

const submit = () => {
    if (editingTeam.value && editingTeam.value.id) {
        form.put(route('admin.attendance.teams.update', { team: editingTeam.value.id }), {
            onSuccess: () => {
                isModalOpen.value = false;
                toast.success("Structural parameters updated");
            }
        });
    } else {
        form.post(route('admin.attendance.teams.store'), {
            onSuccess: () => {
                isModalOpen.value = false;
                toast.success("New operational node initialized");
            }
        });
    }
};

const handleUserSelect = (user) => {
    if (user && !usersToAdd.value.find(u => u.id === user.id)) {
        usersToAdd.value.push(user);
    }
    dummySearch.value = null;
};

const removeUserToAdd = (userId) => {
    usersToAdd.value = usersToAdd.value.filter(u => u.id !== userId);
};

const addMembers = () => {
    if (usersToAdd.value.length === 0) return;
    
    const count = usersToAdd.value.length; // Capture count before clearing
    router.put(route('admin.attendance.teams.members.bulk', { team: activeTeamId.value }), {
        id: activeTeamId.value,
        user_ids: usersToAdd.value.map(u => u.id)
    }, {
        onSuccess: () => {
            usersToAdd.value = [];
            showMembersModal.value = false;
            toast.success(`${count} agents deployed to node`);
        }
    });
};

const removeMember = (userId) => {
    router.delete(route('admin.attendance.teams.members.remove', { team: activeTeamId.value, user: userId }), {
        onBefore: () => confirm('Initiate agent recall from node?'),
        onSuccess: () => toast.success("Agent recalled successfully")
    });
};

const deleteTeam = (team) => {
    router.delete(route('admin.attendance.teams.destroy', { team: team.id }), {
        onBefore: () => confirm('Are you sure you want to dismantle this structural node? All child nodes and assignments will be detached.'),
        onSuccess: () => toast.success("Node dismantled successfully")
    });
};
</script>

<template>
    <div class="animate-fade-in pb-12 font-outfit">
        <!-- Structural Header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-6 bg-white/90 backdrop-blur-md p-4 rounded-2xl border border-slate-200 shadow-sm transition-all overflow-hidden">
            <div class="flex items-center gap-4 w-full md:w-auto">
                <div class="w-12 h-12 bg-slate-900 rounded-xl flex items-center justify-center text-white shadow-xl shadow-slate-200 shrink-0 group-hover:bg-emerald-600 transition-all">
                    <i class="fas fa-sitemap text-lg text-emerald-400 group-hover:rotate-12 transition-transform"></i>
                </div>
                <div>
                    <h2 class="text-sm md:text-lg font-black text-slate-800 tracking-tight leading-none uppercase">Team Architect</h2>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1.5 leading-none">Hierarchy Calibration & Deployment</p>
                </div>
            </div>
            <button @click="openCreate" class="w-full md:w-auto px-8 py-3.5 bg-slate-900 text-white font-black text-[10px] uppercase tracking-[0.2em] rounded-xl hover:bg-emerald-600 shadow-xl shadow-slate-200 transition-all active:scale-95 group">
                <span class="flex items-center justify-center gap-2">
                    <i class="fas fa-plus-circle text-emerald-400 group-hover:scale-110 transition-transform"></i>
                    Initialize Node
                </span>
            </button>
        </div>

        <!-- Node List (Modernized) -->
        <div class="grid grid-cols-1 gap-4 max-w-6xl mx-auto px-1 md:px-0">
            <div v-for="team in teams" :key="team.id" 
                class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm transition-all hover:border-emerald-500/30 flex flex-col lg:flex-row items-center gap-6 relative overflow-hidden"
                :class="{'md:ml-10 border-l-4 border-l-emerald-200': team.parent_team_id}"
            >
                <!-- Visual Identity -->
                <div class="flex flex-col sm:flex-row items-center gap-6 flex-1 w-full">
                    <div class="relative shrink-0">
                        <div class="w-14 h-14 rounded-2xl bg-slate-50 flex items-center justify-center text-2xl group-hover:bg-emerald-50 transition-all shadow-inner overflow-hidden border border-slate-100">
                             <template v-if="team.parent_team_id">
                                <i class="fas fa-network-wired text-emerald-200 group-hover:text-emerald-500 rotate-90 scale-75"></i>
                             </template>
                             <template v-else>
                                <i class="fas fa-crown text-amber-400 group-hover:text-amber-500 transition-colors"></i>
                             </template>
                        </div>
                        <div v-if="team.parent_team_id" class="hidden md:block absolute -left-6 top-1/2 -translate-y-1/2 text-emerald-300 font-black text-2xl">↳</div>
                    </div>
                    
                    <div class="space-y-3 flex-1 text-center sm:text-left min-w-0">
                        <h3 class="text-sm font-black text-slate-800 leading-tight group-hover:text-emerald-700 transition-colors uppercase tracking-tight truncate">{{ team.name }}</h3>
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5">
                            <!-- Manager Badge -->
                            <div v-if="team.manager" class="flex items-center gap-2 px-3 py-1.5 bg-slate-50 rounded-lg border border-slate-100">
                                <div class="w-5 h-5 rounded-full bg-slate-900 text-emerald-400 flex items-center justify-center text-[7px] font-black uppercase">
                                    {{ team.manager.name.charAt(0) }}
                                </div>
                                <span class="text-[9px] font-black text-slate-600 uppercase tracking-widest truncate max-w-[120px]">{{ team.manager.name }}</span>
                            </div>
                            <div v-else class="px-3 py-1.5 bg-slate-50 rounded-lg border border-slate-100 italic text-[8px] font-black text-slate-400 uppercase tracking-widest">
                                VACANT COMMAND
                            </div>

                            <!-- Parent Ref -->
                            <div v-if="team.parent_team_id" class="px-3 py-1.5 bg-slate-50 rounded-lg border border-slate-100 text-[8px] font-black text-slate-400 uppercase tracking-widest">
                                NODE_ID: {{ team.parent_team_id }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats & Intelligence -->
                <div class="flex items-center justify-between lg:justify-end gap-8 w-full lg:w-auto pt-4 lg:pt-0 border-t lg:border-t-0 border-slate-50">
                    <div class="text-left lg:text-center group-hover:scale-105 transition-transform px-2">
                        <div class="text-xl font-black text-slate-800 leading-none mb-1.5 tabular-nums">{{ team.members_count || 0 }}</div>
                        <div class="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] leading-none">Registered_Units</div>
                    </div>

                    <!-- Tactical Actions -->
                    <div class="flex gap-2">
                        <button @click="openMembers(team)" class="w-11 h-11 bg-slate-50 text-slate-600 rounded-xl flex items-center justify-center hover:bg-slate-900 hover:text-white hover:shadow-lg transition-all shadow-sm border border-slate-200 active:scale-90" title="Deployment Zone">
                            <i class="fas fa-users-cog text-[14px]"></i>
                        </button>
                        <button @click="openEdit(team)" class="w-11 h-11 bg-slate-50 text-slate-600 rounded-xl flex items-center justify-center hover:bg-emerald-600 hover:text-white hover:shadow-lg transition-all shadow-sm border border-slate-200 active:scale-90" title="Modify Node">
                            <i class="fas fa-edit text-[14px]"></i>
                        </button>
                        <button @click="deleteTeam(team)" class="w-11 h-11 bg-slate-50 text-slate-300 hover:bg-rose-600 hover:text-white hover:shadow-lg rounded-xl flex items-center justify-center transition-all shadow-sm border border-slate-200 active:scale-90" title="Dismantle Node">
                            <i class="fas fa-trash-alt text-[14px]"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div v-if="teams && teams.length === 0" class="p-12 text-center bg-white/40 rounded-2xl border-2 border-dashed border-slate-200">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-200">
                    <i class="fas fa-layer-group text-2xl"></i>
                </div>
                <h3 class="text-lg font-black text-slate-800 mb-1 uppercase">Structural Void Detected</h3>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Initial Root Hub Deployment Required</p>
            </div>
        </div>

        <!-- Node Architect Modal -->
        <Modal :show="isModalOpen" @close="isModalOpen = false" maxWidth="xl">
            <div class="bg-white p-8 font-outfit">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 bg-slate-900 text-emerald-400 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-project-diagram text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-800 tracking-tight leading-none uppercase">{{ editingTeam ? 'Modify Node' : 'Initialize Node' }}</h2>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1.5">Hierarchy Protocol Calibration</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Node Designation</label>
                        <input v-model="form.name" type="text" placeholder="e.g. ALPHA TACTICAL OPS" class="w-full h-12 rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 font-black text-sm bg-slate-50 shadow-inner px-5" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Superstructure</label>
                            <select v-model="form.parent_team_id" class="w-full h-12 rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 font-bold text-xs bg-slate-50 shadow-inner px-5">
                                <option :value="null">ROOT HUB</option>
                                <option v-for="t in teams" :key="t.id" :value="t.id">{{ t.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Commander (Manager)</label>
                            <UserSearchInput 
                                v-model="form.manager_id" 
                                :initial-label="form.manager_label" 
                                placeholder="Scan for Lead..." 
                                class="h-12" 
                            />
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 pt-6 mt-4 border-t border-slate-100">
                        <button @click="isModalOpen = false" class="px-6 py-3 text-slate-400 font-black text-[10px] uppercase tracking-widest hover:text-slate-600 transition-colors">Abort</button>
                        <button @click="submit" :disabled="form.processing" class="px-10 py-4 bg-slate-900 text-white font-black text-[10px] uppercase tracking-[0.2em] rounded-xl hover:bg-emerald-600 shadow-xl shadow-slate-200 transition-all transform active:scale-95 disabled:opacity-50">
                            {{ editingTeam ? 'Commit Update' : 'Initialize Node' }}
                        </button>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Deployment Zone Modal (Members) -->
        <Modal :show="showMembersModal" @close="showMembersModal = false" maxWidth="2xl">
            <div class="bg-white p-8 font-outfit flex flex-col max-h-[85vh]">
                <div class="flex justify-between items-start mb-8">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-slate-900 text-emerald-400 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-users text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-slate-800 tracking-tight leading-none uppercase">{{ activeTeam?.name }}</h2>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1.5">Unit Deployment Registry</p>
                        </div>
                    </div>
                    <button @click="showMembersModal = false" class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition-all">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
                
                <!-- Deployment Control -->
                <div class="bg-slate-50 p-5 rounded-2xl mb-6 border border-slate-100 shadow-inner">
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Unit Deployment Protocol</label>
                    <div class="space-y-4">
                        <UserSearchInput 
                            v-model="dummySearch"
                            @selected="handleUserSelect"
                            placeholder="Scan Employee Database..." 
                        />
                        
                        <!-- Staging Area (Tags) -->
                        <div v-if="usersToAdd.length > 0" class="flex flex-wrap gap-2">
                            <div v-for="u in usersToAdd" :key="u.id" class="animate-content-fade bg-white border border-slate-200 text-slate-700 px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest flex items-center gap-2 shadow-sm group">
                                {{ u.name }}
                                <button @click="removeUserToAdd(u.id)" class="text-slate-300 hover:text-rose-500"><i class="fas fa-times-circle"></i></button>
                            </div>
                        </div>
                        
                        <button 
                            @click="addMembers" 
                            :disabled="usersToAdd.length === 0" 
                            class="w-full px-6 py-4 bg-slate-900 text-white rounded-xl font-black text-[10px] uppercase tracking-[0.2em] hover:bg-emerald-600 shadow-lg disabled:opacity-50 transition-all active:scale-95"
                        >
                            {{ usersToAdd.length > 0 ? `Deploy ${usersToAdd.length} Selective Units` : 'Select Units for Deployment' }}
                        </button>
                    </div>
                </div>

                <!-- Unit Manifest (List) -->
                <div class="flex-1 overflow-y-auto px-1 custom-scrollbar pr-2">
                    <div v-if="!activeTeam?.members?.length" class="text-center py-12 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-100">
                        <i class="fas fa-user-slash text-slate-200 text-2xl mb-2"></i>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Registry Empty</p>
                    </div>
                    <div v-else class="space-y-3">
                        <div v-for="member in activeTeam.members" :key="member.id" class="group bg-white p-4 rounded-xl border border-slate-100 flex justify-between items-center hover:border-emerald-500/30 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-lg bg-slate-50 text-slate-400 flex items-center justify-center font-black text-sm shadow-inner group-hover:bg-emerald-50 group-hover:text-emerald-600 transition-colors">
                                    {{ member.name.charAt(0) }}
                                </div>
                                <div>
                                    <div class="font-black text-slate-800 text-[11px] uppercase tracking-tight">{{ member.name }}</div>
                                    <div class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">{{ member.email }}</div>
                                </div>
                            </div>
                            <button @click="removeMember(member.id)" class="w-8 h-8 rounded-lg bg-slate-50 text-slate-300 hover:bg-rose-50 hover:text-rose-500 flex items-center justify-center transition-all opacity-0 group-hover:opacity-100">
                                <i class="fas fa-user-minus text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Modal>
    </div>
</template>

<style scoped>
.animate-content-fade {
    animation: content-in 0.4s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
}

@keyframes content-in {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.shadow-inner {
    box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.05);
}

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>

<style scoped>
.animate-content-fade {
    animation: content-in 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
}

@keyframes content-in {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

.shadow-inner {
    box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.05);
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}

:deep(.bg-white\/70) {
    background-color: rgba(255, 255, 255, 0.7);
}
</style>
