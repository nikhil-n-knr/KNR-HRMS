<template>
    <div class="h-full flex flex-col font-inter bg-slate-50/50">
        <!-- Header -->
        <div class="bg-white border-b border-slate-200 px-10 py-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 shadow-sm">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <ShieldCheckIcon class="w-5 h-5 text-emerald-600" />
                    <span class="text-sm font-black text-emerald-500 uppercase tracking-[0.2em]">Security Protocol</span>
                </div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight leading-none">Client Access Hub</h1>
                <p class="text-sm text-slate-500 mt-2 font-medium">Manage external credentials and system integrity overrides.</p>
            </div>
            <div class="flex gap-3 w-full md:w-auto">
                <button 
                    @click="showUserModal = true" 
                    class="flex-1 md:flex-none bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-emerald-100 transition-all flex items-center justify-center gap-3 transform hover:-translate-y-0.5 active:translate-y-0"
                >
                    <UserPlusIcon class="w-4 h-4" />
                    Provision User
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-hidden flex flex-col lg:flex-row">
            <!-- Sidebar: Client List -->
            <div class="w-full lg:w-[400px] bg-white border-r border-slate-200 overflow-y-auto flex flex-col shadow-inner">
                <div class="p-6 border-b border-slate-50">
                    <div class="relative group">
                        <MagnifyingGlassIcon class="w-4 h-4 absolute left-4 top-4 text-slate-300 group-focus-within:text-emerald-500 transition-colors" />
                        <input 
                            type="text" 
                            placeholder="Filter active clients..." 
                            class="w-full pl-11 pr-4 py-4 bg-slate-50 border-transparent rounded-[1.25rem] text-sm font-bold text-slate-900 placeholder-slate-300 focus:bg-white focus:ring-4 focus:ring-emerald-50/50 focus:border-emerald-500 transition-all shadow-sm" 
                        />
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto">
                    <div v-for="client in clients" :key="client.id" 
                        @click="selectClient(client)"
                        :class="[
                            'px-8 py-6 cursor-pointer border-b border-slate-50 hover:bg-slate-50/50 transition-all group relative',
                            selectedClient?.id === client.id ? 'bg-emerald-50/30' : ''
                        ]"
                    >
                        <div v-if="selectedClient?.id === client.id" class="absolute left-0 top-0 bottom-0 w-1.5 bg-emerald-600 rounded-r-full shadow-[2px_0_10px_rgba(5,150,105,0.3)]"></div>
                        
                        <div class="flex justify-between items-start">
                            <h3 :class="['font-black text-base tracking-tight transition-colors', selectedClient?.id === client.id ? 'text-emerald-600' : 'text-slate-900']">{{ client.name }}</h3>
                            <span v-if="!client.is_active" class="px-2 py-0.5 bg-rose-50 text-rose-600 text-sm font-black rounded uppercase tracking-widest border border-rose-100">Suspended</span>
                            <span v-else class="h-2 w-2 rounded-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.4)]"></span>
                        </div>
                        <div class="mt-2 flex items-center gap-4 text-sm font-black text-slate-400 uppercase tracking-widest">
                            <span class="flex items-center gap-1.5"><UsersIcon class="w-3.5 h-3.5" /> {{ client.client_users?.length || 0 }} Nodes</span>
                            <span class="text-slate-200">•</span>
                            <span>#CL-{{ client.id }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content: Client Details -->
            <div class="flex-1 overflow-y-auto bg-slate-50 p-6 sm:p-10" v-if="selectedClient">
                
                <!-- Tab Switching -->
                <div class="mb-10 flex gap-8 border-b border-slate-200">
                    <button 
                        v-for="tab in ['users', 'links']" 
                        :key="tab"
                        @click="activeTab = tab"
                        :class="[
                            'pb-4 text-xs font-black uppercase tracking-[0.2em] transition-all border-b-4 px-2',
                            activeTab === tab ? 'text-emerald-600 border-emerald-600' : 'text-slate-400 border-transparent hover:text-slate-600 hover:border-slate-300'
                        ]"
                    >
                        {{ tab === 'users' ? 'User Management' : 'Secure Analytics' }}
                    </button>
                </div>

                <!-- Tab: Users -->
                <div v-if="activeTab === 'users'" class="space-y-6">
                    <div v-for="user in selectedClient.client_users" :key="user.id" class="bg-white p-6 sm:p-8 rounded-[2.5rem] shadow-[0_10px_40px_rgba(0,0,0,0.02)] border border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center group hover:shadow-xl hover:shadow-emerald-100/10 transition-all border-l-[6px]" :class="user.is_active ? 'border-l-emerald-600' : 'border-l-rose-500'">
                        <div class="flex items-center gap-6">
                            <div class="h-14 w-14 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-900 font-black text-xl shadow-inner border border-slate-100 group-hover:bg-emerald-600 group-hover:text-white group-hover:border-emerald-600 transition-all">
                                {{ user.name.charAt(0) }}
                            </div>
                            <div>
                                <h4 class="font-black text-slate-900 text-lg tracking-tight flex items-center gap-3">
                                    {{ user.name }}
                                    <span v-if="!user.is_active" class="px-2 py-0.5 bg-rose-50 text-rose-600 text-sm rounded uppercase tracking-widest font-black border border-rose-100">Access Revoked</span>
                                </h4>
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mt-2">
                                    <div class="flex items-center gap-1.5">
                                        <EnvelopeIcon class="w-3.5 h-3.5 text-slate-300" />
                                        <p class="text-sm font-black text-slate-400 uppercase tracking-widest">{{ user.email }}</p>
                                    </div>
                                    <span class="text-slate-200 hidden sm:inline">|</span>
                                    <div class="flex items-center gap-1.5 text-emerald-500 font-black text-sm uppercase tracking-widest">
                                        <CubeIcon class="w-3.5 h-3.5" />
                                        {{ user.projects?.length || 0 }} Projects Active
                                    </div>
                                    <div v-if="user.last_login_at" class="flex items-center gap-x-4">
                                        <span class="text-slate-200 hidden sm:inline">|</span>
                                        <p class="text-sm font-black text-slate-400 uppercase tracking-widest">
                                            Telemetry: {{ new Date(user.last_login_at).toLocaleString() }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-3 mt-6 sm:mt-0 w-full sm:w-auto">
                            <button @click="resetPassword(user)" class="flex-1 sm:flex-none p-3 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all border border-transparent hover:border-emerald-100" title="Reset Credentials">
                                <KeyIcon class="w-5 h-5" />
                            </button>
                            <button @click="toggleAccess(user)" :class="['flex-1 sm:flex-none p-3 rounded-xl transition-all border', user.is_active ? 'text-slate-400 hover:text-rose-600 hover:bg-rose-50 hover:border-rose-100' : 'text-emerald-600 bg-emerald-50 border-emerald-100']" title="System Toggle">
                                <PowerIcon class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                    
                    <div v-if="!selectedClient.client_users?.length" class="text-center py-20 bg-white rounded-[3rem] border-4 border-dashed border-slate-100">
                        <div class="h-20 w-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <UserPlusIcon class="w-8 h-8 text-slate-200" />
                        </div>
                        <h3 class="font-black text-slate-900 text-lg">No Credentials Provisioned</h3>
                        <p class="text-slate-400 text-sm font-medium mt-1">Initialize this node to allow external project oversight.</p>
                        <button @click="showUserModal = true" class="mt-8 px-8 py-3 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-widest shadow-xl shadow-slate-200">Initialize Access</button>
                    </div>
                </div>

                <!-- Tab: Secure Links -->
                <div v-if="activeTab === 'links'" class="bg-white p-20 rounded-[3rem] border border-slate-100 text-center shadow-sm">
                    <div class="h-24 w-24 bg-emerald-50 rounded-[2rem] flex items-center justify-center mx-auto mb-8 shadow-inner">
                        <LinkIcon class="w-10 h-10 text-emerald-600" />
                    </div>
                    <h3 class="font-black text-2xl text-slate-900 tracking-tight">Tokenized Access Control</h3>
                    <p class="text-slate-500 text-sm mt-3 font-medium max-w-sm mx-auto leading-relaxed">Secure Link Management has been abstracted to the global Security Controller for enhanced auditability.</p>
                    <div class="mt-10 flex gap-4 justify-center">
                        <button class="px-6 py-3 bg-slate-50 text-slate-400 rounded-xl text-sm font-black uppercase tracking-widest cursor-not-allowed">Centralized View Only</button>
                    </div>
                </div>

            </div>
            
            <!-- Empty State -->
            <div v-else class="flex-1 flex flex-col items-center justify-center text-slate-300 gap-6 p-10 bg-slate-50/50">
                <div class="h-32 w-32 border-8 border-slate-100 rounded-[3rem] border-dashed flex items-center justify-center animate-pulse">
                     <FingerPrintIcon class="w-12 h-12" />
                </div>
                <div class="text-center">
                    <h3 class="font-black text-lg text-slate-400 tracking-tight uppercase tracking-[0.3em]">Neural Link Standby</h3>
                    <p class="text-sm font-black uppercase tracking-widest mt-2">Select a project cluster node to manage access control.</p>
                </div>
            </div>
        </div>

        <!-- Create User Modal -->
        <Modal :show="showUserModal" @close="showUserModal = false" maxWidth="xl">
            <div class="p-10 font-inter">
                <div class="flex justify-between items-start mb-10">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">Provision Node</h3>
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mt-1">Credential Assignment Protocol</p>
                    </div>
                    <button @click="showUserModal = false" class="p-2 text-slate-300 hover:text-rose-500 transition-colors">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <div class="space-y-8">
                    <div>
                        <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-3">Operator Name</label>
                        <input v-model="form.name" type="text" placeholder="Access Holder Name" class="w-full bg-slate-50 border-transparent rounded-2xl px-5 py-5 text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-50/50 focus:border-emerald-500 transition-all" />
                    </div>
                    <div>
                        <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-3">Target Email / ID</label>
                        <input v-model="form.email" type="email" placeholder="Verification endpoint" class="w-full bg-slate-50 border-transparent rounded-2xl px-5 py-5 text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-50/50 focus:border-emerald-500 transition-all" />
                    </div>
                    <div>
                        <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-3">Client Cluster</label>
                        <select v-model="form.client_id" class="w-full bg-slate-50 border-transparent rounded-2xl px-5 py-5 text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-50/50 focus:border-emerald-500 transition-all appearance-none cursor-pointer">
                            <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-3">Project Entitlements</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-48 overflow-y-auto p-4 bg-slate-50 rounded-[2rem] border border-slate-100">
                            <label v-for="project in filteredProjects" :key="project.id" class="flex items-center gap-3 p-4 bg-white border border-slate-200 rounded-2xl cursor-pointer hover:border-emerald-600 hover:shadow-lg hover:shadow-emerald-50 group transition-all">
                                <input type="checkbox" :value="project.id" v-model="form.project_ids" class="h-5 w-5 text-emerald-600 border-slate-300 rounded-lg focus:ring-emerald-500 transition-all cursor-pointer" />
                                <span class="text-xs font-black text-slate-600 group-hover:text-emerald-600 transition-colors uppercase tracking-tight">{{ project.name }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-12 flex flex-col sm:flex-row gap-4">
                    <button @click="showUserModal = false" class="flex-1 px-8 py-5 text-xs font-black text-slate-400 bg-slate-50 hover:bg-slate-100 rounded-2xl uppercase tracking-[0.2em] transition-all">Abort</button>
                    <button @click="submitUser" :disabled="form.processing" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-5 rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-xl shadow-emerald-100 transition-all flex items-center justify-center gap-2 transform hover:-translate-y-0.5 active:translate-y-0">
                        {{ form.processing ? 'Provisioning...' : 'Confirm Access' }}
                    </button>
                </div>
            </div>
        </Modal>

        <!-- New Password Alert Modal (Nicer visual feedback) -->
        <Modal :show="showPasswordModal" @close="showPasswordModal = false" maxWidth="md">
            <div class="p-10 text-center font-inter">
                <div class="h-20 w-20 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                    <KeyIcon class="w-8 h-8 text-emerald-600" />
                </div>
                <h3 class="text-2xl font-black text-slate-900 tracking-tight">Access Reset Successful</h3>
                <p class="text-slate-500 text-sm mt-3 font-medium">New temporary credentials have been generated for the node.</p>
                
                <div class="mt-8 p-6 bg-slate-900 rounded-[1.5rem] relative overflow-hidden group">
                    <p class="text-sm font-black text-emerald-400 uppercase tracking-widest mb-2">Temporary Secret</p>
                    <div class="text-2xl font-black text-white font-mono tracking-widest break-all">
                        {{ newGeneratedPassword }}
                    </div>
                    <button @click="copyPassword" class="mt-4 text-sm font-black text-slate-400 hover:text-white uppercase tracking-widest transition-colors flex items-center gap-2 mx-auto">
                        <DocumentDuplicateIcon class="w-4 h-4" />
                        {{ copied ? 'Secret In Clipboard' : 'Copy Secret' }}
                    </button>
                </div>

                <button @click="showPasswordModal = false" class="mt-10 w-full bg-slate-50 hover:bg-slate-100 text-slate-900 py-4 rounded-xl text-xs font-black uppercase tracking-widest transition-all">Protocol Closed</button>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { 
    UserPlusIcon, 
    UsersIcon, 
    KeyIcon, 
    PowerIcon, 
    MagnifyingGlassIcon,
    ShieldCheckIcon,
    EnvelopeIcon,
    CubeIcon,
    XMarkIcon,
    LinkIcon,
    FingerPrintIcon,
    DocumentDuplicateIcon
} from '@heroicons/vue/24/outline';
import Modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';

const clients = ref([]);
const allProjects = ref([]);
const selectedClient = ref(null);
const activeTab = ref('users');
const showUserModal = ref(false);
const showPasswordModal = ref(false);
const newGeneratedPassword = ref('');
const copied = ref(false);

const form = useForm({
    name: '',
    email: '',
    client_id: null,
    project_ids: []
});

const filteredProjects = computed(() => {
    if (form.client_id) {
         return allProjects.value.filter(p => p.client_id == form.client_id);
    }
    return allProjects.value;
});

const fetchClients = async () => {
    try {
        const { data } = await axios.get(route('clients.index'));
        clients.value = data.clients;
        allProjects.value = data.all_projects;
    } catch (e) {
        console.error("Failed to load clients", e);
    }
};

const selectClient = (client) => {
    selectedClient.value = client;
    form.client_id = client.id;
};

const submitUser = () => {
    form.post(route('clients.users.store'), {
        onSuccess: () => {
            showUserModal.value = false;
            fetchClients();
            form.reset();
        }
    });
};

const resetPassword = async (user) => {
    if (!confirm(`Reset credentials for ${user.name}? Agent session will be terminated.`)) return;
    try {
        const { data } = await axios.post(route('clients.users.reset-password', user.id));
        newGeneratedPassword.value = data.new_password;
        showPasswordModal.value = true;
        copied.value = false;
    } catch (e) {
        alert("Credential reset failed.");
    }
};

const copyPassword = () => {
    navigator.clipboard.writeText(newGeneratedPassword.value);
    copied.value = true;
    setTimeout(() => { copied.value = false }, 3000);
};

const toggleAccess = async (user) => {
    if (user.is_active && !confirm(`Terminate neural link for ${user.name}?`)) return;
    try {
        if (user.is_active) {
            await axios.post(route('clients.users.kill', user.id));
        } else {
             await axios.put(route('clients.users.update', user.id), { ...user, is_active: true });
        }
        fetchClients();
    } catch (e) {
        console.error(e);
    }
};

onMounted(() => {
    fetchClients();
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap');
.font-inter { font-family: 'Inter', sans-serif; }

::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
::-webkit-scrollbar-track {
  background: transparent;
}
::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1;
}
</style>
